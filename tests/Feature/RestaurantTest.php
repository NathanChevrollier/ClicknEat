<?php

use App\Models\User;
use App\Models\Restaurant;

test('un restaurateur peut cru00e9er un restaurant', function () {
    $user = User::factory()->create(['role' => 'restaurateur']);
    
    $response = $this->actingAs($user)
        ->post(route('restaurants.store'), [
            'name' => 'Mon Restaurant Test'
        ]);
    
    $response->assertRedirect(route('restaurants.index'));
    $this->assertDatabaseHas('restaurants', [
        'name' => 'Mon Restaurant Test',
        'user_id' => $user->id
    ]);
});

test('un client ne peut pas cru00e9er un restaurant', function () {
    $user = User::factory()->create(['role' => 'client']);
    
    $response = $this->actingAs($user)
        ->post(route('restaurants.store'), [
            'name' => 'Restaurant Interdit'
        ]);
    
    $response->assertStatus(403);
    $this->assertDatabaseMissing('restaurants', [
        'name' => 'Restaurant Interdit'
    ]);
});

test('un restaurateur peut voir ses restaurants', function () {
    $user = User::factory()->create(['role' => 'restaurateur']);
    $restaurant = Restaurant::factory()->create(['user_id' => $user->id]);
    
    $response = $this->actingAs($user)
        ->get(route('restaurants.index'));
    
    $response->assertStatus(200);
    $response->assertSee($restaurant->name);
});

test('un restaurateur peut modifier son restaurant', function () {
    $user = User::factory()->create(['role' => 'restaurateur']);
    $restaurant = Restaurant::factory()->create(['user_id' => $user->id]);
    
    $response = $this->actingAs($user)
        ->put(route('restaurants.update', $restaurant), [
            'name' => 'Restaurant Modifiu00e9'
        ]);
    
    $response->assertRedirect(route('restaurants.index'));
    $this->assertDatabaseHas('restaurants', [
        'id' => $restaurant->id,
        'name' => 'Restaurant Modifiu00e9'
    ]);
});

test('un restaurateur ne peut pas modifier le restaurant du0027un autre', function () {
    $user1 = User::factory()->create(['role' => 'restaurateur']);
    $user2 = User::factory()->create(['role' => 'restaurateur']);
    $restaurant = Restaurant::factory()->create(['user_id' => $user2->id]);
    
    $response = $this->actingAs($user1)
        ->put(route('restaurants.update', $restaurant), [
            'name' => 'Restaurant Pirate'
        ]);
    
    $response->assertStatus(403);
    $this->assertDatabaseMissing('restaurants', [
        'id' => $restaurant->id,
        'name' => 'Restaurant Pirate'
    ]);
});
