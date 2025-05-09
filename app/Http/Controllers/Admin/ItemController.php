<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
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
     * Affiche la liste des items (plats)
     */
    public function index(Request $request)
    {
        $this->checkAdmin();
        
        // Vérifier si un restaurant ou une catégorie spécifique est demandé
        $restaurant = null;
        $category = null;
        $items = null;
        
        if ($request->has('restaurant_id')) {
            $restaurantId = $request->restaurant_id;
            $restaurant = Restaurant::findOrFail($restaurantId);
            $items = Item::whereHas('category', function($query) use ($restaurantId) {
                $query->where('restaurant_id', $restaurantId);
            })->with('category.restaurant')->get();
        } elseif ($request->has('category_id')) {
            $categoryId = $request->category_id;
            $category = Category::findOrFail($categoryId);
            $restaurant = $category->restaurant;
            $items = Item::where('category_id', $categoryId)->with('category.restaurant')->get();
        } else {
            $items = Item::with('category.restaurant')->get();
        }
        
        return view('admin.items.index', compact('items', 'restaurant', 'category'));
    }

    /**
     * Affiche le formulaire de création d'un item
     */
    public function create(Request $request)
    {
        $this->checkAdmin();
        
        // Récupérer toutes les catégories groupées par restaurant
        $restaurants = Restaurant::with('categories')->get();
        
        // Pré-sélectionner une catégorie si spécifiée dans l'URL
        $selectedCategoryId = $request->has('category_id') ? $request->category_id : null;
        
        return view('admin.items.create', compact('restaurants', 'selectedCategoryId'));
    }

    /**
     * Enregistre un nouvel item
     */
    public function store(Request $request)
    {
        $this->checkAdmin();
        
        // Valider les champs du formulaire
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'is_available' => 'boolean',
            'restaurant_id' => 'required|exists:restaurants,id',
            'menu_id' => 'nullable|exists:menus,id',
        ]);
        
        // Vérifier que la catégorie appartient bien au restaurant sélectionné
        $category = Category::findOrFail($validatedData['category_id']);
        if ($category->restaurant_id != $validatedData['restaurant_id']) {
            return redirect()->back()->withInput()->withErrors([
                'category_id' => 'La catégorie sélectionnée n\'appartient pas au restaurant choisi.'
            ]);
        }
        
        // Préparer les données pour la création de l'item
        $itemData = [
            'name' => $validatedData['name'],
            'description' => $validatedData['description'] ?? null,
            'price' => $validatedData['price'] * 100, // Convertir en centimes
            'category_id' => $validatedData['category_id'],
            'restaurant_id' => $validatedData['restaurant_id'],
            'is_available' => $request->has('is_available'),
        ];
        
        // Ajouter menu_id s'il est présent
        if (isset($validatedData['menu_id'])) {
            $itemData['menu_id'] = $validatedData['menu_id'];
        }
        
        // Créer l'item
        Item::create($itemData);
        
        return redirect()->route('admin.items.index')
            ->with('success', 'Plat créé avec succès');
    }

    /**
     * Affiche les détails d'un item
     */
    public function show(Item $item)
    {
        $this->checkAdmin();
        return view('admin.items.show', compact('item'));
    }

    /**
     * Affiche le formulaire d'édition d'un item
     */
    public function edit(Item $item)
    {
        $this->checkAdmin();
        
        // Récupérer toutes les catégories groupées par restaurant
        $restaurants = Restaurant::with('categories')->get();
        
        return view('admin.items.edit', compact('item', 'restaurants'));
    }

    /**
     * Met à jour un item
     */
    public function update(Request $request, Item $item)
    {
        $this->checkAdmin();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'is_available' => 'boolean',
        ]);
        
        // Convertir le prix en centimes pour le stockage
        $price = $request->price * 100;
        
        $item->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $price,
            'category_id' => $request->category_id,
            'is_available' => $request->has('is_available'),
        ]);
        
        return redirect()->route('admin.items.index')
            ->with('success', 'Plat mis à jour avec succès');
    }

    /**
     * Supprime un item
     */
    public function destroy(Item $item)
    {
        $this->checkAdmin();
        $item->delete();
        
        return redirect()->route('admin.items.index')
            ->with('success', 'Plat supprimé avec succès');
    }
}
