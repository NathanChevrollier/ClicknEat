<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index(Request $request, Restaurant $restaurant) {
        $user = Auth::user();
        
        // Vérifier que le restaurant appartient à l'utilisateur si c'est un restaurateur
        if ($user->isRestaurateur()) {
            $restaurantIds = $user->restaurants()->pluck('id');
            if ($request->filled('restaurant') && $request->restaurant !== 'all') {
                $restaurant = $user->restaurants()->where('id', $request->restaurant)->first();
                if (!$restaurant) {
                    abort(403, 'Ce restaurant ne vous appartient pas.');
                }
                $categories = Category::where('restaurant_id', $restaurant->id)
                    ->orderBy('name')
                    ->get();
            } else {
                $categories = Category::whereIn('restaurant_id', $restaurantIds)
                    ->orderBy('name')
                    ->get();
            }
        } else {
            $categories = Category::where('restaurant_id', $restaurant->id)
                ->orderBy('name')
                ->get();
        }
        
        // Gestion du tri
        $sort = request('sort');
        if ($categories->count() > 0 && $sort) {
            switch ($sort) {
                case 'name_asc':
                    $categories = $categories->sortBy('name');
                    break;
                case 'name_desc':
                    $categories = $categories->sortByDesc('name');
                    break;
            }
            $categories = $categories->values();
        }
        
        return view('categories.index', compact('categories', 'restaurant'));
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
