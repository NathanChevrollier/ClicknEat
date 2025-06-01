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
    public function show(Restaurant $restaurant)
    {
        // Chargement des relations nécessaires
        $restaurant->load(['categories', 'categories.items' => function($query) {
            $query->where('is_active', true);
        }]);
        
        // Nombre total de tables et tables disponibles
        $totalTables = $restaurant->total_tables ?: 0;
        $availableTables = $totalTables;
        
        // Si le restaurant accepte les réservations, calculer le nombre de tables disponibles
        if ($restaurant->accepts_reservations && $totalTables > 0) {
            $reservedTables = Reservation::where('restaurant_id', $restaurant->id)
                                    ->whereDate('date', now()->toDateString())
                                    ->whereIn('status', ['pending', 'confirmed'])
                                    ->sum('number_of_people');
            
            $availableTables = max(0, $totalTables - ceil($reservedTables / 4)); // On estime 4 personnes par table
        }
        
        // Récupérer les avis du restaurant
        $reviews = \App\Models\Review::where('restaurant_id', $restaurant->id)
            ->where('is_approved', true) // Seulement les avis approuvés
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Calculer la moyenne des notes
        $averageRating = 0;
        if ($reviews->count() > 0) {
            $averageRating = $reviews->avg('rating');
        }
        
        return view('restaurants.show', compact('restaurant', 'totalTables', 'availableTables', 'reviews', 'averageRating'));
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
    
    /**
     * Vérifie la disponibilité des tables d'un restaurant
     */
    public function checkAvailability(Request $request, Restaurant $restaurant) {
        // Validation des données
        $request->validate([
            'date' => 'required|date|after:now',
            'time' => 'required|string',
            'guests' => 'required|integer|min:1',
            'exclude_reservation_id' => 'nullable|integer|exists:reservations,id',
        ]);
        
        // Combiner la date et l'heure
        $reservationDateTime = new \DateTime($request->date . ' ' . $request->time);
        $guestsNumber = $request->guests;
        $excludeReservationId = $request->exclude_reservation_id;
        
        // Récupérer toutes les tables du restaurant qui peuvent accueillir le nombre de personnes
        $tables = $restaurant->tables()
            ->where('capacity', '>=', $guestsNumber)
            ->where('is_available', true)
            ->get();
        
        // Calculer les créneaux horaires pour la vérification (une réservation dure environ 2 heures)
        $startTime = (clone $reservationDateTime)->modify('-1 hour');
        $endTime = (clone $reservationDateTime)->modify('+3 hours');
        
        // Récupérer les réservations qui se chevauchent
        $overlappingReservations = \App\Models\Reservation::where('restaurant_id', $restaurant->id)
            ->whereBetween('reservation_date', [$startTime->format('Y-m-d H:i:s'), $endTime->format('Y-m-d H:i:s')])
            ->where('status', '!=', 'cancelled');
        
        // Exclure la réservation en cours de modification si spécifié
        if ($excludeReservationId) {
            $overlappingReservations = $overlappingReservations->where('id', '!=', $excludeReservationId);
        }
        
        // Récupérer les IDs des tables déjà réservées
        $reservedTableIds = $overlappingReservations->pluck('table_id')->toArray();
        
        // Filtrer les tables disponibles
        $availableTables = $tables->filter(function($table) use ($reservedTableIds) {
            return !in_array($table->id, $reservedTableIds);
        })->values();
        
        return response()->json([
            'success' => true,
            'available' => $availableTables->count() > 0,
            'tables' => $availableTables,
            'total_tables' => $tables->count(),
            'available_tables' => $availableTables->count(),
            'datetime' => $reservationDateTime->format('Y-m-d H:i:s')
        ]);
    }
}
