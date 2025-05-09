<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    /**
     * Affiche la liste des ru00e9servations
     */
    public function index()
    {
        $reservations = Reservation::with(['restaurant', 'user', 'table'])->paginate(10);
        return view('admin.reservations.index', compact('reservations'));
    }

    /**
     * Affiche le formulaire de cru00e9ation d'une ru00e9servation
     */
    public function create()
    {
        $restaurants = Restaurant::all();
        $users = User::where('role', 'client')->get();
        return view('admin.reservations.create', compact('restaurants', 'users'));
    }

    /**
     * Enregistre une nouvelle ru00e9servation
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
            'notes' => 'nullable|string',
        ]);

        $reservation = new Reservation();
        $reservation->restaurant_id = $request->restaurant_id;
        $reservation->user_id = $request->user_id;
        $reservation->table_id = $request->table_id;
        $reservation->reservation_date = $request->reservation_date;
        $reservation->guests_number = $request->guests_number;
        $reservation->status = $request->status;
        $reservation->notes = $request->notes;
        $reservation->save();

        return redirect()->route('admin.reservations.index')
            ->with('success', 'Ru00e9servation cru00e9u00e9e avec succu00e8s');
    }

    /**
     * Affiche les du00e9tails d'une ru00e9servation
     */
    public function show(Reservation $reservation)
    {
        return view('admin.reservations.show', compact('reservation'));
    }

    /**
     * Affiche le formulaire de modification d'une ru00e9servation
     */
    public function edit(Reservation $reservation)
    {
        $restaurants = Restaurant::all();
        $users = User::where('role', 'client')->get();
        $tables = Table::where('restaurant_id', $reservation->restaurant_id)->get();
        return view('admin.reservations.edit', compact('reservation', 'restaurants', 'users', 'tables'));
    }

    /**
     * Met u00e0 jour une ru00e9servation
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
            'notes' => 'nullable|string',
        ]);

        $reservation->restaurant_id = $request->restaurant_id;
        $reservation->user_id = $request->user_id;
        $reservation->table_id = $request->table_id;
        $reservation->reservation_date = $request->reservation_date;
        $reservation->guests_number = $request->guests_number;
        $reservation->status = $request->status;
        $reservation->notes = $request->notes;
        $reservation->save();

        return redirect()->route('admin.reservations.index')
            ->with('success', 'Ru00e9servation mise u00e0 jour avec succu00e8s');
    }

    /**
     * Supprime une ru00e9servation
     */
    public function destroy(Reservation $reservation)
    {
        $reservation->delete();
        return redirect()->route('admin.reservations.index')
            ->with('success', 'Ru00e9servation supprimu00e9e avec succu00e8s');
    }

    /**
     * Ru00e9cupu00e8re les tables disponibles pour un restaurant et une date donnu00e9s
     */
    public function getTables(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'reservation_date' => 'required|date',
            'guests_number' => 'required|integer|min:1',
        ]);

        $restaurant_id = $request->restaurant_id;
        $reservation_date = $request->reservation_date;
        $guests_number = $request->guests_number;

        // Ru00e9cupu00e9rer toutes les tables du restaurant qui peuvent accueillir le nombre de personnes
        $tables = Table::where('restaurant_id', $restaurant_id)
            ->where('capacity', '>=', $guests_number)
            ->get();

        // Filtrer les tables du00e9ju00e0 ru00e9servu00e9es u00e0 cette date/heure
        $reservedTableIds = Reservation::where('restaurant_id', $restaurant_id)
            ->where('reservation_date', $reservation_date)
            ->where('status', '!=', 'cancelled')
            ->pluck('table_id')
            ->toArray();

        $availableTables = $tables->filter(function($table) use ($reservedTableIds) {
            return !in_array($table->id, $reservedTableIds);
        });

        return response()->json([
            'tables' => $availableTables
        ]);
    }
}
