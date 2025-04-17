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
        ]);
        
        $restaurant = Restaurant::findOrFail($request->restaurant_id);
        $reservationDate = new \DateTime($request->reservation_date);
        $guestsNumber = $request->guests_number;
        
        // Récupérer toutes les tables du restaurant avec une capacité suffisante
        $tables = $restaurant->tables()
            ->where('capacity', '>=', $guestsNumber)
            ->where('is_available', true)
            ->get();
        
        // Filtrer les tables qui sont déjà réservées à cette date/heure
        $availableTables = $tables->filter(function($table) use ($reservationDate) {
            // Vérifier si la table est disponible à cette date/heure
            $isAvailable = true;
            
            // Récupérer les réservations pour cette table
            $reservations = $table->reservations()
                ->where('status', '!=', 'cancelled')
                ->get();
            
            foreach ($reservations as $reservation) {
                $reservationDateTime = new \DateTime($reservation->reservation_date);
                
                // Vérifier si la réservation chevauche la date/heure demandée (2h avant/après)
                $startTime = (clone $reservationDate)->modify('-2 hours');
                $endTime = (clone $reservationDate)->modify('+2 hours');
                
                if ($reservationDateTime >= $startTime && $reservationDateTime <= $endTime) {
                    $isAvailable = false;
                    break;
                }
            }
            
            return $isAvailable;
        });
        
        return response()->json([
            'tables' => $availableTables->map(function($table) {
                return [
                    'id' => $table->id,
                    'name' => $table->name,
                    'capacity' => $table->capacity,
                    'location' => $table->location ?: 'Non spécifié',
                    'description' => $table->description,
                ];
            })
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
