<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    /**
     * Affiche la liste des commandes
     */
    public function index()
    {
        $orders = Order::all();
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Affiche le formulaire de création d'une commande
     */
    public function create(Request $request)
    {
        $users = \App\Models\User::where('role', 'client')->get();
        $restaurants = \App\Models\Restaurant::all();
        
        // Récupérer les données de pré-remplissage si elles existent
        $selectedUserId = $request->query('user_id');
        $selectedRestaurantId = $request->query('restaurant_id');
        $reservationId = $request->query('reservation_id');
        $tableId = $request->query('table_id');
        
        // Si une réservation est spécifiée, récupérer ses détails
        $reservation = null;
        if ($reservationId) {
            $reservation = \App\Models\Reservation::findOrFail($reservationId);
        }
        
        // Récupérer les items du restaurant sélectionné
        $items = [];
        $menus = [];
        if ($selectedRestaurantId) {
            // Ne récupérer que les plats disponibles
            $items = \App\Models\Item::where('restaurant_id', $selectedRestaurantId)
                ->where('is_available', 1)
                ->get();
                
            // Ne récupérer que les menus qui ont au moins un plat disponible
            $menus = \App\Models\Menu::where('restaurant_id', $selectedRestaurantId)
                ->whereHas('items', function($query) {
                    $query->where('is_available', 1);
                })
                ->get();
        }
        
        return view('admin.orders.create', compact(
            'users', 
            'restaurants', 
            'selectedUserId', 
            'selectedRestaurantId',
            'reservationId',
            'tableId',
            'reservation',
            'items',
            'menus'
        ));
    }

    /**
     * Affiche le détail d'une commande
     */
    public function show($id)
    {
        $order = \App\Models\Order::with(['user', 'restaurant', 'items'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Affiche le formulaire d'édition d'une commande
     */
    public function edit($id)
    {
        // Charger l'ordre avec ses relations, y compris les menus
        $order = \App\Models\Order::with(['user', 'restaurant', 'items', 'menus'])->findOrFail($id);
        
        // Ajouter les informations de prix et de quantité aux menus en regroupant les items par menu_id
        $menuItems = $order->items()->whereNotNull('order_items.menu_id')->get()->groupBy('pivot.menu_id');
        $menuQuantities = [];
        
        foreach($menuItems as $menuId => $items) {
            // La quantité du menu est égale à la quantité d'un des items du menu (ils ont tous la même)
            $menuQuantities[$menuId] = $items->first()->pivot->quantity;
        }
        
        // Récupérer tous les plats disponibles du restaurant
        $availableItems = \App\Models\Item::where('restaurant_id', $order->restaurant_id)
            ->where('is_available', 1)
            ->get();
        
        // Récupérer tous les menus du restaurant qui ont au moins un plat disponible
        $availableMenus = \App\Models\Menu::where('restaurant_id', $order->restaurant_id)
            ->whereHas('items', function($query) {
                $query->where('is_available', 1);
            })
            ->get();
        
        return view('admin.orders.edit', compact('order', 'availableItems', 'availableMenus', 'menuQuantities'));
    }

    /**
     * Stocke une nouvelle commande
     */
    public function store(Request $request)
    {
        // Logs détaillés
        \Illuminate\Support\Facades\Log::debug('[DEBUT] Contenu complet du formulaire:', $request->all());
        \Illuminate\Support\Facades\Log::debug('Plats individuels:', $request->input('individual_items', []));
        \Illuminate\Support\Facades\Log::debug('Nouveaux menus:', $request->input('new_menus', []));
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'restaurant_id' => 'required|exists:restaurants,id',
            'status' => 'required|in:pending,confirmed,preparing,ready,completed,cancelled',
            'delivery_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'individual_items' => 'nullable|array',
            'new_menus' => 'nullable|array',
            'reservation_id' => 'nullable|exists:reservations,id',
        ]);
        
        // Vérifier qu'au moins un plat ou un menu est sélectionné
        if (empty($request->individual_items) && empty($request->new_menus)) {
            return back()->withErrors(['error' => 'Vous devez sélectionner au moins un plat ou un menu']);
        }
        
        // Pré-vérification des plats et menus disponibles
        $hasValidItems = false;
        
        // Créer la commande
        $order = new \App\Models\Order();
        $order->user_id = $request->user_id;
        $order->restaurant_id = $request->restaurant_id;
        $order->status = $request->status;
        $order->delivery_address = $request->delivery_address;
        $order->notes = $request->notes;
        $order->reservation_id = $request->reservation_id; // Associer à une réservation si spécifiée
        $order->total_amount = 0; // Sera mis à jour plus tard
        $order->save(); // Sauvegarde pour avoir un ID
        
        // Calculer le prix total
        $totalPrice = 0;
        $insertCount = 0; // Compteur d'items insérés dans order_items
        
        // Ajouter les plats individuels
        if (!empty($request->individual_items)) {
            \Illuminate\Support\Facades\Log::info('Traitement des plats individuels:', $request->individual_items);
            foreach ($request->individual_items as $itemData) {
                try {
                    if (empty($itemData['id'])) continue;
                    
                    $itemId = $itemData['id'];
                    
                    // Vérifier que l'item est actif/disponible avant de l'ajouter
                    $item = \App\Models\Item::where('id', $itemId)
                        ->where('is_available', 1)
                        ->first();
                        
                    if (!$item) {
                        \Illuminate\Support\Facades\Log::warning('Plat non disponible ou inexistant:', ['item_id' => $itemId]);
                        continue;
                    }
                    
                    $quantity = $itemData['quantity'] ?? 1;
                    
                    // Ajouter au prix total
                    $totalPrice += $item->price * $quantity;
                    
                    // Vérifier d'abord si le plat existe déjà dans la commande comme plat individuel (menu_id = null)
                    // Cela est important pour distinguer les plats individuels des plats de menu
                    $existingOrderItem = \Illuminate\Support\Facades\DB::table('order_items')
                        ->where('order_id', $order->id)
                        ->where('item_id', $item->id)
                        ->whereNull('menu_id')
                        ->first();
                        
                    if ($existingOrderItem) {
                        // Le plat existe déjà, mettre à jour la quantité
                        $result = \Illuminate\Support\Facades\DB::table('order_items')
                            ->where('id', $existingOrderItem->id)
                            ->update([
                                'quantity' => $existingOrderItem->quantity + $quantity,
                                'updated_at' => now()
                            ]);
                            
                        \Illuminate\Support\Facades\Log::info('Quantité du plat individuel mise à jour:', [
                            'order_id' => $order->id,
                            'item_id' => $item->id,
                            'item_name' => $item->name,
                            'old_quantity' => $existingOrderItem->quantity,
                            'new_quantity' => $existingOrderItem->quantity + $quantity
                        ]);
                    } else {
                        // Nouveau plat, l'insérer
                        $result = \Illuminate\Support\Facades\DB::table('order_items')->insert([
                            'order_id' => $order->id,
                            'item_id' => $item->id,
                            'quantity' => $quantity,
                            'price' => $item->price,
                            'menu_id' => null, // Plat individuel, pas de menu associé
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                    
                    if ($result) {
                        $insertCount++;
                        $hasValidItems = true;
                    }
                    
                    \Illuminate\Support\Facades\Log::info('Plat individuel traité:', [
                        'order_id' => $order->id,
                        'item_id' => $itemId,
                        'name' => $item->name,
                        'quantity' => $quantity,
                        'price' => $item->price,
                        'result' => $result
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Erreur traitement plat individuel: ' . $e->getMessage());
                }
            }
        }
        
        // Traiter les nouveaux menus ajoutés
        if (!empty($request->new_menus)) {
            \Illuminate\Support\Facades\Log::info('Traitement des nouveaux menus:', $request->new_menus);
            
            // Vérifier si nous avons des données de plats de menu provenant du formulaire
            $menuItemsData = $request->menu_items_data ?? [];
            \Illuminate\Support\Facades\Log::info('Données brutes des plats de menu:', $menuItemsData);

            // Pour chaque menu sélectionné
            foreach ($request->new_menus as $menuData) {
                try {
                    if (empty($menuData['id'])) continue;
                    
                    $menuId = $menuData['id'];
                    $quantity = $menuData['quantity'] ?? 1;
                    
                    // Charger le menu pour avoir son prix
                    $menu = \App\Models\Menu::find($menuId);
                    if (!$menu || !$menu->is_active) {
                        \Illuminate\Support\Facades\Log::warning('Menu non trouvé ou inactif:', ['menu_id' => $menuId]);
                        continue;
                    }
                    
                    // Ajouter le prix du menu au total
                    $totalPrice += $menu->price * $quantity;
                    $hasValidItems = true; // Marquer comme ayant des plats valides
                    
                    \Illuminate\Support\Facades\Log::info('Menu traité:', [
                        'menu_id' => $menuId,
                        'nom' => $menu->name,
                        'prix' => $menu->price,
                        'quantité' => $quantity
                    ]);
                    
                    // Récupérer automatiquement tous les plats de ce menu
                    // et les ajouter à la commande, que l'on vienne du formulaire ou d'une réservation
                    $menuItems = \App\Models\Item::where('menu_id', $menuId)
                        ->where('is_available', 1)
                        ->get();
                        
                    \Illuminate\Support\Facades\Log::info('Plats du menu récupérés:', [
                        'menu_id' => $menuId, 
                        'count' => $menuItems->count(),
                        'plats' => $menuItems->pluck('name')->toArray()
                    ]);
                    
                    // Ajouter chaque plat du menu à la commande
                    foreach ($menuItems as $item) {
                        try {
                            // Vérifier si le plat existe déjà dans la commande
                            $existingOrderItem = \Illuminate\Support\Facades\DB::table('order_items')
                                ->where('order_id', $order->id)
                                ->where('item_id', $item->id)
                                ->where('menu_id', $menuId)
                                ->first();
                            
                            if ($existingOrderItem) {
                                // Mettre à jour la quantité si le plat existe déjà
                                \Illuminate\Support\Facades\DB::table('order_items')
                                    ->where('id', $existingOrderItem->id)
                                    ->update([
                                        'quantity' => $existingOrderItem->quantity + $quantity,
                                        'updated_at' => now()
                                    ]);
                                
                                \Illuminate\Support\Facades\Log::info('Plat de menu existant mis à jour:', [
                                    'item_id' => $item->id,
                                    'nom' => $item->name,
                                    'menu_id' => $menuId,
                                    'ancien_qte' => $existingOrderItem->quantity,
                                    'nouvelle_qte' => $existingOrderItem->quantity + $quantity
                                ]);
                            } else {
                                // Ajouter le plat à la commande
                                \Illuminate\Support\Facades\DB::table('order_items')->insert([
                                    'order_id' => $order->id,
                                    'item_id' => $item->id,
                                    'quantity' => $quantity,
                                    'price' => $item->price,
                                    'menu_id' => $menuId,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                                
                                \Illuminate\Support\Facades\Log::info('Nouveau plat de menu inséré:', [
                                    'item_id' => $item->id,
                                    'nom' => $item->name,
                                    'menu_id' => $menuId,
                                    'quantité' => $quantity
                                ]);
                            }
                            
                            $insertCount++;
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Erreur lors de l\'ajout du plat du menu:', [
                                'error' => $e->getMessage(),
                                'item_id' => $item->id,
                                'menu_id' => $menuId
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Erreur traitement menu: ' . $e->getMessage());
                }
            }
        }
        
        // Cette section est désactivée pour éviter la double comptabilisation des plats
        // Les plats des menus sont déjà ajoutés automatiquement dans la section précédente
        
        // Traiter les plats des menus envoyés via les champs cachés (Désactivé pour éviter duplication)
        if (false && !empty($request->menu_items_data)) {
            \Illuminate\Support\Facades\Log::info('Traitement des plats de menu via les champs cachés (Désactivé)');
            
            foreach ($request->menu_items_data as $itemId => $itemData) {
                try {
                    // Vérifier que les données sont complètes
                    if (empty($itemData['id']) || empty($itemData['menu_id'])) {
                        \Illuminate\Support\Facades\Log::warning('Données incomplètes pour plat:', ['item_data' => $itemData]);
                        continue;
                    }
                    
                    $menuId = $itemData['menu_id'];
                    $itemId = $itemData['id'];
                    $quantity = $itemData['quantity'] ?? 1;
                    
                    // Vérifier que le plat existe et est disponible
                    $item = \App\Models\Item::where('id', $itemId)
                        ->where('is_available', 1)
                        ->where('menu_id', $menuId) // IMPORTANT: Vérifier que le plat appartient bien au menu indiqué
                        ->first();
                    
                    if (!$item) {
                        \Illuminate\Support\Facades\Log::warning('Plat non disponible ou n\'appartenant pas au menu:', [
                            'item_id' => $itemId, 
                            'menu_id' => $menuId
                        ]);
                        continue;
                    }
                    
                    \Illuminate\Support\Facades\Log::info('Traitement du plat de menu:', [
                        'item_id' => $itemId,
                        'nom' => $item->name,
                        'menu_id' => $menuId,
                        'quantity' => $quantity
                    ]);
                    
                    // Vérifier s'il existe déjà dans la commande
                    $existingOrderItem = \Illuminate\Support\Facades\DB::table('order_items')
                        ->where('order_id', $order->id)
                        ->where('item_id', $itemId)
                        ->first();
                    
                    if ($existingOrderItem) {
                        // Le plat existe déjà, mettre à jour la quantité
                        $result = \Illuminate\Support\Facades\DB::table('order_items')
                            ->where('id', $existingOrderItem->id)
                            ->update([
                                'quantity' => $existingOrderItem->quantity + $quantity,
                                'menu_id' => $menuId, // S'assurer que l'association avec le menu est correcte
                                'updated_at' => now()
                            ]);
                        
                        \Illuminate\Support\Facades\Log::info('Plat de menu existant mis à jour:', [
                            'item_id' => $itemId,
                            'ancien_qte' => $existingOrderItem->quantity,
                            'nouvelle_qte' => $existingOrderItem->quantity + $quantity
                        ]);
                    } else {
                        // Nouveau plat à ajouter
                        $result = \Illuminate\Support\Facades\DB::table('order_items')->insert([
                            'order_id' => $order->id,
                            'item_id' => $itemId,
                            'quantity' => $quantity,
                            'price' => $item->price,
                            'menu_id' => $menuId,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        
                        \Illuminate\Support\Facades\Log::info('Nouveau plat de menu inséré:', [
                            'item_id' => $itemId,
                            'menu_id' => $menuId,
                            'quantité' => $quantity,
                            'prix' => $item->price
                        ]);
                    }
                    
                    $insertCount++;
                    $hasValidItems = true;
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Erreur traitement plat de menu:', [
                        'erreur' => $e->getMessage(),
                        'item_id' => $itemId ?? null,
                        'menu_id' => $menuId ?? null
                    ]);
                }
            }
        }
        
        // Section de traitement via API supprimée car déjà traitée plus haut
        
        // Vérifier qu'au moins un élément a été ajouté à la commande
        if (!$hasValidItems || $insertCount == 0) {
            // Supprimer la commande vide si aucun plat n'a été ajouté
            \Illuminate\Support\Facades\Log::warning('Aucun plat valide n\'a été ajouté à la commande, suppression:', ['order_id' => $order->id]);
            $order->delete();
            return back()->withErrors(['error' => 'Impossible de créer la commande : aucun plat ou menu valide n\'a été sélectionné']);
        }
        
        // Mettre à jour le prix total de la commande
        $order->total_amount = $totalPrice;
        $order->save();
        
        \Illuminate\Support\Facades\Log::info('Commande créée avec succès:', [
            'order_id' => $order->id,
            'total_price' => $totalPrice,
            'items_count' => $insertCount,
            'user_id' => $order->user_id,
            'restaurant_id' => $order->restaurant_id
        ]);
        
        return redirect()->route('admin.orders.show', $order->id)->with('success', 'Commande créée avec succès.');
    }

    /**
     * Met à jour une commande
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'restaurant_id' => 'required|exists:restaurants,id',
            'status' => 'required|in:pending,confirmed,preparing,ready,completed,cancelled',
            'delivery_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.quantity' => 'nullable|integer|min:1',
            'new_items' => 'nullable|array',
            'new_items.*.id' => 'nullable|exists:items,id',
            'new_items.*.quantity' => 'nullable|integer|min:1',
            'existing_menus' => 'nullable|array',
            'existing_menus.*.id' => 'nullable|exists:menus,id',
            'existing_menus.*.quantity' => 'nullable|integer|min:1',
            'new_menus' => 'nullable|array',
            'new_menus.*.id' => 'nullable|exists:menus,id',
            'new_menus.*.quantity' => 'nullable|integer|min:1',
        ]);
        
        $order = \App\Models\Order::findOrFail($id);
        $order->user_id = $request->user_id;
        $order->restaurant_id = $request->restaurant_id;
        $order->status = $request->status;
        $order->delivery_address = $request->delivery_address;
        $order->notes = $request->notes;
        
        // Recalculer le prix total
        $totalPrice = 0;
        $items = [];
        
        // Mettre à jour les quantités des plats existants
        if (!empty($request->items)) {
            foreach ($request->items as $itemId => $itemData) {
                $quantity = $itemData['quantity'];
                // Récupérer le prix depuis la base de données si nécessaire
                $item = \App\Models\Item::find($itemId);
                $price = $item ? $item->price : 0;
                $totalPrice += $price * $quantity;
                
                $items[$itemId] = ['quantity' => $quantity, 'price' => $price];
            }
        }
        
        // Ajouter les nouveaux plats
        if (!empty($request->new_items)) {
            foreach ($request->new_items as $itemId => $itemData) {
                if (empty($itemData['id'])) continue;
                
                // Vérifier que le plat est disponible
                $item = \App\Models\Item::where('id', $itemData['id'])
                    ->where('is_available', 1)
                    ->first();
                
                if (!$item) continue; // Ignorer les plats qui ne sont pas disponibles
                
                $quantity = $itemData['quantity'] ?? 1;
                $totalPrice += $item->price * $quantity;
                
                // Si le plat est déjà dans la commande, augmenter la quantité
                if (isset($items[$item->id])) {
                    $items[$item->id]['quantity'] += $quantity;
                } else {
                    $items[$item->id] = ['quantity' => $quantity, 'price' => $item->price];
                }
            }
        }
        
        // Traiter les menus existants
        if (!empty($request->existing_menus)) {
            \Illuminate\Support\Facades\Log::info('Traitement des menus existants:', ['menus' => $request->existing_menus]);
            
            foreach ($request->existing_menus as $menuId => $menuData) {
                if (empty($menuData['id'])) continue;
                
                $quantity = $menuData['quantity'] ?? 1;
                $menu = \App\Models\Menu::find($menuData['id']);
                
                if (!$menu || !$menu->is_active) {
                    \Illuminate\Support\Facades\Log::warning('Menu existant inactif ou non trouvé:', ['menu_id' => $menuData['id']]);
                    continue;
                }
                
                // Récupérer les plats de ce menu
                $menuItems = \App\Models\Item::where('menu_id', $menu->id)
                    ->where('is_available', 1)
                    ->get();
                
                if ($menuItems->isEmpty()) {
                    \Illuminate\Support\Facades\Log::warning('Menu existant sans plats disponibles:', ['menu_id' => $menu->id]);
                    continue;
                }
                
                foreach ($menuItems as $menuItem) {
                    $items[$menuItem->id] = [
                        'quantity' => $quantity,
                        'price' => $menuItem->price,
                        'menu_id' => $menu->id
                    ];
                    
                    $totalPrice += $menuItem->price * $quantity;
                }
            }
        }
        
        // Ajouter les nouveaux menus
        if (!empty($request->new_menus)) {
            foreach ($request->new_menus as $menuId => $menuData) {
                if (empty($menuData['id'])) continue;
                
                // Vérifier que le menu contient des plats disponibles
                $menu = \App\Models\Menu::find($menuData['id']);
                if (!$menu || !$menu->is_active) continue;
                
                // Récupérer les plats disponibles associés à ce menu
                $menuItems = \App\Models\Item::where('menu_id', $menu->id)
                    ->where('is_available', 1)
                    ->get();
                
                if ($menuItems->isEmpty()) {
                    \Illuminate\Support\Facades\Log::warning('Menu sans plats disponibles ignoré:', ['menu_id' => $menu->id]);
                    continue; // Ignorer les menus sans plats disponibles
                }
                
                \Illuminate\Support\Facades\Log::info('Plats du menu récupérés pour mise à jour:', [
                    'menu_id' => $menu->id, 
                    'count' => $menuItems->count(),
                    'plats' => $menuItems->pluck('name')->toArray()
                ]);
                
                $quantity = $menuData['quantity'] ?? 1;
                
                // Parcourir les plats disponibles du menu
                foreach ($menuItems as $menuItem) {
                    // Si le plat est déjà dans la commande avec un autre menu, on le remplace
                    // car un plat ne peut être associé qu'à un seul menu
                    if (isset($items[$menuItem->id])) {
                        // Si c'est déjà un plat du même menu, on cumule les quantités
                        if (isset($items[$menuItem->id]['menu_id']) && $items[$menuItem->id]['menu_id'] == $menu->id) {
                            $items[$menuItem->id]['quantity'] += $quantity;
                        } else {
                            // Sinon on remplace (plat individuel ou d'un autre menu)
                            $items[$menuItem->id] = ['quantity' => $quantity, 'price' => $menuItem->price, 'menu_id' => $menu->id];
                        }
                    } else {
                        // Nouveau plat du menu
                        $items[$menuItem->id] = ['quantity' => $quantity, 'price' => $menuItem->price, 'menu_id' => $menu->id];
                    }
                    
                    $totalPrice += $menuItem->price * $quantity;
                }
            }
        }
        
        $order->total_amount = $totalPrice; // Utiliser total_amount car c'est le nom de la colonne dans la base de données
        $order->save();
        
        // Sync synchronise les relations en supprimant celles qui ne sont plus présentes
        // et en ajoutant/mettant à jour celles qui le sont
        $order->items()->sync($items);
        
        return redirect()->route('admin.orders.index')->with('success', 'Commande mise à jour avec succès.');
    }

    /**
     * Supprime une commande
     */
    public function destroy($id)
    {
        $order = \App\Models\Order::findOrFail($id);
        
        // Détache tous les plats associés à la commande (cela supprime les entrées dans la table pivot)
        $order->items()->detach();
        
        // Supprime la commande elle-même
        $order->delete();
        
        return redirect()->route('admin.orders.index')->with('success', 'Commande supprimée avec succès.');
    }

    /**
     * Annule une commande
     */
    public function cancel($id)
    {
        $order = \App\Models\Order::findOrFail($id);
        $order->status = 'cancelled';
        $order->save();
        
        return redirect()->route('admin.orders.index')->with('success', 'Commande annulée avec succès.');
    }
}
