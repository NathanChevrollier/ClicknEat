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
                'notes' => 'nullable|string',
            ]);
            
            // Vérifier si au moins un item a une quantité > 0
            $hasItems = false;
            $totalPrice = 0;
            
            if ($request->has('items')) {
                foreach ($request->items as $itemId => $itemData) {
                    if (isset($itemData['quantity']) && $itemData['quantity'] > 0) {
                        $hasItems = true;
                        break;
                    }
                }
            }
            
            if (!$hasItems) {
                return redirect()->back()->with('error', 'Votre commande doit contenir au moins un article.');
            }
            
            $restaurant = Restaurant::findOrFail($request->restaurant_id);
            
            // Créer la commande
            $order = new Order([
                'user_id' => auth()->id(),
                'restaurant_id' => $restaurant->id,
                'status' => Order::STATUS_PENDING,
                'notes' => $request->notes ?? '',
                'total_price' => 0, // Sera mis à jour après l'ajout des items
            ]);
            
            $order->save();
            
            // Ajouter les items à la commande
            if ($request->has('items')) {
                foreach ($request->items as $itemId => $itemData) {
                    $quantity = intval($itemData['quantity'] ?? 0);
                    
                    if ($quantity > 0) {
                        $item = Item::findOrFail($itemId);
                        $price = $item->price;
                        
                        $order->items()->attach($item->id, [
                            'quantity' => $quantity,
                            'price' => $price,
                        ]);
                        
                        $totalPrice += $price * $quantity;
                    }
                }
            }
            
            // Mettre à jour le prix total
            $order->total_price = $totalPrice;
            $order->save();
            
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
        $orderItems = $order->items->pluck('pivot.quantity', 'id')->toArray();
        
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
            
            // Vérifier si au moins un item a une quantité > 0
            $hasItems = false;
            $totalPrice = 0;
            
            if ($request->has('items')) {
                foreach ($request->items as $itemId => $itemData) {
                    if (isset($itemData['quantity']) && $itemData['quantity'] > 0) {
                        $hasItems = true;
                        break;
                    }
                }
            }
            
            if (!$hasItems) {
                return redirect()->back()->with('error', 'Votre commande doit contenir au moins un article.');
            }
            
            // Mettre à jour les notes de la commande
            $order->notes = $request->notes ?? '';
            
            // Supprimer tous les items existants
            $order->items()->detach();
            
            // Ajouter les nouveaux items à la commande
            if ($request->has('items')) {
                foreach ($request->items as $itemId => $itemData) {
                    $quantity = intval($itemData['quantity'] ?? 0);
                    
                    if ($quantity > 0) {
                        $item = Item::findOrFail($itemId);
                        $price = $item->price;
                        
                        $order->items()->attach($item->id, [
                            'quantity' => $quantity,
                            'price' => $price,
                        ]);
                        
                        $totalPrice += $price * $quantity;
                    }
                }
            }
            
            // Mettre à jour le prix total
            $order->total_price = $totalPrice;
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
     * Update the status of an order.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', Order::getStatuses()),
        ]);

        $user = Auth::user();
        
        // Seuls les restaurateurs peuvent mettre à jour le statut
        if (!$user->isRestaurateur()) {
            abort(403, 'Vous n\'avez pas le droit de modifier le statut de cette commande.');
        }

        // Vérifier que le restaurant appartient au restaurateur
        $restaurantIds = $user->restaurants()->pluck('id')->toArray();
        if (!in_array($order->restaurant_id, $restaurantIds)) {
            abort(403, 'Vous n\'avez pas accès à cette commande.');
        }

        $order->status = $request->status;
        $order->save();

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Statut de la commande mis à jour.');
    }

    /**
     * Cancel an order.
     */
    public function cancel(Order $order)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur a le droit d'annuler cette commande
        if ($user->isClient() && $order->user_id !== $user->id) {
            abort(403, 'Vous n\'avez pas accès à cette commande.');
        }

        if ($user->isRestaurateur()) {
            $restaurantIds = $user->restaurants()->pluck('id')->toArray();
            if (!in_array($order->restaurant_id, $restaurantIds)) {
                abort(403, 'Vous n\'avez pas accès à cette commande.');
            }
        }

        // Vérifier que la commande peut être annulée
        if (in_array($order->status, [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])) {
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'Cette commande ne peut plus être annulée.');
        }

        $order->status = Order::STATUS_CANCELLED;
        $order->save();

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Commande annulée avec succès.');
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
        
        // Filtrer par restaurant si spécifié
        if ($request->has('restaurant_id')) {
            $restaurant = Restaurant::findOrFail($request->restaurant_id);
            
            // Vérifier que le restaurant appartient au restaurateur
            if (!$restaurantIds->contains($restaurant->id)) {
                abort(403, 'Vous n\'avez pas accès à ce restaurant.');
            }
            
            $orders = Order::where('restaurant_id', $restaurant->id)
                ->with(['user', 'restaurant'])
                ->orderBy('created_at', 'desc')
                ->get();
                
            return view('orders.restaurant', compact('orders', 'restaurant'));
        }
        
        // Sinon, afficher toutes les commandes de tous les restaurants du restaurateur
        $orders = Order::whereIn('restaurant_id', $restaurantIds)
            ->with(['user', 'restaurant'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Passer null pour $restaurant pour indiquer qu'aucun restaurant spécifique n'est sélectionné
        $restaurant = null;
            
        return view('orders.restaurant', compact('orders', 'restaurant'));
    }
}
