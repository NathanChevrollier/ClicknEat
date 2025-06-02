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
        $restaurants = collect();
        
        // Récupérer les paramètres de filtrage et tri
        $sort = $request->input('sort', 'name_asc'); // Par défaut, tri par nom croissant
        
        // Initialiser la requête de base
        $query = Menu::with(['restaurant', 'items']);
        
        // Gestion des filtres selon le rôle
        if ($user->isRestaurateur()) {
            // Récupérer tous les restaurants du restaurateur
            $restaurants = $user->restaurants()->orderBy('name')->get();
            $restaurantIds = $restaurants->pluck('id')->toArray();
            
            // Si un restaurant est fourni directement dans la route
            if ($restaurant) {
                if (!in_array($restaurant->id, $restaurantIds)) {
                    // Rediriger vers la liste des restaurants au lieu d'afficher une erreur 403
                    return redirect()->route('restaurants.index')
                        ->with('error', 'Vous n\'avez pas accès à ce restaurant.');
                }
                $query->where('restaurant_id', $restaurant->id);
            }
            // Si un restaurant est sélectionné via un paramètre de requête
            else if ($request->filled('restaurant')) {
                if ($request->restaurant === 'all') {
                    // Explicitement "Tous les restaurants"
                    $restaurant = null; // Pas de restaurant spécifique sélectionné
                    $query->whereIn('restaurant_id', $restaurantIds);
                } else {
                    $selectedRestaurant = $restaurants->where('id', $request->restaurant)->first();
                    if (!$selectedRestaurant) {
                        // Afficher tous les menus du restaurateur au lieu d'une erreur 403
                        $restaurant = null; // Réinitialiser pour afficher "tous les restaurants"
                        $query->whereIn('restaurant_id', $restaurantIds);
                    } else {
                        $restaurant = $selectedRestaurant; // Assigner pour l'affichage dans la vue
                        $query->where('restaurant_id', $selectedRestaurant->id);
                    }
                }
            } else {
                // Tous les menus de tous ses restaurants (cas par défaut)
                $restaurant = null; // Explicitement null pour le cas par défaut
                $query->whereIn('restaurant_id', $restaurantIds);
            }
        } else {
            // Gestion existante pour les autres rôles (admin, client)
            if ($restaurant) {
                $query->where('restaurant_id', $restaurant->id);
            }
        }
        
        // Application du tri directement au niveau SQL
        // Application explicite des colonnes complètes pour éviter les ambiguïtés
        switch ($sort) {
            case 'name_desc':
                $query->orderBy('menus.name', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('menus.price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('menus.price', 'desc');
                break;
            case 'name_asc':
            default:
                $query->orderBy('menus.name', 'asc');
                break;
        }
        
        // Exécution de la requête avec pagination
        $menus = $query->paginate(15)->appends($request->except('page'));
        
        return view('menus.index', compact('menus', 'restaurant', 'restaurants', 'sort'));
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
        
        // Vérifier si des plats sont déjà associés à d'autres menus
        $itemsInOtherMenus = Item::whereIn('id', $request->items)
            ->whereNotNull('menu_id')
            ->get();
        
        if ($itemsInOtherMenus->count() > 0) {
            // Collecter les noms des plats déjà attribués pour l'affichage
            $itemNames = $itemsInOtherMenus->pluck('name')->implode(', ');
            
            return back()
                ->withInput()
                ->withErrors(['items' => "Les plats suivants sont déjà associés à d'autres menus : {$itemNames}. Un plat ne peut être associé qu'à un seul menu."]);
        }
        
        // Créer le menu
        $menu = Menu::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'restaurant_id' => $restaurant->id,
        ]);
        
        // Associer les plats au menu en mettant à jour leur menu_id
        Item::whereIn('id', $request->items)->update(['menu_id' => $menu->id]);
        
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
        
        // Vérifier si des plats sélectionnés sont déjà associés à d'autres menus
        $itemsInOtherMenus = Item::whereIn('id', $request->items)
            ->whereNotNull('menu_id')
            ->where('menu_id', '!=', $menu->id) // Exclure les plats déjà associés à ce menu
            ->get();
        
        if ($itemsInOtherMenus->count() > 0) {
            // Collecter les noms des plats déjà attribués pour l'affichage
            $itemNames = $itemsInOtherMenus->pluck('name')->implode(', ');
            
            return back()
                ->withInput()
                ->withErrors(['items' => "Les plats suivants sont déjà associés à d'autres menus : {$itemNames}. Un plat ne peut être associé qu'à un seul menu."]);
        }
        
        // Mettre à jour le menu
        $menu->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
        ]);
        
        // Dissocier les plats actuellement associés à ce menu
        Item::where('menu_id', $menu->id)->update(['menu_id' => null]);
        
        // Associer les nouveaux plats au menu
        if ($request->has('items')) {
            Item::whereIn('id', $request->items)->update(['menu_id' => $menu->id]);
        }
        
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
