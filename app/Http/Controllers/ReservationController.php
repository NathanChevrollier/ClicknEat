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
use Illuminate\Support\Facades\Log;

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
        
        // Gestion du tri
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'date_asc':
                    $query->orderBy('reservation_date', 'asc');
                    break;
                case 'date_desc':
                    $query->orderBy('reservation_date', 'desc');
                    break;
                case 'guests_asc':
                    $query->orderBy('guests_number', 'asc');
                    break;
                case 'guests_desc':
                    $query->orderBy('guests_number', 'desc');
                    break;
                default:
                    $query->orderBy('reservation_date', 'desc');
            }
        } else {
            // Par défaut, trier par date (plus récentes d'abord)
            $query->orderBy('reservation_date', 'desc');
        }
        
        // Paginer les résultats
        $reservations = $query->paginate(10)->withQueryString();
        
        // Utiliser la nouvelle vue pour le restaurateur
        return view('restaurateur.reservations.index', compact('reservations', 'restaurant'));
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
        
        // Récupérer directement les vraies tables de la BDD
        $tables = DB::table('tables')
            ->where('restaurant_id', $restaurantId)
            ->where('is_available', true)
            ->get();
        
        if ($tables->count() === 0) {
            return redirect()->route('restaurants.show', $restaurant->id)
                ->with('error', 'Aucune table n\'est disponible dans ce restaurant pour le moment.');
        }
        
        // Afficher les vraies tables dans le formulaire
        return view('reservations.create', compact('restaurant', 'tables'));
    }

    /**
     * Enregistrer une nouvelle réservation.
     */
    public function store(Request $request)
    {
        // Ajout de journalisation pour débogage
        \Illuminate\Support\Facades\Log::info('Données de réservation reçues', [
            'request_data' => $request->all(),
            'user_id' => auth()->id()
        ]);
        
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'reservation_date' => 'required|date|after:now',
            'guests_number' => 'required|integer|min:1|max:20',
            'table_id' => 'required|exists:tables,id',
            'special_requests' => 'nullable|string|max:500',
            'create_order' => 'nullable|boolean'
        ]);
        
        $restaurant = Restaurant::findOrFail($request->restaurant_id);
        
        // Vérifier que le restaurant est ouvert et accepte les réservations
        if (!$restaurant->is_open) {
            \Illuminate\Support\Facades\Log::warning('Tentative de réservation dans un restaurant fermé', [
                'restaurant_id' => $restaurant->id,
                'user_id' => auth()->id()
            ]);
            return redirect()->route('restaurants.show', $restaurant->id)
                ->with('error', 'Ce restaurant est actuellement fermé et n\'accepte pas de réservations.');
        }
        
        if (!$restaurant->accepts_reservations) {
            \Illuminate\Support\Facades\Log::warning('Tentative de réservation dans un restaurant qui n\'accepte pas les réservations', [
                'restaurant_id' => $restaurant->id,
                'user_id' => auth()->id()
            ]);
            return redirect()->route('restaurants.show', $restaurant->id)
                ->with('error', 'Ce restaurant n\'accepte pas les réservations.');
        }
        
        // Vérifier que la table appartient au restaurant et est disponible
        $table = Table::where('id', $request->table_id)
                      ->where('restaurant_id', $restaurant->id)
                      ->where('is_available', true)
                      ->first();
        
        if (!$table) {
            \Illuminate\Support\Facades\Log::warning('Tentative de réservation avec une table invalide ou inactive', [
                'table_id' => $request->table_id,
                'restaurant_id' => $restaurant->id,
                'user_id' => auth()->id()
            ]);
            return redirect()->route('reservations.create', $restaurant->id)
                ->with('error', 'La table sélectionnée n\'est pas disponible.');
        }
        
        // Vérifier que la table est disponible
        $reservationDate = new \DateTime($request->reservation_date);
        
        // Vérifier que la date de réservation est dans les heures d'ouverture du restaurant
        // Si les heures d'ouverture sont nulles, toutes les heures sont permises
        $dayOfWeek = strtolower($reservationDate->format('l'));
        $hourOfDay = (int)$reservationDate->format('G');
        
        $openingHoursColumn = 'opening_hours_' . $dayOfWeek;
        $closingHoursColumn = 'closing_hours_' . $dayOfWeek;
        
        // Journaliser l'information pour débogage
        Log::info('Vérification des horaires de réservation', [
            'restaurant_id' => $restaurant->id,
            'day' => $dayOfWeek,
            'hour' => $hourOfDay,
            'opening_hour' => $restaurant->$openingHoursColumn,
            'closing_hour' => $restaurant->$closingHoursColumn
        ]);
        
        // Vérifier les horaires seulement si les valeurs ne sont pas nulles
        // Si opening_hours ou closing_hours est null, on permet toutes les heures
        if ($restaurant->$openingHoursColumn !== null && $restaurant->$closingHoursColumn !== null) {
            if ($hourOfDay < (int)$restaurant->$openingHoursColumn || 
                $hourOfDay >= (int)$restaurant->$closingHoursColumn) {
                
                \Illuminate\Support\Facades\Log::warning('Tentative de réservation en dehors des heures d\'ouverture', [
                    'restaurant_id' => $restaurant->id,
                    'day' => $dayOfWeek,
                    'hour' => $hourOfDay,
                    'opening_hour' => $restaurant->$openingHoursColumn,
                    'closing_hour' => $restaurant->$closingHoursColumn
                ]);
                
                return redirect()->route('reservations.create', $restaurant->id)
                    ->with('error', 'L\'horaire de réservation est en dehors des heures d\'ouverture du restaurant.');
            }
        } else {
            // Les horaires ne sont pas définis, toutes les heures sont autorisées
            \Illuminate\Support\Facades\Log::info('Horaires d\'ouverture non définis, réservation autorisée pour toute heure');
        }
        
        if (!$this->isTableAvailable($table->id, $reservationDate)) {
            \Illuminate\Support\Facades\Log::info('Table non disponible pour la réservation', [
                'table_id' => $table->id,
                'reservation_date' => $reservationDate->format('Y-m-d H:i:s')
            ]);
            
            return redirect()->route('reservations.create', $restaurant->id)
                ->with('error', 'Cette table n\'est plus disponible pour cette date et heure.');
        }
        
        // Vérifier que la capacité de la table est suffisante
        if ($table->capacity < $request->guests_number) {
            \Illuminate\Support\Facades\Log::info('Capacité de table insuffisante', [
                'table_id' => $table->id,
                'table_capacity' => $table->capacity,
                'guests_number' => $request->guests_number
            ]);
            
            return redirect()->route('reservations.create', $restaurant->id)
                ->with('error', 'Cette table ne peut pas accueillir autant de personnes.');
        }
        
        // Vérifier si l'utilisateur a déjà une réservation qui chevauche ce créneau
        $userId = Auth::id();
        $startTime = (clone $reservationDate)->modify('-2 hours');
        $endTime = (clone $reservationDate)->modify('+2 hours');
        
        $existingReservation = Reservation::where('user_id', $userId)
            ->where('status', '!=', Reservation::STATUS_CANCELLED)
            ->whereBetween('reservation_date', [$startTime, $endTime])
            ->first();
            
        if ($existingReservation) {
            \Illuminate\Support\Facades\Log::warning('Tentative de réservation multiple pour un même créneau', [
                'user_id' => $userId,
                'existing_reservation_id' => $existingReservation->id,
                'existing_reservation_date' => $existingReservation->reservation_date,
                'new_reservation_date' => $reservationDate->format('Y-m-d H:i:s')
            ]);
            
            return redirect()->route('reservations.create', $restaurant->id)
                ->with('error', 'Vous avez déjà une réservation prévue à cette date et heure.');
        }
        
        try {
            DB::beginTransaction();
            
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
            if ($request->has('create_order') && $request->create_order == 1) {
                // Créer une commande vide associée à la réservation
                $order = new Order([
                    'user_id' => Auth::id(),
                    'restaurant_id' => $restaurant->id,
                    'reservation_id' => $reservation->id, // Lien direct vers la réservation
                    'status' => Order::STATUS_PENDING,
                    'total_amount' => 0,
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
        // Charger la réservation avec toutes les relations nécessaires
        $reservation = Reservation::with([
            'restaurant', 
            'table', 
            'order', 
            'order.items'
        ])->findOrFail($id);
        
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
        
        // Récupérer directement les vraies tables de la BDD
        $tables = DB::table('tables')
            ->where('restaurant_id', $restaurant->id)
            ->where('is_available', true)
            ->orWhere('id', $reservation->table_id) // Inclure la table actuelle même si elle n'est pas disponible
            ->get();
        
        // Charger les catégories et les plats pour le restaurant
        $categories = $restaurant->categories()->with('items')->get();
        
        return view('reservations.edit', compact('reservation', 'restaurant', 'categories', 'tables'));
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
            'date' => 'required|date|after:now',
            'time' => 'required|string',
            'guests' => 'required|integer|min:1|max:20',
            'table_id' => 'required|exists:tables,id',
            'special_requests' => 'nullable|string|max:500',
            'add_order' => 'nullable|boolean',
            'keep_current_items' => 'nullable|in:0,1'
        ]);
        
        // Combiner la date et l'heure
        $reservationDateTime = date('Y-m-d H:i:s', strtotime($request->date . ' ' . $request->time));
        
        // Vérifier que la table appartient au restaurant
        $table = Table::findOrFail($request->table_id);
        if ($table->restaurant_id != $reservation->restaurant_id) {
            return redirect()->route('reservations.edit', $reservation->id)
                ->with('error', 'Table invalide.');
        }
        
        // Vérifier que la table est disponible (en excluant la réservation actuelle)
        $reservationDate = new \DateTime($reservationDateTime);
        if (!$this->isTableAvailable($table->id, $reservationDate, $reservation->id)) {
            return redirect()->route('reservations.edit', $reservation->id)
                ->with('error', 'Cette table n\'est plus disponible pour cette date et heure.');
        }
        
        // Vérifier que la capacité de la table est suffisante
        if ($table->capacity < $request->guests) {
            return redirect()->route('reservations.edit', $reservation->id)
                ->with('error', 'Cette table ne peut pas accueillir autant de personnes.');
        }
        
        // Mettre à jour la réservation
        $reservation->table_id = $table->id;
        $reservation->reservation_date = $reservationDate;
        $reservation->guests_number = $request->guests;
        $reservation->special_requests = $request->special_requests;
        
        $reservation->save();
        
        // Gérer les plats précommandés
        if ($request->has('keep_current_items') && $request->keep_current_items == '1') {
            // L'utilisateur a choisi de conserver les plats actuels, ne pas modifier les plats précommandés
        } else if ($request->has('items')) {
            // L'utilisateur a modifié les plats précommandés
            // Si une commande existe déjà, la mettre à jour
            if ($reservation->order_id) {
                $order = Order::find($reservation->order_id);
                
                // Supprimer les anciens plats
                DB::table('order_items')->where('order_id', $order->id)->delete();
                
                // Ajouter les nouveaux plats
                $totalPrice = 0;
                foreach ($request->items as $itemId => $itemData) {
                    if ($itemData['quantity'] > 0) {
                        $item = Item::find($itemId);
                        if ($item && $item->restaurant_id == $reservation->restaurant_id) {
                            $orderItem = [
                                'order_id' => $order->id,
                                'item_id' => $itemId,
                                'quantity' => $itemData['quantity'],
                                'price' => $item->price,
                                'special_instructions' => $itemData['special_instructions'] ?? null,
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                            
                            DB::table('order_items')->insert($orderItem);
                            $totalPrice += $item->price * $itemData['quantity'];
                        }
                    }
                }
                
                // Mettre à jour le prix total de la commande
                $order->total_price = $totalPrice;
                $order->save();
            }
        }
        
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
        
        // Annuler également la commande associée si elle existe
        if ($reservation->order_id) {
            $order = Order::findOrFail($reservation->order_id);
            if ($order->status !== Order::STATUS_COMPLETED && $order->status !== Order::STATUS_CANCELLED) {
                $order->status = Order::STATUS_CANCELLED;
                $order->save();
            }
        }
        
        $reservation->status = Reservation::STATUS_CANCELLED;
        $reservation->save();
        
        return redirect()->route('reservations.show', $reservation->id)
            ->with('success', 'Réservation annulée avec succès. ' . ($reservation->order_id ? 'La commande associée a également été annulée.' : ''));
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
            'reservation_id' => $reservation->id, // Lien direct vers la réservation
            'status' => Order::STATUS_PENDING,
            'total_amount' => 0,
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
    
    /**
     * Afficher une réservation spécifique pour un restaurant (pour restaurateurs).
     */
    public function restaurantReservation(Request $request, $restaurantId, $reservationId)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur est bien propriétaire du restaurant
        $restaurant = $user->restaurants()->where('id', $restaurantId)->first();
        if (!$restaurant) {
            abort(403, 'Ce restaurant ne vous appartient pas.');
        }
        
        // Récupérer la réservation
        $reservation = Reservation::where('id', $reservationId)
            ->where('restaurant_id', $restaurantId)
            ->with(['restaurant', 'table', 'user', 'order'])
            ->firstOrFail();
        
        return view('restaurateur.reservations.show', compact('reservation', 'restaurant'));
    }
    
    /**
     * Mettre à jour le statut d'une réservation (pour restaurateurs).
     */
    public function restaurantUpdateStatus(Request $request, $restaurantId, $reservationId)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur est bien propriétaire du restaurant
        $restaurant = $user->restaurants()->where('id', $restaurantId)->first();
        if (!$restaurant) {
            abort(403, 'Ce restaurant ne vous appartient pas.');
        }
        
        // Valider la requête
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ]);
        
        // Récupérer la réservation
        $reservation = Reservation::where('id', $reservationId)
            ->where('restaurant_id', $restaurantId)
            ->firstOrFail();
        
        // Mettre à jour le statut
        $reservation->status = $request->status;
        $reservation->save();
        
        // Journal de l'opération
        Log::info("Statut de réservation mis à jour", [
            'user_id' => $user->id,
            'reservation_id' => $reservation->id,
            'old_status' => $reservation->getOriginal('status'),
            'new_status' => $request->status
        ]);
        
        return redirect()->route('restaurant.reservations.show', [$restaurantId, $reservationId])
            ->with('success', 'Statut de la réservation mis à jour avec succès.');
    }
}

