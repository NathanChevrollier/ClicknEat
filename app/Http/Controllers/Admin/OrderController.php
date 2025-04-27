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
        // À compléter selon la logique métier
        return redirect()->route('admin.orders.index')->with('success', 'Commande créée (exemple).');
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
