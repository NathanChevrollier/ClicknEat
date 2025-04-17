<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Item;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    /**
     * Vérifie si l'utilisateur est un administrateur
     */
    private function checkAdmin()
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Accès réservé aux administrateurs.');
        }
    }

    /**
     * Affiche la liste des menus
     */
    public function index(Request $request)
    {
        $this->checkAdmin();
        
        // Vérifier si un restaurant spécifique est demandé
        $restaurant = null;
        
        if ($request->has('restaurant_id')) {
            $restaurantId = $request->restaurant_id;
            $restaurant = Restaurant::findOrFail($restaurantId);
            $menus = Menu::where('restaurant_id', $restaurantId)->with('restaurant')->get();
        } else {
            $menus = Menu::with('restaurant')->get();
        }
        
        return view('admin.menus.index', compact('menus', 'restaurant'));
    }

    /**
     * Affiche le formulaire de création d'un menu
     */
    public function create(Request $request)
    {
        $this->checkAdmin();
        
        // Récupérer tous les restaurants
        $restaurants = Restaurant::all();
        
        // Pré-sélectionner un restaurant si spécifié dans l'URL
        $selectedRestaurantId = $request->has('restaurant_id') ? $request->restaurant_id : null;
        
        // Récupérer les plats pour l'affichage initial
        $items = [];
        if ($selectedRestaurantId) {
            $items = Item::whereHas('category', function($query) use ($selectedRestaurantId) {
                $query->where('restaurant_id', $selectedRestaurantId);
            })->get();
        }
        
        return view('admin.menus.create', compact('restaurants', 'items', 'selectedRestaurantId'));
    }

    /**
     * Enregistre un nouveau menu
     */
    public function store(Request $request)
    {
        $this->checkAdmin();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'restaurant_id' => 'required|exists:restaurants,id',
            'items' => 'required|array',
            'items.*' => 'exists:items,id',
        ]);
        
        // Convertir le prix en centimes pour le stockage
        $price = $request->price * 100;
        
        // Créer le menu
        $menu = Menu::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $price,
            'restaurant_id' => $request->restaurant_id,
        ]);
        
        // Associer les plats au menu
        $menu->items()->attach($request->items);
        
        return redirect()->route('admin.menus.index')
            ->with('success', 'Menu créé avec succès');
    }

    /**
     * Affiche les détails d'un menu
     */
    public function show(Menu $menu)
    {
        $this->checkAdmin();
        $menu->load('items.category', 'restaurant.user');
        return view('admin.menus.show', compact('menu'));
    }

    /**
     * Affiche le formulaire d'édition d'un menu
     */
    public function edit(Menu $menu)
    {
        $this->checkAdmin();
        
        // Récupérer tous les restaurants
        $restaurants = Restaurant::all();
        
        // Récupérer les plats du restaurant
        $items = Item::whereHas('category', function($query) use ($menu) {
            $query->where('restaurant_id', $menu->restaurant_id);
        })->get();
        
        // Récupérer les IDs des plats sélectionnés
        $selectedItems = $menu->items->pluck('id')->toArray();
        
        return view('admin.menus.edit', compact('menu', 'restaurants', 'items', 'selectedItems'));
    }

    /**
     * Met à jour un menu
     */
    public function update(Request $request, Menu $menu)
    {
        $this->checkAdmin();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'restaurant_id' => 'required|exists:restaurants,id',
            'items' => 'required|array',
            'items.*' => 'exists:items,id',
        ]);
        
        // Convertir le prix en centimes pour le stockage
        $price = $request->price * 100;
        
        // Mettre à jour le menu
        $menu->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $price,
            'restaurant_id' => $request->restaurant_id,
        ]);
        
        // Mettre à jour les plats associés
        $menu->items()->sync($request->items);
        
        return redirect()->route('admin.menus.index')
            ->with('success', 'Menu mis à jour avec succès');
    }

    /**
     * Supprime un menu
     */
    public function destroy(Menu $menu)
    {
        $this->checkAdmin();
        
        // Détacher d'abord les plats associés (relation many-to-many)
        $menu->items()->detach();
        
        // Supprimer le menu
        $menu->delete();
        
        return redirect()->route('admin.menus.index')
            ->with('success', 'Menu supprimé avec succès');
    }
}
