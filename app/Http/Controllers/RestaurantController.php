<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Auth;

class RestaurantController extends Controller
{
    /**
     * Constructeur du contrôleur
     */
    public function __construct()
    {
        // Pas besoin de définir les middlewares ici, ils sont déjà définis dans les routes
    }

    /**
     * Affiche la liste des restaurants
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Requête de base
        $query = Restaurant::with(['user', 'categories']);
        
        // Filtre par nom
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Filtre par catégorie
        if ($request->filled('category')) {
            // À implémenter si besoin
        }
        
        // Filtre pour les avis
        if ($request->filled('filter')) {
            if ($request->filter === 'reviewed' && $user) {
                // Afficher uniquement les restaurants que l'utilisateur a évalués
                $query->whereHas('reviews', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            } elseif ($request->filter === 'all_reviews' && $user && $user->isAdmin()) {
                // Pour les admins : afficher tous les restaurants avec des avis
                $query->whereHas('reviews');
            }
        }
        
        // Tri des résultats
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                default:
                    $query->orderBy('name', 'asc');
            }
        } else {
            // Tri par défaut
            $query->orderBy('name', 'asc');
        }
        
        // Filtre pour les restaurateurs
        if ($user && $user->isRestaurateur()) {
            // Un restaurateur ne voit que ses restaurants
            $query->where('user_id', $user->id);
        }
        
        // Pagination des résultats
        $restaurants = $query->paginate(9);
        
        // Garde les filtres dans les liens de pagination
        $restaurants->appends($request->except('page'));
        
        return view('restaurants.index', compact('restaurants'));
    }

    /**
     * Affiche la liste des restaurants pour les utilisateurs non authentifiés
     */
    public function publicIndex(Request $request)
    {
        // Requête de base
        $query = Restaurant::with(['user', 'categories']);
        
        // Filtre par nom
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Filtre par catégorie
        if ($request->filled('category')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->category . '%');
            });
        }
        
        // Tri des résultats (par défaut: les plus récents)
        $query->orderBy('created_at', 'desc');
        
        $restaurants = $query->paginate(12);
        
        return view('restaurants.public-index', compact('restaurants'));
    }

    /**
     * Affiche le formulaire de création
     */
    public function create() {
        // Vérification des droits
        if (!Auth::user()->isRestaurateur()) {
            abort(403, 'Vous n\'avez pas le droit de créer un restaurant.');
        }
        
        return view('restaurants.create');
    }

    /**
     * Enregistre un nouveau restaurant
     */
    public function store(Request $request) {
        // Vérification des droits
        if (!Auth::user()->isRestaurateur()) {
            abort(403, 'Vous n\'avez pas le droit de créer un restaurant.');
        }
        
        // Validation des données
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'is_open' => 'nullable|boolean',
            'accepts_reservations' => 'nullable|boolean',
        ]);
        
        // Création du restaurant
        $restaurant = new Restaurant();
        $restaurant->name = $request->name;
        $restaurant->address = $request->address;
        $restaurant->phone = $request->phone;
        $restaurant->description = $request->description;
        $restaurant->is_open = $request->has('is_open');
        $restaurant->accepts_reservations = $request->has('accepts_reservations');
        $restaurant->user_id = Auth::id();
        $restaurant->save();
        
        return redirect()->route('restaurants.index')
            ->with('success', 'Restaurant créé avec succès');
    }

    /**
     * Affiche un restaurant spécifique pour les utilisateurs non authentifiés
     */
    public function publicShow(Restaurant $restaurant)
    {
        $restaurant->load(['categories', 'categories.items' => function($query) {
            $query->where('is_active', true);
        }]);
        
        // Récupérer les avis approuvés
        $reviews = $restaurant->reviews()->where('is_approved', true)->latest()->take(5)->get();
        
        return view('restaurants.public-show', compact('restaurant', 'reviews'));
    }

    /**
     * Affiche les détails d'un restaurant
     */
    public function show($id) {
        $restaurant = Restaurant::with(['categories', 'categories.items', 'tables'])->findOrFail($id);
        
        // Vérification des droits pour les restaurateurs
        $user = Auth::user();
        if ($user && $user->isRestaurateur() && $restaurant->user_id !== $user->id) {
            // Si c'est un restaurateur mais pas son restaurant, rediriger vers la vue publique
            return redirect()->route('restaurants.public.show', $restaurant->id);
        }
        
        // Calculer le nombre de tables disponibles
        $totalTables = $restaurant->tables->count();
        $availableTables = $restaurant->tables->where('is_available', true)->count();
        
        return view('restaurants.show', compact('restaurant', 'totalTables', 'availableTables'));
    }

    /**
     * Affiche le formulaire d'édition
     */
    public function edit($id) {
        $restaurant = Restaurant::findOrFail($id);
        
        // Vérification des droits
        if (Auth::user()->isRestaurateur() && $restaurant->user_id !== Auth::id()) {
            abort(403, 'Vous n\'avez pas le droit de modifier ce restaurant.');
        }
        
        return view('restaurants.edit', compact('restaurant'));
    }

    /**
     * Met à jour un restaurant
     */
    public function update(Request $request, $id) {
        $restaurant = Restaurant::findOrFail($id);
        
        // Vérification des droits
        if (Auth::user()->isRestaurateur() && $restaurant->user_id !== Auth::id()) {
            abort(403, 'Vous n\'avez pas le droit de modifier ce restaurant.');
        }
        
        // Validation des données
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'is_open' => 'nullable|boolean',
            'accepts_reservations' => 'nullable|boolean',
        ]);
        
        // Mise à jour du restaurant
        $restaurant->name = $request->name;
        $restaurant->address = $request->address;
        $restaurant->phone = $request->phone;
        $restaurant->description = $request->description;
        $restaurant->is_open = $request->input('is_open', $restaurant->is_open);
        $restaurant->accepts_reservations = $request->input('accepts_reservations', $restaurant->accepts_reservations);
        $restaurant->save();
        
        return redirect()->route('restaurants.index')
            ->with('success', 'Restaurant mis à jour avec succès');
    }

    /**
     * Supprime un restaurant
     */
    public function destroy(Request $request, $id) {
        $restaurant = Restaurant::findOrFail($id);
        
        // Vérification des droits
        if (Auth::user()->isRestaurateur() && $restaurant->user_id !== Auth::id()) {
            abort(403, 'Vous n\'avez pas le droit de supprimer ce restaurant.');
        }
        
        $restaurant->delete();
        
        return redirect()->route('restaurants.index')
            ->with('success', 'Restaurant supprimé avec succès');
    }

    /**
     * Affiche les catégories et plats d'un restaurant
     */
    public function categoriesItems($id) {
        $restaurant = Restaurant::findOrFail($id);
        
        // Vérification des droits
        if (Auth::user()->isRestaurateur() && $restaurant->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Vous n\'avez pas le droit de voir les catégories et plats de ce restaurant.');
        }
        
        // Récupérer les catégories avec leurs plats
        $categories = $restaurant->categories()->with(['items' => function($query) {
            $query->orderBy('name');
        }])->orderBy('name')->get();
        
        return view('restaurants.categories_items', compact('restaurant', 'categories'));
    }
}
