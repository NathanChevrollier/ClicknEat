<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index(Request $request, Restaurant $restaurant = null) {
        $user = Auth::user();
        $restaurants = collect();
        
        // Récupérer le paramètre de tri
        $sort = $request->input('sort', 'name_asc'); // Par défaut, tri par nom croissant
        
        // Initialiser la requête de base
        $query = Category::query();
        
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
                        // Afficher toutes les catégories du restaurateur au lieu d'une erreur 403
                        $restaurant = null; // Réinitialiser pour afficher "tous les restaurants"
                        $query->whereIn('restaurant_id', $restaurantIds);
                    } else {
                        $restaurant = $selectedRestaurant; // Assigner pour l'affichage dans la vue
                        $query->where('restaurant_id', $selectedRestaurant->id);
                    }
                }
            } else {
                // Toutes les catégories de tous ses restaurants (cas par défaut)
                $restaurant = null; // Explicitement null pour le cas par défaut
                $query->whereIn('restaurant_id', $restaurantIds);
            }
        } else if ($restaurant) {
            // Pour les autres rôles (admin, client) avec un restaurant spécifié
            $query->where('restaurant_id', $restaurant->id);
        }
        
        // Application du tri directement au niveau SQL avec préfixe de table pour éviter les ambigüités
        switch ($sort) {
            case 'name_desc':
                $query->orderBy('categories.name', 'desc');
                break;
            case 'name_asc':
            default:
                $query->orderBy('categories.name', 'asc');
                break;
        }
        
        // Exécution de la requête
        $categories = $query->get();
        
        return view('categories.index', compact('categories', 'restaurant', 'restaurants', 'sort'));
    }

    public function create(Restaurant $restaurant) {
        // Vérifier que l'utilisateur est un restaurateur
        if (!Auth::user()->isRestaurateur()) {
            abort(403, 'Vous n\'avez pas le droit de créer une catégorie.');
        }
        
        // Vérifier que le restaurant appartient à l'utilisateur
        if ($restaurant->user_id !== Auth::id()) {
            abort(403, 'Ce restaurant ne vous appartient pas.');
        }
        
        return view('categories.create', compact('restaurant'));
    }

    public function store(Request $request, Restaurant $restaurant) {
        // Vérifier que l'utilisateur est un restaurateur
        if (!Auth::user()->isRestaurateur()) {
            abort(403, 'Vous n\'avez pas le droit de créer une catégorie.');
        }
        
        // Vérifier que le restaurant appartient à l'utilisateur connecté
        if ($restaurant->user_id !== Auth::id()) {
            abort(403, 'Ce restaurant ne vous appartient pas.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        
        // Créer la catégorie
        $category = Category::create([
            'name' => $request->name,
            'restaurant_id' => $restaurant->id,
        ]);
        
        return redirect()->route('restaurants.categories.index', $restaurant)
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function show(Restaurant $restaurant, Category $category) {
        // Vérifier que la catégorie appartient bien au restaurant
        if ($category->restaurant_id !== $restaurant->id) {
            abort(404, 'Cette catégorie n\'appartient pas à ce restaurant.');
        }
        
        // Vérifier que l'utilisateur a le droit de voir cette catégorie
        if (Auth::user()->isRestaurateur() && $restaurant->user_id !== Auth::id()) {
            abort(403, 'Ce restaurant ne vous appartient pas.');
        }
        
        return view('categories.show', compact('category', 'restaurant'));
    }

    public function edit(Restaurant $restaurant, Category $category) {
        // Vérifier que la catégorie appartient bien au restaurant
        if ($category->restaurant_id !== $restaurant->id) {
            abort(404, 'Cette catégorie n\'appartient pas à ce restaurant.');
        }
        
        // Vérifier que l'utilisateur est un restaurateur
        if (!Auth::user()->isRestaurateur()) {
            abort(403, 'Vous n\'avez pas le droit de modifier une catégorie.');
        }
        
        // Vérifier que le restaurant appartient à l'utilisateur
        if ($restaurant->user_id !== Auth::id()) {
            abort(403, 'Ce restaurant ne vous appartient pas.');
        }
        
        return view('categories.edit', compact('category', 'restaurant'));
    }

    public function update(Request $request, Restaurant $restaurant, Category $category) {
        // Vérifier que la catégorie appartient bien au restaurant
        if ($category->restaurant_id !== $restaurant->id) {
            abort(404, 'Cette catégorie n\'appartient pas à ce restaurant.');
        }
        
        // Vérifier que l'utilisateur est un restaurateur
        if (!Auth::user()->isRestaurateur()) {
            abort(403, 'Vous n\'avez pas le droit de modifier une catégorie.');
        }
        
        // Vérifier que le restaurant appartient à l'utilisateur
        if ($restaurant->user_id !== Auth::id()) {
            abort(403, 'Ce restaurant ne vous appartient pas.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        
        $category->update([
            'name' => $request->name,
        ]);
        
        return redirect()->route('restaurants.categories.index', $restaurant)
            ->with('success', 'Catégorie mise à jour avec succès.');
    }

    public function destroy(Restaurant $restaurant, Category $category) {
        // Vérifier que la catégorie appartient bien au restaurant
        if ($category->restaurant_id !== $restaurant->id) {
            abort(404, 'Cette catégorie n\'appartient pas à ce restaurant.');
        }
        
        // Vérifier que l'utilisateur est un restaurateur
        if (!Auth::user()->isRestaurateur()) {
            abort(403, 'Vous n\'avez pas le droit de supprimer une catégorie.');
        }
        
        // Vérifier que le restaurant appartient à l'utilisateur
        if ($restaurant->user_id !== Auth::id()) {
            abort(403, 'Ce restaurant ne vous appartient pas.');
        }
        
        $category->delete();
        
        return redirect()->route('restaurants.categories.index', $restaurant)
            ->with('success', 'Catégorie supprimée avec succès.');
    }
}
