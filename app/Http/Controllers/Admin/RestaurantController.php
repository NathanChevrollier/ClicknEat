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
    public function index(Request $request)
    {
        $query = Restaurant::with('user'); // Préchargement de la relation user pour éviter les problèmes N+1
        
        // Recherche par nom, adresse ou propriétaire
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Tri des restaurants
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        // Vérifier que le champ de tri est valide
        $validSortFields = ['id', 'name', 'address', 'created_at'];
        
        if (!in_array($sortField, $validSortFields)) {
            $sortField = 'created_at';
        }
        
        // Tri spécial pour le propriétaire (nécessite une jointure)
        if ($sortField === 'user') {
            $query->join('users', 'restaurants.user_id', '=', 'users.id')
                  ->orderBy('users.name', $sortDirection === 'asc' ? 'asc' : 'desc')
                  ->select('restaurants.*'); // Important pour éviter les conflits de colonnes
        } else {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }
        
        $restaurants = $query->paginate(10)->withQueryString();
        
        return view('admin.restaurants.index', compact('restaurants', 'sortField', 'sortDirection'));
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
        
        // Récupérer les avis du restaurant
        $reviews = \App\Models\Review::where('restaurant_id', $restaurant->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Calculer la moyenne des notes
        $averageRating = 0;
        if ($reviews->count() > 0) {
            $averageRating = $reviews->avg('rating');
        }
        
        return view('admin.restaurants.show', compact('restaurant', 'items', 'itemsCount', 'reviews', 'averageRating'));
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
