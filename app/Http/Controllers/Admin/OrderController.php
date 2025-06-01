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
        $order = \App\Models\Order::with(['user', 'restaurant', 'items'])->findOrFail($id);
        
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
        
        return view('admin.orders.edit', compact('order', 'availableItems', 'availableMenus'));
    }

    /**
     * Stocke une nouvelle commande
     */
    public function store(Request $request)
    {
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'restaurant_id' => 'required|exists:restaurants,id',
            'status' => 'required|in:pending,confirmed,preparing,ready,completed,cancelled',
            'delivery_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.id' => 'nullable|exists:items,id',
            'items.*.quantity' => 'nullable|integer|min:1',
            'menus' => 'nullable|array',
            'menus.*.id' => 'nullable|exists:menus,id',
            'menus.*.quantity' => 'nullable|integer|min:1',
            'reservation_id' => 'nullable|exists:reservations,id',
        ]);

        // Vérifier qu'au moins un plat ou un menu est sélectionné
        if (empty($request->items) && empty($request->menus)) {
            return back()->withErrors(['error' => 'Vous devez sélectionner au moins un plat ou un menu']);
        }

        // Créer la commande
        $order = new \App\Models\Order();
        $order->user_id = $request->user_id;
        $order->restaurant_id = $request->restaurant_id;
        $order->status = $request->status;
        $order->delivery_address = $request->delivery_address;
        $order->notes = $request->notes;
        $order->reservation_id = $request->reservation_id; // Associer à une réservation si spécifiée
        
        // Calculer le prix total
        $totalPrice = 0;
        $items = [];
        $menuItems = [];
        
        // Ajouter les plats individuels
        if (!empty($request->items)) {
            foreach ($request->items as $itemId => $itemData) {
                // Ignorer les items avec l'attribut is_from_menu pour le calcul du prix total
                // Car ces plats sont déjà comptabilisés dans le prix du menu
                $isFromMenu = isset($itemData['is_from_menu']) && $itemData['is_from_menu'] == 1;
                
                // Vérifier que l'item est actif/disponible avant de l'ajouter
                $item = \App\Models\Item::where('id', $itemId)
                    ->where('is_available', 1)
                    ->firstOrFail();
                    
                $quantity = $itemData['quantity'];
                
                // Ajouter au prix total seulement si ce n'est pas un plat provenant d'un menu
                if (!$isFromMenu) {
                    $totalPrice += $item->price * $quantity;
                }
                
                // Préparer les données pour l'association plat-commande
                $syncData = ['quantity' => $quantity, 'price' => $item->price];
                
                // Si le plat fait partie d'un menu, ajouter l'ID du menu
                if (isset($itemData['menu_id'])) {
                    $syncData['menu_id'] = $itemData['menu_id'];
                }
                
                // Avec la nouvelle structure, chaque item n'apparaît qu'une seule fois car indexé par son ID
                $items[$item->id] = $syncData;
            }
        }
        
        // Ajouter les menus
        if (!empty($request->menus)) {
            foreach ($request->menus as $menuData) {
                // Chargement du menu avec ses plats actifs uniquement
                $menu = \App\Models\Menu::with(['items' => function($query) {
                    $query->where('is_available', 1);
                }])->findOrFail($menuData['id']);
                
                // Vérifier que le menu contient au moins un plat actif
                if (count($menu->items) == 0) {
                    continue; // Ignorer ce menu s'il n'a pas de plats actifs
                }
                
                $quantity = $menuData['quantity'];
                $totalPrice += $menu->price * $quantity;
                
                // Ajouter uniquement les plats actifs du menu à la commande
                foreach ($menu->items as $menuItem) {
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
                }
            }
        }
        
        $order->total_amount = $totalPrice; // Utiliser total_amount car c'est le nom de la colonne dans la base de données
        $order->save();
        
        // Utiliser sync() pour attacher les plats à la commande (comme dans la méthode update)
        // Cette méthode gère correctement les quantités et remplace attach()
        $order->items()->sync($items);
        
        return redirect()->route('admin.orders.index')
            ->with('success', 'Commande créée avec succès');
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
        
        // Ajouter les nouveaux menus
        if (!empty($request->new_menus)) {
            foreach ($request->new_menus as $menuId => $menuData) {
                if (empty($menuData['id'])) continue;
                
                // Vérifier que le menu contient des plats disponibles
                $menu = \App\Models\Menu::with(['items' => function($query) {
                    $query->where('is_available', 1);
                }])->find($menuData['id']);
                
                if (!$menu || $menu->items->isEmpty()) continue; // Ignorer les menus sans plats disponibles
                
                $quantity = $menuData['quantity'] ?? 1;
                
                // Parcourir les plats disponibles du menu
                foreach ($menu->items as $menuItem) {
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
