<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $restaurant = null;
        $restaurants = collect();
        
        // Définir le tri par défaut
        $sort = $request->input('sort', 'name_asc');
        
        // Créer la requête de base
        $query = Item::with(['category.restaurant']);
        
        if ($user->isRestaurateur()) {
            // Les restaurateurs ne voient que les items de leurs restaurants
            $restaurants = $user->restaurants()->orderBy('name')->get();
            $restaurantIds = $restaurants->pluck('id')->toArray();
            $categoryIds = Category::whereIn('restaurant_id', $restaurantIds)->pluck('id');
            $query->whereIn('category_id', $categoryIds);
            
            // Filtrage par restaurant
            if ($request->filled('restaurant')) {
                if ($request->restaurant === 'all') {
                    // Explicitement "Tous les restaurants"
                    $restaurant = null;
                } else {
                    $selectedRestaurant = $restaurants->where('id', $request->restaurant)->first();
                    if ($selectedRestaurant) {
                        $restaurant = $selectedRestaurant;
                        $restaurantCategoryIds = Category::where('restaurant_id', $selectedRestaurant->id)->pluck('id');
                        $query->whereIn('category_id', $restaurantCategoryIds);
                    }
                }
            }
        }
        
        // Application du tri directement au niveau SQL avec préfixe de table
        switch ($sort) {
            case 'name_desc':
                $query->orderBy('items.name', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('items.price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('items.price', 'desc');
                break;
            case 'name_asc':
            default:
                $query->orderBy('items.name', 'asc');
                break;
        }
        
        // Paginer les résultats
        $items = $query->paginate(15)->withQueryString();
        
        return view('items.index', compact('items', 'restaurant', 'restaurants', 'sort'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Vérifier que l'utilisateur est un restaurateur
        if (!Auth::user()->isRestaurateur()) {
            abort(403, 'Vous n\'avez pas le droit de créer un item.');
        }
        
        // Récupérer uniquement les catégories des restaurants de l'utilisateur connecté
        $restaurantIds = Auth::user()->restaurants()->pluck('id');
        $categories = Category::whereIn('restaurant_id', $restaurantIds)->get();
        
        return view('items.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Vérifier que l'utilisateur est un restaurateur
        if (!Auth::user()->isRestaurateur()) {
            abort(403, 'Vous n\'avez pas le droit de créer un item.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'nullable|integer',
            'price' => 'required|integer',
            'category_id' => 'required|exists:categories,id',
        ]);
        
        // Vérifier que la catégorie appartient à un restaurant de l'utilisateur connecté
        $category = Category::findOrFail($request->category_id);
        $restaurantIds = Auth::user()->restaurants()->pluck('id')->toArray();
        
        if (!in_array($category->restaurant_id, $restaurantIds)) {
            return redirect()->back()->with('error', 'Vous n\'avez pas le droit de créer un item pour cette catégorie.');
        }

        try {
            // Créer l'item avec les données du formulaire
            $item = new Item();
            $item->name = $request->name;
            $item->description = $request->description;
            $item->cost = $request->cost;
            $item->price = $request->price;
            $item->is_active = $request->has('is_active') ? 1 : 0;
            $item->category_id = $request->category_id;
            $item->save();

            return redirect()->route('items.index')->with('success', 'Item ajouté avec succès');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de l\'enregistrement de l\'item: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        return view('items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        // Vérifier que l'utilisateur est un restaurateur et que l'item appartient à un de ses restaurants
        $user = Auth::user();
        if (!$user->isRestaurateur()) {
            abort(403, 'Vous n\'avez pas le droit de modifier cet item.');
        }
        
        $restaurantIds = $user->restaurants()->pluck('id')->toArray();
        $category = Category::findOrFail($item->category_id);
        
        if (!in_array($category->restaurant_id, $restaurantIds)) {
            abort(403, 'Vous n\'avez pas le droit de modifier cet item.');
        }
        
        // Récupérer uniquement les catégories des restaurants de l'utilisateur connecté
        $categories = Category::whereIn('restaurant_id', $restaurantIds)->get();
        
        return view('items.edit', compact('item', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        // Vérifier que l'utilisateur est un restaurateur et que l'item appartient à un de ses restaurants
        $user = Auth::user();
        if (!$user->isRestaurateur()) {
            abort(403, 'Vous n\'avez pas le droit de modifier cet item.');
        }
        
        $restaurantIds = $user->restaurants()->pluck('id')->toArray();
        $category = Category::findOrFail($item->category_id);
        
        if (!in_array($category->restaurant_id, $restaurantIds)) {
            abort(403, 'Vous n\'avez pas le droit de modifier cet item.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'nullable|integer',
            'price' => 'required|integer',
            'is_active' => 'boolean',
            'category_id' => 'required|exists:categories,id',
        ]);
        
        // Vérifier que la nouvelle catégorie appartient à un restaurant de l'utilisateur connecté
        $newCategory = Category::findOrFail($request->category_id);
        
        if (!in_array($newCategory->restaurant_id, $restaurantIds)) {
            abort(403, 'Vous n\'avez pas le droit d\'assigner cet item à cette catégorie.');
        }

        $item->update($request->all());

        return redirect()->route('items.index')->with('success', 'Item mis à jour');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        // Vérifier que l'utilisateur est un restaurateur et que l'item appartient à un de ses restaurants
        $user = Auth::user();
        if (!$user->isRestaurateur()) {
            abort(403, 'Vous n\'avez pas le droit de supprimer cet item.');
        }
        
        $restaurantIds = $user->restaurants()->pluck('id')->toArray();
        $category = Category::findOrFail($item->category_id);
        
        if (!in_array($category->restaurant_id, $restaurantIds)) {
            abort(403, 'Vous n\'avez pas le droit de supprimer cet item.');
        }
        
        $item->delete();
        return redirect()->route('items.index')->with('success', 'Item supprimé');
    }

    /**
     * Affiche le formulaire d'ajout direct d'un item
     */
    public function add()
    {
        // Vérifier que l'utilisateur est un restaurateur
        if (!Auth::user()->isRestaurateur()) {
            abort(403, 'Vous n\'avez pas le droit de créer un item.');
        }
        
        // Récupérer uniquement les catégories des restaurants de l'utilisateur connecté
        $restaurantIds = Auth::user()->restaurants()->pluck('id');
        $categories = Category::whereIn('restaurant_id', $restaurantIds)->get();
        
        return view('items.add', compact('categories'));
    }

    /**
     * Enregistre un nouvel item directement
     */
    public function storeDirect(Request $request)
    {
        // Vérifier que l'utilisateur est un restaurateur
        if (!Auth::user()->isRestaurateur()) {
            abort(403, 'Vous n\'avez pas le droit de créer un item.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'nullable|integer',
            'price' => 'required|integer',
            'category_id' => 'required|exists:categories,id',
        ]);
        
        // Vérifier que la catégorie appartient à un restaurant de l'utilisateur connecté
        $category = Category::findOrFail($request->category_id);
        $restaurantIds = Auth::user()->restaurants()->pluck('id')->toArray();
        
        if (!in_array($category->restaurant_id, $restaurantIds)) {
            return redirect()->back()->with('error', 'Vous n\'avez pas le droit de créer un item pour cette catégorie.');
        }

        try {
            // Créer l'item avec les données du formulaire
            $item = new Item();
            $item->name = $request->name;
            $item->description = $request->description;
            $item->cost = $request->cost;
            $item->price = $request->price;
            $item->is_active = $request->has('is_active') ? 1 : 0;
            $item->category_id = $request->category_id;
            $item->save();

            return redirect()->route('items.index')->with('success', 'Item ajouté avec succès');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de l\'enregistrement de l\'item: ' . $e->getMessage());
        }
    }
}
