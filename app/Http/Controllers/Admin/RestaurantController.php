<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Restaurant;

class RestaurantController extends Controller
{
    /**
     * Affiche la liste des restaurants
     */
    public function index()
    {
        $restaurants = Restaurant::paginate(10);
        return view('admin.restaurants.index', compact('restaurants'));
    }

    /**
     * Affiche le formulaire de création d'un restaurant
     */
    public function create()
    {
        return view('admin.restaurants.create');
    }

    /**
     * Enregistre un nouveau restaurant
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            // Ajoute d'autres champs nécessaires ici
        ]);

        Restaurant::create($request->all());
        return redirect()->route('admin.restaurants.index')->with('success', 'Restaurant créé avec succès');
    }

    /**
     * Affiche le détail d'un restaurant
     */
    public function show(Restaurant $restaurant)
    {
        // Charger explicitement les relations nécessaires
        $restaurant->load(['user', 'categories', 'menus', 'menus.items']);
        
        // Récupérer les plats du restaurant via ses catégories
        $items = \App\Models\Item::whereHas('category', function($query) use ($restaurant) {
            $query->where('restaurant_id', $restaurant->id);
        })->with('category')->get();
        
        $itemsCount = $items->count();
        
        return view('admin.restaurants.show', compact('restaurant', 'items', 'itemsCount'));
    }

    /**
     * Affiche le formulaire d'édition d'un restaurant
     */
    public function edit(Restaurant $restaurant)
    {
        return view('admin.restaurants.edit', compact('restaurant'));
    }

    /**
     * Met à jour un restaurant
     */
    public function update(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            // Ajoute d'autres champs nécessaires ici
        ]);

        $restaurant->update($request->all());
        return redirect()->route('admin.restaurants.index')->with('success', 'Restaurant mis à jour avec succès');
    }

    /**
     * Supprime un restaurant
     */
    public function destroy(Restaurant $restaurant)
    {
        $restaurant->delete();
        return redirect()->route('admin.restaurants.index')->with('success', 'Restaurant supprimé avec succès');
    }
}
