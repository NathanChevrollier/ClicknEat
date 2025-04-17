<?php

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Table;

test('un restaurateur peut voir ses tables', function () {
    // Créer un utilisateur restaurateur
    $user = User::factory()->create(['role' => 'restaurateur']);
    
    // Créer un restaurant associé à ce restaurateur
    $restaurant = Restaurant::factory()->create(['user_id' => $user->id]);
    
    // Créer une table pour ce restaurant
    $table = Table::factory()->create(['restaurant_id' => $restaurant->id]);
    
    // Accéder à la page des tables du restaurant en tant que restaurateur
    $response = actingAs($user)
        ->get(route('restaurants.tables.index', $restaurant->id));
    
    // Vérifier que la page s'affiche correctement
    $response->assertStatus(200);
});

test('un restaurateur ne peut pas voir les tables d\'autres restaurants', function () {
    // Créer un utilisateur restaurateur
    $user = User::factory()->create(['role' => 'restaurateur']);
    
    // Créer un restaurant qui n'appartient pas à ce restaurateur
    $otherRestaurant = Restaurant::factory()->create();
    
    // Créer une table pour cet autre restaurant
    $table = Table::factory()->create(['restaurant_id' => $otherRestaurant->id]);
    
    // Tenter d'accéder aux tables de l'autre restaurant
    $response = actingAs($user)
        ->get(route('restaurants.tables.index', $otherRestaurant->id));
    
    // Vérifier que l'accès est interdit
    $response->assertStatus(403);
});
