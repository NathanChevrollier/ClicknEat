<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\Table;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    /**
     * Affiche la liste des réservations
     */
    public function index()
    {
        $reservations = Reservation::with(['restaurant', 'user', 'table'])->paginate(10);
        $categories = Category::all(); // Ajout de la variable categories requise par le layout
        return view('admin.reservations.index', compact('reservations', 'categories'));
    }

    /**
     * Affiche le formulaire de création d'une réservation
     */
    public function create()
    {
        $restaurants = Restaurant::all();
        $users = User::where('role', 'client')->get();
        $categories = Category::all(); // Ajout de la variable categories requise par le layout
        return view('admin.reservations.create', compact('restaurants', 'users', 'categories'));
    }

    /**
     * Enregistre une nouvelle réservation
     */
    public function store(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'user_id' => 'required|exists:users,id',
            'table_id' => 'required|exists:tables,id',
            'reservation_date' => 'required|date|after:now',
            'guests_number' => 'required|integer|min:1',
            'status' => 'required|in:pending,confirmed,cancelled,completed',
            'create_order' => 'nullable',
        ]);

        // Créer la réservation
        $reservation = new Reservation();
        $reservation->restaurant_id = $request->restaurant_id;
        $reservation->user_id = $request->user_id;
        $reservation->table_id = $request->table_id;
        $reservation->reservation_date = $request->reservation_date;
        $reservation->guests_number = $request->guests_number;
        $reservation->status = $request->status;
        $reservation->save();

        // Si l'utilisateur a demandé à créer une commande, rediriger vers le formulaire de création de commande
        if ($request->input('create_order') == '1') {
            // Créer une commande vide associée à la réservation
            $order = new \App\Models\Order();
            $order->user_id = $reservation->user_id;
            $order->restaurant_id = $reservation->restaurant_id;
            $order->status = 'pending';
            $order->total_amount = 0; // Utilisation directe du nom de colonne correct
            $order->delivery_address = 'Commande associée à la réservation #' . $reservation->id;
            $order->notes = ''; // Laisser vide pour que l'utilisateur puisse le remplir
            $order->save();
            
            // Associer la commande à la réservation
            $reservation->order_id = $order->id;
            $reservation->save();
            
            // Rediriger vers le formulaire de modification de la commande
            return redirect()->route('admin.orders.edit', ['order' => $order->id])
                ->with('success', 'Réservation créée avec succès. Vous pouvez maintenant compléter la commande.');
        }

        return redirect()->route('admin.reservations.index')
            ->with('success', 'Réservation créée avec succès');
    }

    /**
     * Affiche les détails d'une réservation
     */
    public function show(Reservation $reservation)
    {
        $categories = Category::all(); // Ajout de la variable categories requise par le layout
        return view('admin.reservations.show', compact('reservation', 'categories'));
    }

    /**
     * Affiche le formulaire de modification d'une réservation
     */
    public function edit(Reservation $reservation)
    {
        $restaurants = Restaurant::all();
        $users = User::where('role', 'client')->get();
        $tables = Table::where('restaurant_id', $reservation->restaurant_id)->get();
        $categories = Category::all(); // Ajout de la variable categories requise par le layout
        return view('admin.reservations.edit', compact('reservation', 'restaurants', 'users', 'tables', 'categories'));
    }

    /**
     * Met à jour une réservation
     */
    public function update(Request $request, Reservation $reservation)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'user_id' => 'required|exists:users,id',
            'table_id' => 'required|exists:tables,id',
            'reservation_date' => 'required|date',
            'guests_number' => 'required|integer|min:1',
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ]);

        $reservation->restaurant_id = $request->restaurant_id;
        $reservation->user_id = $request->user_id;
        $reservation->table_id = $request->table_id;
        $reservation->reservation_date = $request->reservation_date;
        $reservation->guests_number = $request->guests_number;
        $reservation->status = $request->status;
        $reservation->save();

        // Redirection explicite vers la page de détails de la réservation dans l'interface d'administration
        return redirect('/admin/reservations/' . $reservation->id)
            ->with('success', 'Réservation mise à jour avec succès');
    }

    /**
     * Supprime une réservation
     */
    public function destroy(Reservation $reservation)
    {
        try {
            // Récupérer la commande associée à la réservation, si elle existe
            $orders = \App\Models\Order::where('reservation_id', $reservation->id)->get();
            
            // Commencer une transaction pour assurer l'atomicité des opérations
            DB::beginTransaction();
            
            // Si des commandes sont associées, les supprimer
            foreach ($orders as $order) {
                // Détacher tous les plats de la commande
                $order->items()->detach();
                // Supprimer la commande
                $order->delete();
            }
            
            // Supprimer la réservation
            $reservation->delete();
            
            // Valider les modifications
            DB::commit();
            
            return redirect()->route('admin.reservations.index')
                ->with('success', 'Réservation et commande associée supprimées avec succès');
        } catch (\Exception $e) {
            // Annuler les modifications en cas d'erreur
            DB::rollBack();
            
            return redirect()->route('admin.reservations.index')
                ->with('error', 'Erreur lors de la suppression de la réservation: ' . $e->getMessage());
        }
    }

    /**
     * Confirme une réservation
     */
    public function confirm(Request $request, Reservation $reservation)
    {
        $reservation->status = 'confirmed';
        $reservation->save();

        return redirect()->back()->with('success', 'Réservation confirmée avec succès');
    }



    /**
     * Marque une réservation comme terminée
     */
    public function complete(Request $request, Reservation $reservation)
    {
        $reservation->status = 'completed';
        $reservation->save();

        return redirect()->back()->with('success', 'Réservation marquée comme terminée');
    }

    /**
     * Annule une réservation
     */
    public function cancel(Reservation $reservation)
    {
        try {
            // Commencer une transaction pour assurer l'atomicité des opérations
            DB::beginTransaction();
            
            $reservation->status = 'cancelled';
            $reservation->save();
            
            // Récupérer les commandes associées à la réservation, si elles existent
            $orders = \App\Models\Order::where('reservation_id', $reservation->id)->get();
            
            // Si des commandes sont associées, les annuler également
            foreach ($orders as $order) {
                $order->status = 'cancelled';
                $order->save();
            }
            
            // Valider les modifications
            DB::commit();
            
            return redirect()->route('admin.reservations.index')
                ->with('success', 'Réservation et commande associée annulées avec succès');
        } catch (\Exception $e) {
            // Annuler les modifications en cas d'erreur
            DB::rollBack();
            
            return redirect()->route('admin.reservations.index')
                ->with('error', 'Erreur lors de l\'annulation de la réservation: ' . $e->getMessage());
        }
    }

    /**
     * Récupère les tables d'un restaurant
     * Mode 1: Toutes les tables si on ne spécifie que restaurant_id
     * Mode 2: Tables disponibles si on spécifie restaurant_id, reservation_date et guests_number
     */
    public function getTables(Request $request)
    {
        // Journaliser toutes les données reçues pour le débogage
        \Illuminate\Support\Facades\Log::info('Données reçues dans getAvailableTables', [
            'all' => $request->all(),
            'content_type' => $request->header('Content-Type'),
            'method' => $request->method()
        ]);
        
        // Validation de base - uniquement restaurant_id est obligatoire
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'reservation_date' => 'nullable|date',
            'guests_number' => 'nullable|integer|min:1',
            'exclude_reservation_id' => 'nullable|integer|exists:reservations,id',
        ]);

        $restaurant_id = $request->restaurant_id;
        $requestDate = $request->has('reservation_date');
        
        // Récupérer les tables de base filtrées par capacité si spécifié
        $tablesQuery = Table::where('restaurant_id', $restaurant_id)
            ->where('is_available', true);
            
        if ($request->has('guests_number')) {
            $tablesQuery->where('capacity', '>=', $request->guests_number);
        }
        
        $tables = $tablesQuery->get();
        \Illuminate\Support\Facades\Log::info('Tables de base trouvées: ' . $tables->count());
        
        // MODE 1: Renvoyer toutes les tables si on ne demande pas de vérifier les réservations
        if (!$requestDate) {
            $mappedTables = $tables->map(function($table) {
                return [
                    'id' => $table->id,
                    'name' => $table->name,
                    'capacity' => $table->capacity,
                    'location' => $table->location ?: '',
                    'description' => $table->description,
                    'is_available' => true
                ];
            });
            
            return response()->json([
                'success' => true,
                'tables' => $mappedTables,
                'message' => $mappedTables->count() > 0 
                    ? 'Tables trouvées.' 
                    : 'Aucune table configurée pour ce restaurant.'
            ]);
        }
        
        // MODE 2: Filtrer les tables disponibles en fonction des réservations existantes
        $reservation_date = new \DateTime($request->reservation_date);
        
        // Récupérer les réservations chevauchantes
        $startTime = (clone $reservation_date)->modify('-1 hour');
        $endTime = (clone $reservation_date)->modify('+1 hour');
        
        \Illuminate\Support\Facades\Log::info('Recherche de réservations entre ' . 
            $startTime->format('Y-m-d H:i:s') . ' et ' . 
            $endTime->format('Y-m-d H:i:s'));
        
        $reservationsQuery = Reservation::where('restaurant_id', $restaurant_id)
            ->whereBetween('reservation_date', [
                $startTime->format('Y-m-d H:i:s'), 
                $endTime->format('Y-m-d H:i:s')
            ])
            ->whereIn('status', ['pending', 'confirmed', 'in_progress']);
            
        // Exclure la réservation en cours de modification si spécifié
        if ($request->has('exclude_reservation_id')) {
            $reservationsQuery->where('id', '!=', $request->exclude_reservation_id);
        }
            
        $reservations = $reservationsQuery->get();
        \Illuminate\Support\Facades\Log::info('Réservations chevauchantes: ' . $reservations->count());
        
        $reservedTableIds = $reservations->pluck('table_id')->toArray();
        
        $availableTables = $tables->filter(function($table) use ($reservedTableIds) {
            return !in_array($table->id, $reservedTableIds);
        })->values();
        
        $mappedTables = $availableTables->map(function($table) {
            return [
                'id' => $table->id,
                'name' => $table->name,
                'capacity' => $table->capacity,
                'location' => $table->location ?: '',
                'description' => $table->description,
                'is_available' => true
            ];
        });

        return response()->json([
            'success' => true,
            'tables' => $mappedTables,
            'message' => $mappedTables->count() > 0 
                ? 'Tables disponibles trouvées.' 
                : 'Aucune table disponible pour cette date et ce nombre de personnes.'
        ]);
    }

    /**
     * Méthode de test pour récupérer des tables (version simplifiée pour déboguer)
     */
    public function getTablesTest()
    {
        // Renvoie simplement des tables factices pour tester
        return response()->json([
            'tables' => [
                ['id' => 1, 'name' => 'Table 1', 'capacity' => 2],
                ['id' => 2, 'name' => 'Table 2', 'capacity' => 4],
                ['id' => 3, 'name' => 'Table 3', 'capacity' => 6]
            ]
        ]);
    }
    
    /**
     * Récupère toutes les tables d'un restaurant sans filtrage
     * Utilisé comme plan B quand la recherche normale échoue
     */
    public function getAllRestaurantTables(Request $request)
    {
        // Validation de base
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id'
        ]);
        
        // Journaliser la requête
        \Illuminate\Support\Facades\Log::info('Récupération de toutes les tables pour restaurant_id: ' . $request->restaurant_id);
        
        // Récupérer toutes les tables du restaurant
        $tables = \App\Models\Table::where('restaurant_id', $request->restaurant_id)
            ->where('is_available', true)
            ->get()
            ->map(function($table) {
                return [
                    'id' => $table->id,
                    'name' => $table->name,
                    'capacity' => $table->capacity,
                    'location' => $table->location ?: '',
                    'description' => $table->description,
                    'is_available' => true
                ];
            });
            
        // Journaliser le résultat
        \Illuminate\Support\Facades\Log::info('Tables trouvées: ' . $tables->count());
        
        return response()->json([
            'success' => true,
            'tables' => $tables,
            'message' => $tables->count() > 0 
                ? 'Tables trouvées.' 
                : 'Aucune table configurée pour ce restaurant.'
        ]);
    }
}
