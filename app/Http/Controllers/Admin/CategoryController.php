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
        
        // Vérifier si un restaurant spécifique est demandé
        $restaurant = null;
        $categories = null;
        
        if ($request->has('restaurant_id')) {
            $restaurantId = $request->restaurant_id;
            $restaurant = Restaurant::findOrFail($restaurantId);
            $categories = Category::where('restaurant_id', $restaurantId)
                ->with(['restaurant', 'items'])
                ->get();
        } else {
            $categories = Category::with(['restaurant', 'items'])->get();
        }
        
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
