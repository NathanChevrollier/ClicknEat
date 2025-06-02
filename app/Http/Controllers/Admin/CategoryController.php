<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
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
     * Affiche la liste des catégories
     */
    public function index(Request $request)
    {
        $this->checkAdmin();
        
        // Initialiser la requête de base
        $query = Category::with(['restaurant', 'items']);
        $restaurant = null;
        
        // Filtrer par restaurant si spécifié
        if ($request->has('restaurant_id')) {
            $restaurantId = $request->restaurant_id;
            $restaurant = Restaurant::findOrFail($restaurantId);
            $query->where('restaurant_id', $restaurantId);
        }
        
        // Recherche textuelle si spécifiée
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Gestion du tri
        $sortField = $request->input('sort', 'id');
        $sortDirection = $request->input('direction', 'asc');
        
        // Valider les champs de tri autorisés
        $allowedSortFields = ['id', 'name'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'id';
        }
        
        // Tri spécial pour les champs de relation
        if ($sortField === 'restaurant') {
            $query->join('restaurants', 'categories.restaurant_id', '=', 'restaurants.id')
                  ->select('categories.*')
                  ->orderBy('restaurants.name', $sortDirection);
        } else {
            $query->orderBy($sortField, $sortDirection);
        }
        
        // Pagination (15 éléments par page)
        $categories = $query->paginate(15);
        
        return view('admin.categories.index', compact('categories', 'restaurant'));
    }

    /**
     * Affiche le formulaire de création d'une catégorie
     */
    public function create()
    {
        $this->checkAdmin();
        $restaurants = Restaurant::all();
        return view('admin.categories.create', compact('restaurants'));
    }

    /**
     * Enregistre une nouvelle catégorie
     */
    public function store(Request $request)
    {
        $this->checkAdmin();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'restaurant_id' => 'required|exists:restaurants,id',
        ]);
        
        Category::create([
            'name' => $request->name,
            'restaurant_id' => $request->restaurant_id,
        ]);
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Catégorie créée avec succès');
    }

    /**
     * Affiche les détails d'une catégorie
     */
    public function show(Category $category)
    {
        $this->checkAdmin();
        return view('admin.categories.show', compact('category'));
    }

    /**
     * Affiche le formulaire d'édition d'une catégorie
     */
    public function edit(Category $category)
    {
        $this->checkAdmin();
        $restaurants = Restaurant::all();
        return view('admin.categories.edit', compact('category', 'restaurants'));
    }

    /**
     * Met à jour une catégorie
     */
    public function update(Request $request, Category $category)
    {
        $this->checkAdmin();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'restaurant_id' => 'required|exists:restaurants,id',
        ]);
        
        $category->update([
            'name' => $request->name,
            'restaurant_id' => $request->restaurant_id,
        ]);
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Catégorie mise à jour avec succès');
    }

    /**
     * Supprime une catégorie
     */
    public function destroy(Category $category)
    {
        $this->checkAdmin();
        $category->delete();
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Catégorie supprimée avec succès');
    }
}
