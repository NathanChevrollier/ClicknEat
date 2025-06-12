<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CategorieTable;

class TableController extends Controller
{
    /**
     * Afficher la liste des tables de tous les restaurants du restaurateur
     */
    public function index(Request $request, $restaurantId = null)
    {
        $user = Auth::user();
        $tables = collect();
        $restaurants = collect();
        $restaurant = null;

        if ($user->isRestaurateur()) {
            // Récupérer tous les restaurants du restaurateur
            $restaurants = $user->restaurants()->orderBy('name')->get();
            if ($request->filled('restaurant') && $request->restaurant !== 'all') {
                $restaurant = $restaurants->where('id', $request->restaurant)->first();
                if (!$restaurant) {
                    abort(403, 'Ce restaurant ne vous appartient pas.');
                }
                $tables = $restaurant->tables;
            } else {
                // Toutes les tables de tous ses restaurants
                $tables = \App\Models\Table::whereIn('restaurant_id', $restaurants->pluck('id'))->with('restaurant')->get();
            }
        } else {
            // Admin ou autre rôle
            if ($restaurantId) {
                $restaurant = Restaurant::findOrFail($restaurantId);
                $tables = $restaurant->tables;
            } else {
                $tables = \App\Models\Table::with('restaurant')->get();
            }
        }

        // Gestion du tri
        $sort = $request->input('sort');
        if ($tables->count() > 0 && $sort) {
            switch ($sort) {
                case 'name_asc':
                    $tables = $tables->sortBy('name');
                    break;
                case 'name_desc':
                    $tables = $tables->sortByDesc('name');
                    break;
                case 'capacity_asc':
                    $tables = $tables->sortBy('capacity');
                    break;
                case 'capacity_desc':
                    $tables = $tables->sortByDesc('capacity');
                    break;
            }
            $tables = $tables->values();
        }

        return view('tables.index', compact('tables', 'restaurant', 'restaurants'));
    }

    /**
     * Afficher le formulaire de création d'une table.
     */
    public function create(Request $request, $restaurantId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        $categories = CategorieTable::all();
        
        // Vérifier que l'utilisateur est le propriétaire du restaurant ou un administrateur
        if ($restaurant->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Vous n\'avez pas le droit d\'ajouter des tables à ce restaurant.');
        }
        
        
        
        return view('tables.create', compact('restaurant', 'categories'));
    }

    /**
     * Enregistrer une nouvelle table.
     */
    public function store(Request $request, $restaurantId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        
        // Vérifier que l'utilisateur est le propriétaire du restaurant ou un administrateur
        if ($restaurant->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Vous n\'avez pas le droit d\'ajouter des tables à ce restaurant.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_available' => 'boolean',
            'pmr' => 'boolean',
            'categorie_id' => 'required|exists:categorie_table,id',
        ]);
        
        $table = new Table([
            'restaurant_id' => $restaurant->id,
            'name' => $request->name,
            'capacity' => $request->capacity,
            'location' => $request->location,
            'description' => $request->description,
            'is_available' => $request->has('is_available'),
            'pmr' => $request->pmr == '1',
            'categorie_id' => $request->categorie_id,
        ]);
        
        $table->save();
        
        return redirect()->route('restaurants.tables.index', $restaurant->id)
                         ->with('success', 'Table ajoutée avec succès');
    }

    /**
     * Afficher les détails d'une table.
     */
    public function show($restaurantId, $tableId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        $table = Table::where('restaurant_id', $restaurantId)->findOrFail($tableId);
        $categories = CategorieTable::all();
        
        // Vérifier que l'utilisateur est le propriétaire du restaurant ou un administrateur
        if ($restaurant->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Vous n\'avez pas le droit de voir les détails de cette table.');
        }
        
        return view('tables.show', compact('restaurant', 'table', 'categories'));
    }

    /**
     * Afficher le formulaire de modification d'une table.
     */
    public function edit($restaurantId, $tableId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        $table = Table::where('restaurant_id', $restaurantId)->findOrFail($tableId);
        $categories = CategorieTable::all();
        
        // Vérifier que l'utilisateur est le propriétaire du restaurant ou un administrateur
        if ($restaurant->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Vous n\'avez pas le droit de modifier cette table.');
        }

        return view('tables.edit', compact('restaurant', 'table', 'categories'));
    }

    /**
     * Mettre à jour une table.
     */
    public function update(Request $request, $restaurantId, $tableId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        $table = Table::where('restaurant_id', $restaurantId)->findOrFail($tableId);
        
        // Vérifier que l'utilisateur est le propriétaire du restaurant ou un administrateur
        if ($restaurant->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Vous n\'avez pas le droit de modifier cette table.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_available' => 'boolean',
            'pmr' => 'boolean',
            'categorie_id' => 'required|exists:categorie_table,id',
        ]);
        
        $table->name = $request->name;
        $table->capacity = $request->capacity;
        $table->location = $request->location;
        $table->description = $request->description;
        $table->is_available = $request->has('is_available');
        $table->pmr = $request->pmr == '1';
        $table->categorie_id = $request->categorie_id;
        
        $table->save();
        
        return redirect()->route('restaurants.tables.index', $restaurant->id)
                         ->with('success', 'Table mise à jour avec succès');
    }

    /**
     * Supprimer une table.
     */
    public function destroy($restaurantId, $tableId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        $table = Table::where('restaurant_id', $restaurantId)->findOrFail($tableId);
        
        // Vérifier que l'utilisateur est le propriétaire du restaurant ou un administrateur
        if ($restaurant->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Vous n\'avez pas le droit de supprimer cette table.');
        }
        
        // Vérifier qu'il n'y a pas de réservations futures pour cette table
        $futureReservations = $table->reservations()
            ->where('reservation_date', '>', now())
            ->where('status', '!=', 'cancelled')
            ->count();
            
        if ($futureReservations > 0) {
            return redirect()->route('restaurants.tables.index', $restaurant->id)
                             ->with('error', 'Impossible de supprimer cette table car elle a des réservations futures.');
        }
        
        $table->delete();
        
        return redirect()->route('restaurants.tables.index', $restaurant->id)
                         ->with('success', 'Table supprimée avec succès');
    }
    
    /**
     * Afficher la disponibilité des tables pour une date donnée.
     */
    public function availability(Request $request, $restaurantId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        $categories = CategorieTable::all();
        
        $date = $request->input('date', date('Y-m-d'));
        $time = $request->input('time', '19:00');
        $guests = $request->input('guests', 2);
        
        $dateTime = new \DateTime($date . ' ' . $time);
        
        $availableTables = $restaurant->getAvailableTables($dateTime, $guests);
        
        return view('tables.availability', compact('restaurant', 'availableTables', 'date', 'time', 'guests', 'categories'));
    }

    /**
     * Obtenir les tables disponibles pour une date et un nombre de personnes donnés.
     */
    public function getAvailableTables(Request $request)
    {
        try {
            // Journaliser toutes les données reçues pour le débogage
            \Illuminate\Support\Facades\Log::info('Données reçues dans getAvailableTables', [
                'all' => $request->all(),
                'content_type' => $request->header('Content-Type'),
                'method' => $request->method()
            ]);
            
            // Valider les données reçues
            $validated = $request->validate([
                'restaurant_id' => 'required|exists:restaurants,id',
                'reservation_date' => 'required',  // Simplifier la validation pour tester
                'guests_number' => 'required|integer|min:1',
                'exclude_reservation_id' => 'nullable|integer|exists:reservations,id',
            ]);
            
            // Fix pour le format de date au besoin
            if (!strtotime($request->reservation_date)) {
                \Illuminate\Support\Facades\Log::error('Format de date invalide: ' . $request->reservation_date);
                return response()->json([
                    'success' => false,
                    'message' => 'Format de date invalide',
                    'tables' => []
                ], 400);
            }
            
            \Illuminate\Support\Facades\Log::info('Début de la recherche de tables disponibles', [
                'restaurant_id' => $request->restaurant_id,
                'date' => $request->reservation_date,
                'guests' => $request->guests_number,
                'exclude_id' => $request->exclude_reservation_id
            ]);
        
        $restaurant = Restaurant::findOrFail($request->restaurant_id);
        $reservationDate = new \DateTime($request->reservation_date);
        $guestsNumber = $request->guests_number;
        $excludeReservationId = $request->exclude_reservation_id;
        
        // Journaliser la requête pour débogage
        \Illuminate\Support\Facades\Log::info('Recherche de tables pour restaurant_id: ' . $request->restaurant_id . ', guests: ' . $guestsNumber);
        
        // Requête SQL directe pour récupérer toutes les tables physiques du restaurant
        $tablesQuery = \App\Models\Table::where('restaurant_id', $restaurant->id)
            ->where('capacity', '>=', $guestsNumber)
            ->where('is_available', true);
            
        \Illuminate\Support\Facades\Log::info('Requête SQL tables: ' . $tablesQuery->toSql());
        $tables = $tablesQuery->get();
        \Illuminate\Support\Facades\Log::info('Nombre de tables trouvées: ' . $tables->count());
        
        // Récupérer les réservations chevauchantes - utiliser une plage plus courte (1h avant/après)
        $startTime = (clone $reservationDate)->modify('-1 hour');
        $endTime = (clone $reservationDate)->modify('+1 hour');
        
        // Formater les dates pour le log et la requête
        $startFormatted = $startTime->format('Y-m-d H:i:s');
        $endFormatted = $endTime->format('Y-m-d H:i:s');
        $dateFormatted = $reservationDate->format('Y-m-d H:i:s');
        
        \Illuminate\Support\Facades\Log::info('Recherche de réservations entre ' . $startFormatted . ' et ' . $endFormatted);
        
        $reservationsQuery = \App\Models\Reservation::where('restaurant_id', $restaurant->id)
            ->whereBetween('reservation_date', [$startFormatted, $endFormatted])
            ->whereIn('status', ['pending', 'confirmed', 'in_progress']);
        
        // Journaliser la requête SQL pour les réservations
        \Illuminate\Support\Facades\Log::info('Requête SQL réservations: ' . $reservationsQuery->toSql());
        
        // Exclure la réservation en cours de modification si spécifié
        if ($excludeReservationId) {
            $reservationsQuery->where('id', '!=', $excludeReservationId);
        }
        
        // Récupérer toutes les réservations pour les analyser
        $reservations = $reservationsQuery->get();
        \Illuminate\Support\Facades\Log::info('Réservations trouvées: ' . $reservations->count(), [
            'réservations' => $reservations->toArray()
        ]);
        
        // Récupérer les IDs des tables réservées
        $reservedTableIds = $reservations->pluck('table_id')->toArray();
        \Illuminate\Support\Facades\Log::info('Tables réservées IDs: ' . implode(', ', $reservedTableIds));
        
        // Journaliser toutes les tables du restaurant
        \Illuminate\Support\Facades\Log::info('Toutes les tables du restaurant:', [
            'tables' => $tables->map(function($table) {
                return [
                    'id' => $table->id,
                    'name' => $table->name,
                    'capacity' => $table->capacity,
                    'is_available' => $table->is_available
                ];
            })->toArray()
        ]);
        
        // Filtrer les tables disponibles (celles qui ne sont pas déjà réservées)
        $availableTables = $tables->filter(function($table) use ($reservedTableIds) {
            return !in_array($table->id, $reservedTableIds);
        });
        
        \Illuminate\Support\Facades\Log::info('Nombre de tables disponibles après filtrage: ' . $availableTables->count());
        
        // Ajouter la table actuelle si elle existe et qu'elle a été exclue
        if ($excludeReservationId) {
            $currentReservation = \App\Models\Reservation::find($excludeReservationId);
            if ($currentReservation) {
                $currentTableId = $currentReservation->table_id;
                $currentTableIncluded = $availableTables->contains('id', $currentTableId);
                
                if (!$currentTableIncluded) {
                    // Récupérer la table actuelle si elle n'est pas déjà incluse
                    $currentTable = \App\Models\Table::find($currentTableId);
                    if ($currentTable && $currentTable->capacity >= $guestsNumber) {
                        $availableTables->push($currentTable);
                    }
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'tables' => $availableTables->map(function($table) {
                return [
                    'table_id' => $table->id,
                    'name' => $table->name,
                    'capacity' => $table->capacity,
                    'pmr' => $table->pmr,
                    'categorie_id' => $table->categorie_id,
                    'categorie_nom' => $table->categorie ? $table->categorie->nom : null,
                    'location' => $table->location ?: '',
                    'description' => $table->description,
                    'is_available' => true,
                ];
            }),
            'message' => $availableTables->count() > 0 
                ? 'Tables disponibles trouvées.' 
                : 'Aucune table disponible pour cette date et ce nombre de personnes.'
        ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de la recherche de tables disponibles', [
                'message' => $e->getMessage(),
                'restaurant_id' => $request->restaurant_id ?? 'non défini',
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'tables' => [],
                'message' => 'Une erreur est survenue lors de la recherche de tables disponibles.'
            ], 500);
        }
    }

    /**
     * Basculer rapidement la disponibilité d'une table.
     */
    public function toggleAvailability($restaurantId, $tableId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        $table = Table::where('restaurant_id', $restaurantId)->findOrFail($tableId);
        
        // Vérifier que l'utilisateur est le propriétaire du restaurant ou un administrateur
        if ($restaurant->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Vous n\'avez pas le droit de modifier cette table.');
        }
        
        // Basculer la disponibilité
        $table->is_available = !$table->is_available;
        $table->save();
        
        $status = $table->is_available ? 'disponible' : 'indisponible';
        
        return redirect()->route('restaurants.tables.index', $restaurant->id)
                         ->with('success', "La table {$table->name} est maintenant {$status}.");
    }


}
