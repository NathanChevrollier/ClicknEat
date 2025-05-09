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
    public function create()
    {
        return view('admin.orders.create');
    }

    /**
     * Affiche le détail d'une commande
     */
    public function show($id)
    {
        return view('admin.orders.show');
    }

    /**
     * Affiche le formulaire d'édition d'une commande
     */
    public function edit($id)
    {
        return view('admin.orders.edit');
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
        
        // Calculer le prix total
        $totalPrice = 0;
        $items = [];
        $menuItems = [];
        
        // Ajouter les plats individuels
        if (!empty($request->items)) {
            foreach ($request->items as $itemData) {
                $item = \App\Models\Item::findOrFail($itemData['id']);
                $quantity = $itemData['quantity'];
                $totalPrice += $item->price * $quantity;
                
                $items[$item->id] = ['quantity' => $quantity, 'price' => $item->price];
            }
        }
        
        // Ajouter les menus
        if (!empty($request->menus)) {
            foreach ($request->menus as $menuData) {
                $menu = \App\Models\Menu::with('items')->findOrFail($menuData['id']);
                $quantity = $menuData['quantity'];
                $totalPrice += $menu->price * $quantity;
                
                // Ajouter les plats du menu à la commande
                foreach ($menu->items as $menuItem) {
                    // Si le plat est déjà dans la commande, augmenter la quantité
                    if (isset($items[$menuItem->id])) {
                        $items[$menuItem->id]['quantity'] += $quantity;
                    } else {
                        $items[$menuItem->id] = ['quantity' => $quantity, 'price' => $menuItem->price, 'menu_id' => $menu->id];
                    }
                }
            }
        }
        
        $order->total_price = $totalPrice;
        $order->save();
        
        // Attacher les plats à la commande
        $order->items()->attach($items);
        
        return redirect()->route('admin.orders.index')
            ->with('success', 'Commande créée avec succès');
    }

    /**
     * Met à jour une commande
     */
    public function update(Request $request, $id)
    {
        // À compléter selon la logique métier
        return redirect()->route('admin.orders.index')->with('success', 'Commande mise à jour (exemple).');
    }

    /**
     * Supprime une commande
     */
    public function destroy($id)
    {
        // À compléter selon la logique métier
        return redirect()->route('admin.orders.index')->with('success', 'Commande supprimée (exemple).');
    }
}
