<?php

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Menu;

test('un restaurateur peut voir ses menus', function () {
    // Créer un utilisateur restaurateur
    $user = User::factory()->create(['role' => 'restaurateur']);
    
    // Créer un restaurant associé à ce restaurateur
    $restaurant = Restaurant::factory()->create(['user_id' => $user->id]);
    
    // Créer un menu pour ce restaurant
    $menu = Menu::factory()->create(['restaurant_id' => $restaurant->id]);
    
    // Accéder à la page des menus en tant que restaurateur
    $response = actingAs($user)
        ->get(route('menus.index'));
    
    // Vérifier que la page s'affiche correctement
    $response->assertStatus(200);
});

test('un restaurateur ne peut pas voir les menus d\'autres restaurants', function () {
    // Créer un utilisateur restaurateur
    $user = User::factory()->create(['role' => 'restaurateur']);
    
    // Créer un restaurant qui n'appartient pas à ce restaurateur
    $otherRestaurant = Restaurant::factory()->create();
    
    // Créer un menu pour cet autre restaurant
    $menu = Menu::factory()->create(['restaurant_id' => $otherRestaurant->id]);
    
    // Tenter d'accéder aux menus de l'autre restaurant
    $response = actingAs($user)
        ->get(route('menus.index', ['restaurant' => $otherRestaurant->id]));
    
    // Vérifier que l'accès est interdit
    $response->assertStatus(403);
});
