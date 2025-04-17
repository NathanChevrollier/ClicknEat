<?php

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Item;

test('un restaurateur peut voir ses plats', function () {
    // Créer un utilisateur restaurateur
    $user = User::factory()->create(['role' => 'restaurateur']);
    
    // Créer un restaurant associé à ce restaurateur
    $restaurant = Restaurant::factory()->create(['user_id' => $user->id]);
    
    // Créer un plat pour ce restaurant
    $item = Item::factory()->create(['restaurant_id' => $restaurant->id]);
    
    // Accéder à la page des plats en tant que restaurateur
    $response = actingAs($user)
        ->get(route('items.index'));
    
    // Vérifier que la page s'affiche correctement
    $response->assertStatus(200);
});

test('un restaurateur ne peut pas voir les plats d\'autres restaurants', function () {
    // Créer un utilisateur restaurateur
    $user = User::factory()->create(['role' => 'restaurateur']);
    
    // Créer un restaurant qui n'appartient pas à ce restaurateur
    $otherRestaurant = Restaurant::factory()->create();
    
    // Créer un plat pour cet autre restaurant
    $item = Item::factory()->create(['restaurant_id' => $otherRestaurant->id]);
    
    // Tenter d'accéder aux plats de l'autre restaurant
    $response = actingAs($user)
        ->get(route('items.index', ['restaurant' => $otherRestaurant->id]));
    
    // Vérifier que l'accès est interdit
    $response->assertStatus(403);
});
