<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Item;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    /**
     * Afficher la liste des réservations.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Filtrer les réservations selon le rôle de l'utilisateur
        if ($user->isRestaurateur()) {
            $restaurantIds = $user->restaurants()->pluck('id');
            if ($request->filled('restaurant') && $request->restaurant !== 'all') {
                $restaurant = $user->restaurants()->where('id', $request->restaurant)->first();
                if (!$restaurant) {
                    abort(403, 'Ce restaurant ne vous appartient pas.');
                }
                $reservations = Reservation::where('restaurant_id', $restaurant->id)
                    ->with(['restaurant', 'table', 'user', 'order'])
                    ->orderBy('reservation_date', 'desc')
                    ->paginate(10);
            } else {
                $reservations = Reservation::whereIn('restaurant_id', $restaurantIds)
                    ->with(['restaurant', 'table', 'user', 'order'])
                    ->orderBy('reservation_date', 'desc')
                    ->paginate(10);
            }
                
            // Gestion du tri
            $sort = request('sort');
            if ($reservations->count() > 0 && $sort) {
                switch ($sort) {
                    case 'date_asc':
                        $reservations = $reservations->sortBy('reservation_date');
                        break;
                    case 'date_desc':
                        $reservations = $reservations->sortByDesc('reservation_date');
                        break;
                    case 'guests_asc':
                        $reservations = $reservations->sortBy('guests_number');
                        break;
                    case 'guests_desc':
                        $reservations = $reservations->sortByDesc('guests_number');
                        break;
                }
                $reservations = $reservations->values();
            }
                
            return view('reservations.index', compact('reservations'));
        } elseif ($user->isAdmin()) {
            // Les administrateurs voient toutes les réservations
            $reservations = Reservation::with(['restaurant', 'table', 'user', 'order'])
                ->orderBy('reservation_date', 'desc')
                ->paginate(10);
                
            // Gestion du tri
            $sort = request('sort');
            if ($reservations->count() > 0 && $sort) {
                switch ($sort) {
                    case 'date_asc':
                        $reservations = $reservations->sortBy('reservation_date');
                        break;
                    case 'date_desc':
                        $reservations = $reservations->sortByDesc('reservation_date');
                        break;
                    case 'guests_asc':
                        $reservations = $reservations->sortBy('guests_number');
                        break;
                    case 'guests_desc':
                        $reservations = $reservations->sortByDesc('guests_number');
                        break;
                }
                $reservations = $reservations->values();
            }
                
            return view('reservations.index', compact('reservations'));
        } else {
            // Les clients voient leurs propres réservations
            $reservations = $user->reservations()
                ->with(['restaurant', 'table', 'order'])
                ->orderBy('reservation_date', 'desc')
                ->paginate(10);
                
            // Gestion du tri
            $sort = request('sort');
            if ($reservations->count() > 0 && $sort) {
                switch ($sort) {
                    case 'date_asc':
                        $reservations = $reservations->sortBy('reservation_date');
                        break;
                    case 'date_desc':
                        $reservations = $reservations->sortByDesc('reservation_date');
                        break;
                    case 'guests_asc':
                        $reservations = $reservations->sortBy('guests_number');
                        break;
                    case 'guests_desc':
                        $reservations = $reservations->sortByDesc('guests_number');
                        break;
                }
                $reservations = $reservations->values();
            }
                
            return view('reservations.index', compact('reservations'));
        }
    }

    /**
     * Afficher les réservations d'un restaurant spécifique (pour restaurateurs).
     */
    public function restaurantReservations(Request $request, $restaurantId)
    {
        $user = Auth::user();
        $restaurant = Restaurant::findOrFail($restaurantId);
        
        // Vérifier que l'utilisateur est bien le propriétaire du restaurant ou admin
        if (!$user->isAdmin() && $restaurant->user_id !== $user->id) {
            return redirect()->route('dashboard')
                ->with('error', 'Vous n\'avez pas accès à cette page.');
        }
        
        // Construire la requête de base
        $query = Reservation::where('restaurant_id', $restaurantId)
            ->with(['table', 'user', 'order']);
            
        // Filtrer par statut si demandé
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtrer par date si demandée
        if ($request->filled('date')) {
            $date = $request->date;
            $query->whereDate('reservation_date', $date);
        }
        
        // Trier par date (plus récentes d'abord)
        $query->orderBy('reservation_date', 'desc');
        
        // Paginer les résultats
        $reservations = $query->paginate(10);
        
        return view('reservations.restaurant', compact('reservations', 'restaurant'));
    }

    /**
     * Afficher le formulaire de création d'une réservation.
     */
    public function create(Request $request, $restaurantId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        
        // Vérifier que le restaurant accepte les réservations
        if (!$restaurant->accepts_reservations) {
            return redirect()->route('restaurants.show', $restaurant->id)
                ->with('error', 'Ce restaurant n\'accepte pas les réservations.');
        }
        
        // Vérifier qu'il y a au moins une table disponible dans le restaurant
        $availableTables = $restaurant->tables()->where('is_available', true)->count();
        if ($availableTables === 0) {
            return redirect()->route('restaurants.show', $restaurant->id)
                ->with('error', 'Aucune table n\'est disponible dans ce restaurant pour le moment.');
        }
        
        return view('reservations.create', compact('restaurant'));
    }

    /**
     * Enregistrer une nouvelle réservation.
     */
    public function store(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'reservation_date' => 'required|date|after:now',
            'guests_number' => 'required|integer|min:1|max:20',
            'table_id' => 'required|exists:tables,id',
            'special_requests' => 'nullable|string|max:500',
            'add_order' => 'nullable|boolean'
        ]);
        
        $restaurant = Restaurant::findOrFail($request->restaurant_id);
        
        // Vérifier que le restaurant accepte les réservations
        if (!$restaurant->accepts_reservations) {
            return redirect()->route('restaurants.show', $restaurant->id)
                ->with('error', 'Ce restaurant n\'accepte pas les réservations.');
        }
        
        // Vérifier que la table appartient au restaurant
        $table = Table::findOrFail($request->table_id);
        if ($table->restaurant_id != $restaurant->id) {
            return redirect()->route('reservations.create', $restaurant->id)
                ->with('error', 'Table invalide.');
        }
        
        // Vérifier que la table est disponible
        $reservationDate = new \DateTime($request->reservation_date);
        if (!$this->isTableAvailable($table->id, $reservationDate)) {
            return redirect()->route('reservations.create', $restaurant->id)
                ->with('error', 'Cette table n\'est plus disponible pour cette date et heure.');
        }
        
        // Vérifier que la capacité de la table est suffisante
        if ($table->capacity < $request->guests_number) {
            return redirect()->route('reservations.create', $restaurant->id)
                ->with('error', 'Cette table ne peut pas accueillir autant de personnes.');
        }
        
        DB::beginTransaction();
        
        try {
            // Créer la réservation
            $reservation = new Reservation([
                'user_id' => Auth::id(),
                'restaurant_id' => $restaurant->id,
                'table_id' => $table->id,
                'reservation_date' => $reservationDate,
                'guests_number' => $request->guests_number,
                'status' => Reservation::STATUS_PENDING,
                'special_requests' => $request->special_requests,
            ]);
            
            $reservation->save();
            
            DB::commit();
            
            // Si l'utilisateur souhaite également passer une commande
            if ($request->has('add_order') && $request->add_order == 1) {
                // Créer une commande vide associée à la réservation
                $order = new Order([
                    'user_id' => Auth::id(),
                    'restaurant_id' => $restaurant->id,
                    'status' => Order::STATUS_PENDING,
                    'total_price' => 0,
                    'notes' => 'Commande associée à la réservation #' . $reservation->id,
                ]);
                
                $order->save();
                
                // Associer la commande à la réservation
                $reservation->order_id = $order->id;
                $reservation->save();
                
                return redirect()->route('orders.edit', ['order' => $order->id])
                    ->with('success', 'Réservation créée avec succès. Vous pouvez maintenant compléter votre commande.');
            }
            
            return redirect()->route('reservations.show', $reservation->id)
                ->with('success', 'Réservation créée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('reservations.create', $restaurant->id)
                ->with('error', 'Une erreur est survenue lors de la création de la réservation: ' . $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'une réservation.
     */
    public function show($id)
    {
        $reservation = Reservation::with(['restaurant', 'table', 'user', 'order.items'])->findOrFail($id);
        
        // Vérifier que l'utilisateur a le droit de voir cette réservation
        $user = Auth::user();
        if ($user->isClient() && $reservation->user_id != $user->id) {
            abort(403, 'Vous n\'avez pas le droit de voir cette réservation.');
        } else if ($user->isRestaurateur()) {
            $restaurantIds = $user->restaurants()->pluck('id')->toArray();
            if (!in_array($reservation->restaurant_id, $restaurantIds)) {
                abort(403, 'Vous n\'avez pas le droit de voir cette réservation.');
            }
        }
        
        return view('reservations.show', compact('reservation'));
    }

    /**
     * Afficher le formulaire de modification d'une réservation.
     */
    public function edit($id)
    {
        $reservation = Reservation::with(['restaurant', 'table'])->findOrFail($id);
        
        // Vérifier que l'utilisateur a le droit de modifier cette réservation
        $user = Auth::user();
        if ($user->isClient() && $reservation->user_id != $user->id) {
            abort(403, 'Vous n\'avez pas le droit de modifier cette réservation.');
        }
        
        // Vérifier que la réservation peut être modifiée
        if (!$this->canBeModified($reservation)) {
            return redirect()->route('reservations.show', $reservation->id)
                ->with('error', 'Cette réservation ne peut plus être modifiée.');
        }
        
        $restaurant = $reservation->restaurant;
        
        return view('reservations.edit', compact('reservation', 'restaurant'));
    }

    /**
     * Mettre à jour une réservation.
     */
    public function update(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);
        
        // Vérifier que l'utilisateur a le droit de modifier cette réservation
        $user = Auth::user();
        if ($user->isClient() && $reservation->user_id != $user->id) {
            abort(403, 'Vous n\'avez pas le droit de modifier cette réservation.');
        }
        
        // Vérifier que la réservation peut être modifiée
        if (!$this->canBeModified($reservation)) {
            return redirect()->route('reservations.show', $reservation->id)
                ->with('error', 'Cette réservation ne peut plus être modifiée.');
        }
        
        $request->validate([
            'reservation_date' => 'required|date|after:now',
            'guests_number' => 'required|integer|min:1|max:20',
            'table_id' => 'required|exists:tables,id',
            'special_requests' => 'nullable|string|max:500',
            'add_order' => 'nullable|boolean'
        ]);
        
        // Vérifier que la table appartient au restaurant
        $table = Table::findOrFail($request->table_id);
        if ($table->restaurant_id != $reservation->restaurant_id) {
            return redirect()->route('reservations.edit', $reservation->id)
                ->with('error', 'Table invalide.');
        }
        
        // Vérifier que la table est disponible (en excluant la réservation actuelle)
        $reservationDate = new \DateTime($request->reservation_date);
        if (!$this->isTableAvailable($table->id, $reservationDate, $reservation->id)) {
            return redirect()->route('reservations.edit', $reservation->id)
                ->with('error', 'Cette table n\'est plus disponible pour cette date et heure.');
        }
        
        // Vérifier que la capacité de la table est suffisante
        if ($table->capacity < $request->guests_number) {
            return redirect()->route('reservations.edit', $reservation->id)
                ->with('error', 'Cette table ne peut pas accueillir autant de personnes.');
        }
        
        // Mettre à jour la réservation
        $reservation->table_id = $table->id;
        $reservation->reservation_date = $reservationDate;
        $reservation->guests_number = $request->guests_number;
        $reservation->special_requests = $request->special_requests;
        
        $reservation->save();
        
        // Si l'utilisateur souhaite ajouter une commande et qu'il n'y en a pas déjà une
        if ($request->has('add_order') && $request->add_order == 1 && !$reservation->order_id) {
            // Créer une commande vide associée à la réservation
            $order = new Order([
                'user_id' => Auth::id(),
                'restaurant_id' => $reservation->restaurant_id,
                'status' => Order::STATUS_PENDING,
                'total_price' => 0,
                'notes' => 'Commande associée à la réservation #' . $reservation->id,
            ]);
            
            $order->save();
            
            // Associer la commande à la réservation
            $reservation->order_id = $order->id;
            $reservation->save();
            
            return redirect()->route('orders.edit', ['order' => $order->id])
                ->with('success', 'Réservation mise à jour avec succès. Vous pouvez maintenant compléter votre commande.');
        }
        
        return redirect()->route('reservations.show', $reservation->id)
            ->with('success', 'Réservation mise à jour avec succès.');
    }

    /**
     * Supprimer une réservation.
     */
    public function destroy($id)
    {
        $reservation = Reservation::findOrFail($id);
        
        // Vérifier que l'utilisateur a le droit de supprimer cette réservation
        $user = Auth::user();
        if ($user->isClient() && $reservation->user_id != $user->id) {
            abort(403, 'Vous n\'avez pas le droit de supprimer cette réservation.');
        }
        
        // Vérifier que la réservation peut être supprimée
        if (!$this->canBeModified($reservation)) {
            return redirect()->route('reservations.show', $reservation->id)
                ->with('error', 'Cette réservation ne peut plus être supprimée.');
        }
        
        $reservation->delete();
        
        return redirect()->route('reservations.index')
            ->with('success', 'Réservation supprimée avec succès.');
    }

    /**
     * Annuler une réservation.
     */
    public function cancel($id)
    {
        $reservation = Reservation::findOrFail($id);
        
        // Vérifier que l'utilisateur a le droit d'annuler cette réservation
        $user = Auth::user();
        if ($user->isClient() && $reservation->user_id != $user->id) {
            abort(403, 'Vous n\'avez pas le droit d\'annuler cette réservation.');
        } else if ($user->isRestaurateur()) {
            $restaurantIds = $user->restaurants()->pluck('id')->toArray();
            if (!in_array($reservation->restaurant_id, $restaurantIds)) {
                abort(403, 'Vous n\'avez pas le droit d\'annuler cette réservation.');
            }
        }
        
        // Vérifier que la réservation peut être annulée
        if ($reservation->status === Reservation::STATUS_CANCELLED || $reservation->status === Reservation::STATUS_COMPLETED) {
            return redirect()->route('reservations.show', $reservation->id)
                ->with('error', 'Cette réservation ne peut pas être annulée.');
        }
        
        $reservation->status = Reservation::STATUS_CANCELLED;
        $reservation->save();
        
        return redirect()->route('reservations.show', $reservation->id)
            ->with('success', 'Réservation annulée avec succès.');
    }

    /**
     * Confirmer une réservation (restaurateur uniquement).
     */
    public function confirm($id)
    {
        $reservation = Reservation::findOrFail($id);
        
        // Vérifier que l'utilisateur est un restaurateur et qu'il a le droit de confirmer cette réservation
        $user = Auth::user();
        if (!$user->isRestaurateur()) {
            abort(403, 'Seuls les restaurateurs peuvent confirmer les réservations.');
        }
        
        $restaurantIds = $user->restaurants()->pluck('id')->toArray();
        if (!in_array($reservation->restaurant_id, $restaurantIds)) {
            abort(403, 'Vous n\'avez pas le droit de confirmer cette réservation.');
        }
        
        // Vérifier que la réservation peut être confirmée
        if ($reservation->status !== Reservation::STATUS_PENDING) {
            return redirect()->route('reservations.show', $reservation->id)
                ->with('error', 'Cette réservation ne peut pas être confirmée.');
        }
        
        $reservation->status = Reservation::STATUS_CONFIRMED;
        $reservation->save();
        
        return redirect()->route('reservations.show', $reservation->id)
            ->with('success', 'Réservation confirmée avec succès.');
    }

    /**
     * Compléter une réservation (restaurateur uniquement).
     */
    public function complete($id)
    {
        $reservation = Reservation::findOrFail($id);
        
        // Vérifier que l'utilisateur est un restaurateur et qu'il a le droit de compléter cette réservation
        $user = Auth::user();
        if (!$user->isRestaurateur()) {
            abort(403, 'Seuls les restaurateurs peuvent marquer les réservations comme terminées.');
        }
        
        $restaurantIds = $user->restaurants()->pluck('id')->toArray();
        if (!in_array($reservation->restaurant_id, $restaurantIds)) {
            abort(403, 'Vous n\'avez pas le droit de modifier cette réservation.');
        }
        
        // Vérifier que la réservation peut être marquée comme terminée
        if ($reservation->status !== Reservation::STATUS_CONFIRMED) {
            return redirect()->route('reservations.show', $reservation->id)
                ->with('error', 'Cette réservation ne peut pas être marquée comme terminée.');
        }
        
        $reservation->status = Reservation::STATUS_COMPLETED;
        $reservation->save();
        
        return redirect()->route('reservations.show', $reservation->id)
            ->with('success', 'Réservation marquée comme terminée avec succès.');
    }

    /**
     * Ajouter une commande à une réservation existante.
     */
    public function addOrder($id)
    {
        $reservation = Reservation::findOrFail($id);
        
        // Vérifier que l'utilisateur a le droit de modifier cette réservation
        $user = Auth::user();
        if ($user->isClient() && $reservation->user_id != $user->id) {
            abort(403, 'Vous n\'avez pas le droit de modifier cette réservation.');
        }
        
        // Vérifier si la réservation a déjà une commande
        if ($reservation->order_id) {
            return redirect()->route('orders.edit', ['order' => $reservation->order_id])
                ->with('info', 'Cette réservation a déjà une commande associée. Vous pouvez la modifier ci-dessous.');
        }
        
        // Créer une commande vide associée à la réservation
        $order = new Order([
            'user_id' => $reservation->user_id,
            'restaurant_id' => $reservation->restaurant_id,
            'status' => Order::STATUS_PENDING,
            'total_price' => 0,
            'notes' => 'Commande associée à la réservation #' . $reservation->id,
        ]);
        
        $order->save();
        
        // Associer la commande à la réservation
        $reservation->order_id = $order->id;
        $reservation->save();
        
        return redirect()->route('orders.edit', ['order' => $order->id])
            ->with('success', 'Vous pouvez maintenant ajouter des plats à votre commande.');
    }

    /**
     * Vérifier si une table est disponible à une date donnée.
     */
    private function isTableAvailable($tableId, $dateTime, $excludeReservationId = null)
    {
        $query = Reservation::where('table_id', $tableId)
            ->where('status', '!=', Reservation::STATUS_CANCELLED)
            ->where(function($q) use ($dateTime) {
                // Vérifier les réservations qui se chevauchent (2 heures avant et après)
                $startTime = (clone $dateTime)->modify('-2 hours');
                $endTime = (clone $dateTime)->modify('+2 hours');
                
                $q->whereBetween('reservation_date', [$startTime, $endTime]);
            });
        
        // Exclure la réservation actuelle si on est en train de la modifier
        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }
        
        return $query->count() === 0;
    }

    /**
     * Vérifier si une réservation peut être modifiée.
     */
    private function canBeModified($reservation)
    {
        // Une réservation ne peut pas être modifiée si elle est annulée ou terminée
        if (in_array($reservation->status, [Reservation::STATUS_CANCELLED, Reservation::STATUS_COMPLETED])) {
            return false;
        }
        
        // Une réservation ne peut pas être modifiée si elle est dans moins de 24 heures
        $now = new \DateTime();
        $reservationDate = new \DateTime($reservation->reservation_date);
        $diff = $now->diff($reservationDate);
        
        return $diff->days > 0 || $reservationDate > $now;
    }
}
