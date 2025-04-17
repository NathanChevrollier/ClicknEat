<?php

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Reservation;

test('un restaurateur peut voir ses réservations', function () {
    // Créer un utilisateur restaurateur
    $user = User::factory()->create(['role' => 'restaurateur']);
    
    // Créer un restaurant associé à ce restaurateur
    $restaurant = Restaurant::factory()->create(['user_id' => $user->id]);
    
    // Créer une réservation pour ce restaurant
    $reservation = Reservation::factory()->create(['restaurant_id' => $restaurant->id]);
    
    // Accéder à la page des réservations en tant que restaurateur
    $response = actingAs($user)
        ->get(route('reservations.index'));
    
    // Vérifier que la page s'affiche correctement
    $response->assertStatus(200);
    
    // Vérifier que la réservation est visible
    $response->assertSee($reservation->id);
});

test('un restaurateur ne peut pas voir les réservations d\'autres restaurants', function () {
    // Créer un utilisateur restaurateur
    $user = User::factory()->create(['role' => 'restaurateur']);
    
    // Créer un restaurant qui n'appartient pas à ce restaurateur
    $otherRestaurant = Restaurant::factory()->create();
    
    // Créer une réservation pour cet autre restaurant
    $reservation = Reservation::factory()->create(['restaurant_id' => $otherRestaurant->id]);
    
    // Tenter d'accéder aux réservations de l'autre restaurant
    $response = actingAs($user)
        ->get(route('reservations.index', ['restaurant' => $otherRestaurant->id]));
    
    // Vérifier que l'accès est interdit
    $response->assertStatus(403);
});
