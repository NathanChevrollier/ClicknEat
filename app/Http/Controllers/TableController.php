<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        
        // Vérifier que l'utilisateur est le propriétaire du restaurant ou un administrateur
        if ($restaurant->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Vous n\'avez pas le droit d\'ajouter des tables à ce restaurant.');
        }
        
        return view('tables.create', compact('restaurant'));
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
        ]);
        
        $table = new Table([
            'restaurant_id' => $restaurant->id,
            'name' => $request->name,
            'capacity' => $request->capacity,
            'location' => $request->location,
            'description' => $request->description,
            'is_available' => $request->has('is_available'),
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
        
        // Vérifier que l'utilisateur est le propriétaire du restaurant ou un administrateur
        if ($restaurant->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Vous n\'avez pas le droit de voir les détails de cette table.');
        }
        
        return view('tables.show', compact('restaurant', 'table'));
    }

    /**
     * Afficher le formulaire de modification d'une table.
     */
    public function edit($restaurantId, $tableId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        $table = Table::where('restaurant_id', $restaurantId)->findOrFail($tableId);
        
        // Vérifier que l'utilisateur est le propriétaire du restaurant ou un administrateur
        if ($restaurant->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Vous n\'avez pas le droit de modifier cette table.');
        }
        
        return view('tables.edit', compact('restaurant', 'table'));
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
        ]);
        
        $table->name = $request->name;
        $table->capacity = $request->capacity;
        $table->location = $request->location;
        $table->description = $request->description;
        $table->is_available = $request->has('is_available');
        
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
        
        $date = $request->input('date', date('Y-m-d'));
        $time = $request->input('time', '19:00');
        $guests = $request->input('guests', 2);
        
        $dateTime = new \DateTime($date . ' ' . $time);
        
        $availableTables = $restaurant->getAvailableTables($dateTime, $guests);
        
        return view('tables.availability', compact('restaurant', 'availableTables', 'date', 'time', 'guests'));
    }

    /**
     * Obtenir les tables disponibles pour une date et un nombre de personnes donnés.
     */
    public function getAvailableTables(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'reservation_date' => 'required|date|after:now',
            'guests_number' => 'required|integer|min:1|max:20',
            'exclude_reservation_id' => 'nullable|integer|exists:reservations,id',
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
        
        // Récupérer les réservations chevauchantes
        $startTime = (clone $reservationDate)->modify('-2 hours');
        $endTime = (clone $reservationDate)->modify('+2 hours');
        
        $reservationsQuery = \App\Models\Reservation::where('restaurant_id', $restaurant->id)
            ->whereBetween('reservation_date', [$startTime->format('Y-m-d H:i:s'), $endTime->format('Y-m-d H:i:s')])
            ->where('status', '!=', 'cancelled');
            
        // Exclure la réservation en cours de modification si spécifié
        if ($excludeReservationId) {
            $reservationsQuery->where('id', '!=', $excludeReservationId);
        }
        
        $reservedTableIds = $reservationsQuery->pluck('table_id')->toArray();
        \Illuminate\Support\Facades\Log::info('Tables réservées IDs: ' . implode(', ', $reservedTableIds));
        
        // Filtrer les tables disponibles (celles qui ne sont pas déjà réservées)
        $availableTables = $tables->filter(function($table) use ($reservedTableIds) {
            return !in_array($table->id, $reservedTableIds);
        });
        
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
                    'id' => $table->id,
                    'name' => $table->name,
                    'capacity' => $table->capacity,
                    'location' => $table->location ?: '',
                    'description' => $table->description,
                    'is_available' => true
                ];
            }),
            'message' => $availableTables->count() > 0 
                ? 'Tables disponibles trouvées.' 
                : 'Aucune table disponible pour cette date et ce nombre de personnes.'
        ]);
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
