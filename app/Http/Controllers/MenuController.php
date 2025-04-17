<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Item;
use App\Models\Restaurant;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    /**
     * Affiche la liste des menus (corrigé pour restaurateur multi-restaurants)
     */
    public function index(Request $request, Restaurant $restaurant = null)
    {
        $user = Auth::user();
        $menus = collect();
        $restaurants = collect();
        
        if ($user->isRestaurateur()) {
            // Récupérer tous les restaurants du restaurateur
            $restaurants = $user->restaurants()->orderBy('name')->get();
            // Si un restaurant est sélectionné (filtrage), on affiche ses menus
            if ($request->filled('restaurant') && $request->restaurant !== 'all') {
                $restaurant = $restaurants->where('id', $request->restaurant)->first();
                if (!$restaurant) {
                    abort(403, 'Ce restaurant ne vous appartient pas.');
                }
                $menus = $restaurant->menus()->with('restaurant')->get();
            } else {
                // Tous les menus de tous ses restaurants
                $menus = Menu::with('restaurant')->whereIn('restaurant_id', $restaurants->pluck('id'))->get();
            }
        } else {
            // Gestion existante pour les autres rôles
            if ($restaurant) {
                $menus = Menu::where('restaurant_id', $restaurant->id)->with('restaurant')->get();
            } else {
                $menus = Menu::with('restaurant')->get();
            }
        }
        
        // Gestion du tri
        $sort = $request->input('sort');
        if ($menus->count() > 0 && $sort) {
            switch ($sort) {
                case 'name_asc':
                    $menus = $menus->sortBy('name');
                    break;
                case 'name_desc':
                    $menus = $menus->sortByDesc('name');
                    break;
                case 'price_asc':
                    $menus = $menus->sortBy('price');
                    break;
                case 'price_desc':
                    $menus = $menus->sortByDesc('price');
                    break;
            }
            $menus = $menus->values(); // reset keys
        }
        
        return view('menus.index', compact('menus', 'restaurant', 'restaurants'));
    }

    /**
     * Affiche le formulaire de création d'un menu
     */
    public function create(Restaurant $restaurant)
    {
        // Vérifier que l'utilisateur est un restaurateur
        if (!Auth::user()->isRestaurateur()) {
            abort(403, 'Vous n\'avez pas le droit de créer un menu.');
        }
        
        // Vérifier que le restaurant appartient à l'utilisateur
        if ($restaurant->user_id !== Auth::id()) {
            abort(403, 'Ce restaurant ne vous appartient pas.');
        }
        
        // Récupérer les plats disponibles pour le restaurant
        $categoryIds = Category::where('restaurant_id', $restaurant->id)->pluck('id');
        $items = Item::whereIn('category_id', $categoryIds)->get();
        
        return view('menus.create', compact('restaurant', 'items'));
    }

    /**
     * Enregistre un nouveau menu
     */
    public function store(Request $request, Restaurant $restaurant)
    {
        // Vérifier que l'utilisateur est un restaurateur
        if (!Auth::user()->isRestaurateur()) {
            abort(403, 'Vous n\'avez pas le droit de créer un menu.');
        }
        
        // Vérifier que le restaurant appartient à l'utilisateur
        if ($restaurant->user_id !== Auth::id()) {
            abort(403, 'Ce restaurant ne vous appartient pas.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'items' => 'required|array',
            'items.*' => 'exists:items,id',
        ]);
        
        // Vérifier que tous les plats sélectionnés appartiennent au restaurant
        $categoryIds = Category::where('restaurant_id', $restaurant->id)->pluck('id');
        $itemsCount = Item::whereIn('category_id', $categoryIds)
            ->whereIn('id', $request->items)
            ->count();
            
        if ($itemsCount !== count($request->items)) {
            return back()->withErrors(['items' => 'Certains plats sélectionnés n\'appartiennent pas au restaurant choisi.']);
        }
        
        // Créer le menu
        $menu = Menu::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'restaurant_id' => $restaurant->id,
        ]);
        
        // Associer les plats au menu
        $menu->items()->attach($request->items);
        
        return redirect()->route('restaurants.menus.index', $restaurant)
            ->with('success', 'Menu créé avec succès.');
    }

    /**
     * Affiche les détails d'un menu
     */
    public function show(Restaurant $restaurant, Menu $menu)
    {
        // Vérifier que le menu appartient bien au restaurant
        if ($menu->restaurant_id !== $restaurant->id) {
            abort(404, 'Ce menu n\'appartient pas à ce restaurant.');
        }
        
        // Vérifier que l'utilisateur a le droit de voir ce menu
        if (Auth::user()->isRestaurateur() && $restaurant->user_id !== Auth::id()) {
            abort(403, 'Vous n\'avez pas le droit de voir ce menu.');
        }
        
        // Charger les relations
        $menu->load(['restaurant', 'items']);
        
        return view('menus.show', compact('menu', 'restaurant'));
    }

    /**
     * Affiche le formulaire d'édition d'un menu
     */
    public function edit(Restaurant $restaurant, Menu $menu)
    {
        // Vérifier que le menu appartient bien au restaurant
        if ($menu->restaurant_id !== $restaurant->id) {
            abort(404, 'Ce menu n\'appartient pas à ce restaurant.');
        }
        
        // Vérifier que l'utilisateur est un restaurateur
        if (!Auth::user()->isRestaurateur()) {
            abort(403, 'Vous n\'avez pas le droit de modifier un menu.');
        }
        
        // Vérifier que le restaurant appartient à l'utilisateur
        if ($restaurant->user_id !== Auth::id()) {
            abort(403, 'Ce restaurant ne vous appartient pas.');
        }
        
        // Récupérer les plats disponibles pour le restaurant du menu
        $categoryIds = Category::where('restaurant_id', $restaurant->id)->pluck('id');
        $items = Item::whereIn('category_id', $categoryIds)->get();
        
        // Récupérer les IDs des plats associés au menu
        $selectedItems = $menu->items->pluck('id')->toArray();
        
        return view('menus.edit', compact('menu', 'restaurant', 'items', 'selectedItems'));
    }

    /**
     * Met à jour un menu
     */
    public function update(Request $request, Restaurant $restaurant, Menu $menu)
    {
        // Vérifier que le menu appartient bien au restaurant
        if ($menu->restaurant_id !== $restaurant->id) {
            abort(404, 'Ce menu n\'appartient pas à ce restaurant.');
        }
        
        // Vérifier que l'utilisateur est un restaurateur
        if (!Auth::user()->isRestaurateur()) {
            abort(403, 'Vous n\'avez pas le droit de modifier un menu.');
        }
        
        // Vérifier que le restaurant appartient à l'utilisateur
        if ($restaurant->user_id !== Auth::id()) {
            abort(403, 'Ce restaurant ne vous appartient pas.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'items' => 'required|array',
            'items.*' => 'exists:items,id',
        ]);
        
        // Vérifier que tous les plats sélectionnés appartiennent au restaurant
        $categoryIds = Category::where('restaurant_id', $restaurant->id)->pluck('id');
        $itemsCount = Item::whereIn('category_id', $categoryIds)
            ->whereIn('id', $request->items)
            ->count();
            
        if ($itemsCount !== count($request->items)) {
            return back()->withErrors(['items' => 'Certains plats sélectionnés n\'appartiennent pas au restaurant choisi.']);
        }
        
        // Mettre à jour le menu
        $menu->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
        ]);
        
        // Mettre à jour les plats associés au menu
        $menu->items()->sync($request->items);
        
        return redirect()->route('restaurants.menus.index', $restaurant)
            ->with('success', 'Menu mis à jour avec succès.');
    }

    /**
     * Supprime un menu
     */
    public function destroy(Restaurant $restaurant, Menu $menu)
    {
        // Vérifier que le menu appartient bien au restaurant
        if ($menu->restaurant_id !== $restaurant->id) {
            abort(404, 'Ce menu n\'appartient pas à ce restaurant.');
        }
        
        // Vérifier que l'utilisateur est un restaurateur
        if (!Auth::user()->isRestaurateur()) {
            abort(403, 'Vous n\'avez pas le droit de supprimer un menu.');
        }
        
        // Vérifier que le restaurant appartient à l'utilisateur
        if ($restaurant->user_id !== Auth::id()) {
            abort(403, 'Ce restaurant ne vous appartient pas.');
        }
        
        // Supprimer le menu
        $menu->delete();
        
        return redirect()->route('restaurants.menus.index', $restaurant)
            ->with('success', 'Menu supprimé avec succès.');
    }
}
