<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Category;

test('un restaurateur peut voir ses catégories', function () {
    // Créer un utilisateur restaurateur
    $user = User::factory()->create(['role' => 'restaurateur']);
    
    // Créer un restaurant associé à ce restaurateur
    $restaurant = Restaurant::factory()->create(['user_id' => $user->id]);
    
    // Créer une catégorie pour ce restaurant
    $category = Category::factory()->create(['restaurant_id' => $restaurant->id]);
    
    // Accéder à la page des catégories en tant que restaurateur
    $response = actingAs($user)
        ->get(route('categories.index'));
    
    // Vérifier que la page s'affiche correctement
    $response->assertStatus(200);
});

test('un restaurateur ne peut pas voir les catégories d\'autres restaurants', function () {
    // Créer un utilisateur restaurateur
    $user = User::factory()->create(['role' => 'restaurateur']);
    
    // Créer un restaurant qui n'appartient pas à ce restaurateur
    $otherRestaurant = Restaurant::factory()->create();
    
    // Créer une catégorie pour cet autre restaurant
    $category = Category::factory()->create(['restaurant_id' => $otherRestaurant->id]);
    
    // Tenter d'accéder aux catégories de l'autre restaurant
    $response = actingAs($user)
        ->get(route('restaurants.categories.index', ['restaurant' => $otherRestaurant->id]));
    
    // Vérifier que l'accès est interdit
    $response->assertStatus(403);
});
