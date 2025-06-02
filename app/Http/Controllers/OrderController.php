<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isRestaurateur()) {
            $restaurantIds = $user->restaurants()->pluck('id');
            if ($request->filled('restaurant') && $request->restaurant !== 'all') {
                $restaurant = $user->restaurants()->where('id', $request->restaurant)->first();
                if (!$restaurant) {
                    abort(403, 'Ce restaurant ne vous appartient pas.');
                }
                $orders = Order::where('restaurant_id', $restaurant->id)
                    ->with(['restaurant', 'user', 'items'])
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                $orders = Order::whereIn('restaurant_id', $restaurantIds)
                    ->with(['restaurant', 'user', 'items'])
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
            return view('orders.restaurant', compact('orders'));
        } elseif ($user->isAdmin()) {
            // Les administrateurs voient toutes les commandes
            $orders = Order::with(['user', 'restaurant'])->latest()->get();
            return view('orders.index', compact('orders'));
        } else {
            // Les clients voient leurs propres commandes
            $orders = $user->orders()->with(['restaurant'])->latest()->get();
            return view('orders.index', compact('orders'));
        }
    }

    /**
     * Show the form for creating a new order.
     */
    public function create($restaurant)
    {
        $restaurant = Restaurant::findOrFail($restaurant);
        
        // Vérifier que le restaurant est ouvert
        if (!$restaurant->is_open) {
            return redirect()->route('restaurants.show', $restaurant->id)
                ->with('error', 'Ce restaurant est actuellement fermé et n\'accepte pas de commandes.');
        }
        
        $categories = $restaurant->categories()->with('items')->get();
        return view('orders.create', compact('restaurant', 'categories'));
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request)
    {
        // Ajout de journalisation pour débogage
        Log::info('Données de commande reçues', [
            'request_data' => $request->all()
        ]);
        
        try {
            $validatedData = $request->validate([
                'restaurant_id' => 'required|exists:restaurants,id',
                'items' => 'sometimes|array',
                'menus' => 'sometimes|array',
                'notes' => 'nullable|string',
            ]);
            
            // Vérifier si au moins un item ou un menu a une quantité > 0
            $hasItems = false;
            $totalPrice = 0;
            $insertCount = 0;
            
            // Vérifier les plats individuels
            if ($request->has('items')) {
                foreach ($request->items as $itemId => $itemData) {
                    if (isset($itemData['quantity']) && $itemData['quantity'] > 0) {
                        $hasItems = true;
                        break;
                    }
                }
            }
            
            // Vérifier les menus
            if (!$hasItems && $request->has('menus')) {
                foreach ($request->menus as $menuId => $menuData) {
                    if (isset($menuData['quantity']) && $menuData['quantity'] > 0) {
                        $hasItems = true;
                        break;
                    }
                }
            }
            
            if (!$hasItems) {
                return redirect()->back()->with('error', 'Votre commande doit contenir au moins un article ou un menu.');
            }
            
            $restaurant = Restaurant::findOrFail($request->restaurant_id);
            
            // Créer la commande immédiatement pour avoir un ID
            $order = new Order([
                'user_id' => auth()->id(),
                'restaurant_id' => $restaurant->id,
                'status' => Order::STATUS_PENDING,
                'notes' => $request->notes ?? '',
                'total_amount' => 0, // Sera mis à jour après l'ajout des items
            ]);
            
            $order->save();
            Log::info('Commande créée', ['order_id' => $order->id]);
            
            // 1. Traiter les plats individuels
            if ($request->has('items')) {
                foreach ($request->items as $itemId => $itemData) {
                    $quantity = intval($itemData['quantity'] ?? 0);
                    
                    if ($quantity > 0) {
                        try {
                            // Vérifier que le plat existe et est disponible
                            $item = Item::where('id', $itemId)
                                ->where('is_available', 1)
                                ->first();
                                
                            if (!$item) {
                                Log::warning('Plat non disponible:', ['item_id' => $itemId]);
                                continue;
                            }
                            
                            $price = $item->price;
                            
                            // Vérifier si le plat existe déjà dans la commande comme plat individuel (menu_id = null)
                            // Cela est important pour distinguer les plats individuels des plats de menu
                            $existingOrderItem = \Illuminate\Support\Facades\DB::table('order_items')
                                ->where('order_id', $order->id)
                                ->where('item_id', $itemId)
                                ->whereNull('menu_id')
                                ->first();
                                
                            if ($existingOrderItem) {
                                // Mettre à jour la quantité si le plat existe déjà
                                \Illuminate\Support\Facades\DB::table('order_items')
                                    ->where('id', $existingOrderItem->id)
                                    ->update([
                                        'quantity' => $existingOrderItem->quantity + $quantity,
                                        'updated_at' => now()
                                    ]);
                                    
                                Log::info('Quantité du plat individuel mise à jour:', [
                                    'item_id' => $itemId,
                                    'item_name' => $item->name,
                                    'old_quantity' => $existingOrderItem->quantity,
                                    'new_quantity' => $existingOrderItem->quantity + $quantity
                                ]);
                            } else {
                                // Insérer le nouveau plat
                                \Illuminate\Support\Facades\DB::table('order_items')->insert([
                                    'order_id' => $order->id,
                                    'item_id' => $itemId,
                                    'quantity' => $quantity,
                                    'price' => $price,
                                    'menu_id' => null, // Pas de menu car c'est un plat individuel
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                                
                                Log::info('Plat individuel inséré:', [
                                    'item_id' => $itemId,
                                    'item_name' => $item->name,
                                    'quantity' => $quantity,
                                    'price' => $price
                                ]);
                            }
                            
                            $totalPrice += $price * $quantity;
                            $insertCount++;
                            $hasItems = true;
                        } catch (\Exception $e) {
                            Log::error('Erreur lors du traitement du plat:', [
                                'error' => $e->getMessage(),
                                'item_id' => $itemId
                            ]);
                        }
                    }
                }
            }
            
            // 2. Traiter les menus
            if ($request->has('menus')) {
                foreach ($request->menus as $menuId => $menuData) {
                    $quantity = intval($menuData['quantity'] ?? 0);
                    
                    if ($quantity > 0) {
                        try {
                            // Vérifier que le menu existe
                            $menu = \App\Models\Menu::where('id', $menuId)
                                ->first();
                                
                            if (!$menu) {
                                Log::warning('Menu non trouvé ou inactif:', ['menu_id' => $menuId]);
                                continue;
                            }
                            
                            // Ajouter le prix du menu au total
                            $totalPrice += $menu->price * $quantity;
                            $hasItems = true;
                            
                            Log::info('Menu traité:', [
                                'menu_id' => $menuId,
                                'menu_name' => $menu->name,
                                'quantity' => $quantity,
                                'price' => $menu->price
                            ]);
                            
                            // Récupérer les plats du menu
                            $menuItems = \App\Models\Item::where('items.menu_id', $menuId)
                                ->where('is_available', 1)
                                ->get();
                                
                            if ($menuItems->isEmpty()) {
                                Log::warning('Menu sans plats disponibles:', ['menu_id' => $menuId]);
                                continue;
                            }
                            
                            Log::info('Plats du menu récupérés:', [
                                'menu_id' => $menuId, 
                                'count' => $menuItems->count(),
                                'plats' => $menuItems->pluck('name')->toArray()
                            ]);
                            
                            // Ajouter chaque plat du menu à la commande
                            foreach ($menuItems as $item) {
                                try {
                                    // Vérifier si le plat existe déjà dans la commande avec le même menu_id
                                    // Cela est important pour distinguer les plats individuels des plats de menu
                                    $existingOrderItem = \Illuminate\Support\Facades\DB::table('order_items')
                                        ->where('order_id', $order->id)
                                        ->where('item_id', $item->id)
                                        ->where('order_items.menu_id', $menuId)
                                        ->first();
                                        
                                    if ($existingOrderItem) {
                                        // Mettre à jour la quantité si le plat existe déjà
                                        \Illuminate\Support\Facades\DB::table('order_items')
                                            ->where('id', $existingOrderItem->id)
                                            ->update([
                                                'quantity' => $existingOrderItem->quantity + $quantity,
                                                'menu_id' => $menuId, // Associer au menu
                                                'updated_at' => now()
                                            ]);
                                            
                                        Log::info('Quantité du plat de menu mise à jour:', [
                                            'item_id' => $item->id,
                                            'item_name' => $item->name,
                                            'menu_id' => $menuId,
                                            'old_quantity' => $existingOrderItem->quantity,
                                            'new_quantity' => $existingOrderItem->quantity + $quantity
                                        ]);
                                    } else {
                                        // Insérer le nouveau plat du menu
                                        \Illuminate\Support\Facades\DB::table('order_items')->insert([
                                            'order_id' => $order->id,
                                            'item_id' => $item->id,
                                            'quantity' => $quantity,
                                            'price' => $item->price,
                                            'menu_id' => $menuId, // Associer au menu
                                            'created_at' => now(),
                                            'updated_at' => now()
                                        ]);
                                        
                                        Log::info('Plat de menu inséré:', [
                                            'item_id' => $item->id,
                                            'item_name' => $item->name,
                                            'menu_id' => $menuId,
                                            'quantity' => $quantity,
                                            'price' => $item->price
                                        ]);
                                    }
                                    
                                    $insertCount++;
                                } catch (\Exception $e) {
                                    Log::error('Erreur lors du traitement du plat de menu:', [
                                        'error' => $e->getMessage(),
                                        'item_id' => $item->id,
                                        'menu_id' => $menuId
                                    ]);
                                }
                            }
                        } catch (\Exception $e) {
                            Log::error('Erreur lors du traitement du menu:', [
                                'error' => $e->getMessage(),
                                'menu_id' => $menuId
                            ]);
                        }
                    }
                }
            }
            
            // Vérifier qu'au moins un élément a été ajouté à la commande
            if (!$hasItems || $insertCount == 0) {
                $order->delete();
                return redirect()->back()->with('error', 'Aucun article valide n\'a été ajouté à votre commande.');
            }
            
            // Mettre à jour le prix total
            $order->total_amount = $totalPrice;
            $order->save();
            
            Log::info('Commande finalisée avec succès', [
                'order_id' => $order->id, 
                'total_amount' => $totalPrice,
                'items_count' => $insertCount
            ]);
            
            return redirect()->route('orders.index')
                ->with('success', 'Commande créée avec succès!');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de la commande', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Une erreur est survenue lors de la création de votre commande : ' . $e->getMessage());
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur a le droit de voir cette commande
        if ($user->isClient() && $order->user_id !== $user->id) {
            abort(403, 'Vous n\'avez pas accès à cette commande.');
        }

        if ($user->isRestaurateur()) {
            $restaurantIds = $user->restaurants()->pluck('id')->toArray();
            if (!in_array($order->restaurant_id, $restaurantIds)) {
                abort(403, 'Vous n\'avez pas accès à cette commande.');
            }
        }

        $order->load(['user', 'restaurant', 'items']);
        return view('orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit(Order $order)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur a le droit de modifier cette commande
        if ($user->isClient() && $order->user_id !== $user->id) {
            abort(403, 'Vous n\'avez pas accès à cette commande.');
        }

        if ($user->isRestaurateur()) {
            $restaurantIds = $user->restaurants()->pluck('id')->toArray();
            if (!in_array($order->restaurant_id, $restaurantIds)) {
                abort(403, 'Vous n\'avez pas accès à cette commande.');
            }
        }

        // Vérifier que la commande peut être modifiée
        if (in_array($order->status, [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])) {
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'Cette commande ne peut plus être modifiée.');
        }

        $restaurant = $order->restaurant;
        $categories = $restaurant->categories()->with('items')->get();
        
        // Modifier pour éviter l'ambiguïté de menu_id en chargeant les items de manière explicite
        $orderItems = [];
        foreach ($order->items as $item) {
            $orderItems[$item->id] = $item->pivot->quantity;
        }
        
        return view('orders.edit', compact('order', 'restaurant', 'categories', 'orderItems'));
    }

    /**
     * Update the specified order in storage.
     */
    public function update(Request $request, Order $order)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur a le droit de modifier cette commande
        if ($user->isClient() && $order->user_id !== $user->id) {
            abort(403, 'Vous n\'avez pas accès à cette commande.');
        }

        if ($user->isRestaurateur()) {
            $restaurantIds = $user->restaurants()->pluck('id')->toArray();
            if (!in_array($order->restaurant_id, $restaurantIds)) {
                abort(403, 'Vous n\'avez pas accès à cette commande.');
            }
        }

        // Vérifier que la commande peut être modifiée
        if (in_array($order->status, [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])) {
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'Cette commande ne peut plus être modifiée.');
        }

        try {
            $validatedData = $request->validate([
                'items' => 'sometimes|array',
                'notes' => 'nullable|string',
            ]);
            
            // Vérifier si au moins un item ou menu a une quantité > 0
            $hasItems = false;
            $totalPrice = 0;
            
            // Vérifier les plats individuels
            if ($request->has('items')) {
                foreach ($request->items as $itemId => $itemData) {
                    if (isset($itemData['quantity']) && $itemData['quantity'] > 0) {
                        $hasItems = true;
                        break;
                    }
                }
            }
            
            // Vérifier les menus
            if (!$hasItems && $request->has('menus')) {
                foreach ($request->menus as $menuId => $menuData) {
                    if (isset($menuData['quantity']) && $menuData['quantity'] > 0) {
                        $hasItems = true;
                        break;
                    }
                }
            }
            
            if (!$hasItems) {
                return redirect()->back()->with('error', 'Votre commande doit contenir au moins un article ou un menu.');
            }
            
            // Mettre à jour les notes de la commande
            $order->notes = $request->notes ?? '';
            
            // Structure pour suivre les items à attacher
            $itemsToAttach = [];
            
            // Pour suivre les menus ajoutés et leurs quantités
            $menus = [];
            
            // 1. Ajouter les nouveaux items individuels à la commande
            if ($request->has('items')) {
                foreach ($request->items as $itemId => $itemData) {
                    $quantity = intval($itemData['quantity'] ?? 0);
                    
                    if ($quantity > 0) {
                        $item = Item::findOrFail($itemId);
                        $price = $item->price;
                        $menuId = null;
                        
                        // Vérifier si l'item fait partie d'un menu
                        if ($item->menu_id) {
                            $menuId = $item->menu_id;
                        }
                        
                        // Si le plat est déjà dans la commande avec un autre menu, on le remplace
                        // car un plat ne peut être associé qu'à un seul menu
                        if (isset($itemsToAttach[$item->id])) {
                            // Si c'est déjà un plat du même menu, on cumule les quantités
                            if (isset($itemsToAttach[$item->id]['menu_id']) && $itemsToAttach[$item->id]['menu_id'] == $menuId) {
                                $itemsToAttach[$item->id]['quantity'] += $quantity;
                            } else {
                                // Sinon on remplace (plat individuel ou d'un autre menu)
                                $itemsToAttach[$item->id] = [
                                    'quantity' => $quantity, 
                                    'price' => $price, 
                                    'menu_id' => $menuId
                                ];
                            }
                        } else {
                            // Nouveau plat
                            $itemsToAttach[$item->id] = [
                                'quantity' => $quantity, 
                                'price' => $price, 
                                'menu_id' => $menuId
                            ];
                        }
                        
                        // Ajouter au prix total uniquement pour les plats individuels (sans menu_id)
                        if ($menuId === null) {
                            $totalPrice += $price * $quantity;
                        }
                    }
                }
            }
            
            // 2. Traiter les menus
            if ($request->has('menus')) {
                foreach ($request->menus as $menuId => $menuData) {
                    $quantity = intval($menuData['quantity'] ?? 0);
                    
                    if ($quantity > 0) {
                        // Récupérer le menu
                        $menu = \App\Models\Menu::findOrFail($menuId);
                        $menus[$menuId] = ['quantity' => $quantity, 'price' => $menu->price];
                        
                        // Ajouter le prix du menu au total
                        $totalPrice += $menu->price * $quantity;
                        
                        // Récupérer les plats du menu
                        $menuItems = Item::where('menu_id', $menuId)->get();
                        
                        // Ajouter chaque plat du menu à la commande
                        foreach ($menuItems as $item) {
                            // Si le plat est déjà dans itemsToAttach comme plat individuel, on ne l'ajoute pas
                            if (isset($itemsToAttach[$item->id]) && $itemsToAttach[$item->id]['menu_id'] === null) {
                                continue;
                            }
                            
                            // Ajouter ou mettre à jour le plat avec son menu_id
                            $itemsToAttach[$item->id] = [
                                'quantity' => $quantity,
                                'price' => $item->price,
                                'menu_id' => $menuId
                            ];
                        }
                    }
                }
            }
            
            // Remplacer tous les items de la commande par les nouveaux
            if (!empty($itemsToAttach)) {
                $order->items()->sync($itemsToAttach);
            } else {
                // Si aucun item n'est sélectionné, détacher tous les items
                $order->items()->detach();
            }
            
            // Mettre à jour le prix total
            $order->total_amount = $totalPrice;
            $order->save();
            
            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Commande mise à jour avec succès!');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de la commande', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Une erreur est survenue lors de la mise à jour de votre commande : ' . $e->getMessage());
        }
    }


/**
 * Display a listing of the orders for a restaurant owner.
 */
public function restaurantOrders(Request $request)
{
    $user = Auth::user();
    
    // Vérifier que l'utilisateur est un restaurateur
    if (!$user->isRestaurateur()) {
        abort(403, 'Vous n\'avez pas accès à cette page.');
    }
    
    $restaurantIds = $user->restaurants()->pluck('id');
    
    // Si l'utilisateur n'a pas de restaurant, rediriger vers la page des restaurants
    if ($restaurantIds->isEmpty()) {
        return redirect()->route('restaurants.index')
            ->with('error', 'Vous devez d\'abord créer un restaurant.');
    }
    
    // Initialiser la requête
    $query = Order::with(['user', 'restaurant', 'items']);
    
    // Filtrer par restaurant si spécifié
    if ($request->has('restaurant_id')) {
        $restaurant = Restaurant::findOrFail($request->restaurant_id);
        
        // Vérifier que le restaurant appartient au restaurateur
        if (!$restaurantIds->contains($restaurant->id)) {
            abort(403, 'Vous n\'avez pas accès à ce restaurant.');
        }
        
        $query->where('restaurant_id', $restaurant->id);
    } else {
        // Sinon, filtrer par tous les restaurants du restaurateur
        $query->whereIn('restaurant_id', $restaurantIds);
        $restaurant = null;
    }
    
    // Appliquer les filtres supplémentaires
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->whereHas('user', function($userQuery) use ($search) {
                $userQuery->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
            })
            ->orWhere('id', 'like', "%{$search}%");
        });
    }
    
    // Filtrer par statut
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    
    // Filtrer par date
    if ($request->filled('date')) {
        $date = $request->date;
        $query->whereDate('created_at', $date);
    }
    
    // Récupérer les commandes avec tri par date décroissante
    $orders = $query->orderBy('created_at', 'desc')->get();
        
    return view('orders.restaurant', compact('orders', 'restaurant'));
}

/**
 * Update the status of an order.
 */
public function updateStatus(Request $request, $id)
{
    $order = Order::findOrFail($id);
    
    // Vérifier que l'utilisateur a le droit de modifier cette commande
    $user = Auth::user();
    if ($user->isRestaurateur()) {
        $restaurantIds = $user->restaurants()->pluck('id')->toArray();
        if (!in_array($order->restaurant_id, $restaurantIds)) {
            abort(403, 'Vous n\'avez pas le droit de modifier cette commande.');
        }
    } else if (!$user->isAdmin()) {
        abort(403, 'Vous n\'avez pas le droit de modifier cette commande.');
    }
    
    // Mettre à jour le statut
    $order->status = $request->status;
    $order->save();
    
    return redirect()->back()->with('success', 'Statut de la commande mis à jour avec succès.');
}

/**
 * Cancel an order.
 */
public function cancel($id)
{
    $order = Order::findOrFail($id);
    
    // Vérifier que l'utilisateur a le droit d'annuler cette commande
    $user = Auth::user();
    if ($user->isClient() && $order->user_id != $user->id) {
        abort(403, 'Vous n\'avez pas le droit d\'annuler cette commande.');
    } else if ($user->isRestaurateur()) {
        $restaurantIds = $user->restaurants()->pluck('id')->toArray();
        if (!in_array($order->restaurant_id, $restaurantIds)) {
            abort(403, 'Vous n\'avez pas le droit d\'annuler cette commande.');
        }
    }
    
    // Vérifier que la commande peut être annulée
    if ($order->status === Order::STATUS_CANCELLED || $order->status === Order::STATUS_COMPLETED) {
        return redirect()->back()->with('error', 'Cette commande ne peut pas être annulée.');
    }
    
    // Annuler la commande
    $order->status = Order::STATUS_CANCELLED;
    $order->save();
    
    return redirect()->back()->with('success', 'Commande annulée avec succès.');
}
}
