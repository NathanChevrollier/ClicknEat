<?php

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Order;

test('un utilisateur peut être un client', function () {
    $user = User::factory()->create(['role' => 'client']);
    
    expect($user->isClient())->toBeTrue();
    expect($user->isRestaurateur())->toBeFalse();
});

test('un utilisateur peut être un restaurateur', function () {
    $user = User::factory()->create(['role' => 'restaurateur']);
    
    expect($user->isClient())->toBeFalse();
    expect($user->isRestaurateur())->toBeTrue();
});

test('un restaurateur peut avoir plusieurs restaurants', function () {
    $user = User::factory()->create(['role' => 'restaurateur']);
    $restaurant1 = Restaurant::factory()->create(['user_id' => $user->id]);
    $restaurant2 = Restaurant::factory()->create(['user_id' => $user->id]);
    
    expect($user->restaurants)->toHaveCount(2);
    expect($user->restaurants->contains($restaurant1))->toBeTrue();
    expect($user->restaurants->contains($restaurant2))->toBeTrue();
});

test('un client peut avoir plusieurs commandes', function () {
    $user = User::factory()->create(['role' => 'client']);
    $restaurant = Restaurant::factory()->create();
    
    $order1 = Order::factory()->create([
        'user_id' => $user->id,
        'restaurant_id' => $restaurant->id
    ]);
    
    $order2 = Order::factory()->create([
        'user_id' => $user->id,
        'restaurant_id' => $restaurant->id
    ]);
    
    expect($user->orders)->toHaveCount(2);
    expect($user->orders->contains($order1))->toBeTrue();
    expect($user->orders->contains($order2))->toBeTrue();
});
