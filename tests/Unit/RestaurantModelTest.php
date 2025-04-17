<?php

use App\Models\Restaurant;
use App\Models\User;
use App\Models\Category;
use App\Models\Order;

test('un restaurant appartient à un restaurateur', function () {
    $user = User::factory()->create(['role' => 'restaurateur']);
    $restaurant = Restaurant::factory()->create(['user_id' => $user->id]);
    
    expect($restaurant->user->id)->toBe($user->id);
    expect($restaurant->user->isRestaurateur())->toBeTrue();
});

test('un restaurant peut avoir plusieurs catégories', function () {
    $restaurant = Restaurant::factory()->create();
    $category1 = Category::factory()->create(['restaurant_id' => $restaurant->id]);
    $category2 = Category::factory()->create(['restaurant_id' => $restaurant->id]);
    
    expect($restaurant->categories)->toHaveCount(2);
    expect($restaurant->categories->contains($category1))->toBeTrue();
    expect($restaurant->categories->contains($category2))->toBeTrue();
});

test('un restaurant peut avoir plusieurs commandes', function () {
    $restaurant = Restaurant::factory()->create();
    $user = User::factory()->create(['role' => 'client']);
    
    $order1 = Order::factory()->create([
        'restaurant_id' => $restaurant->id,
        'user_id' => $user->id
    ]);
    
    $order2 = Order::factory()->create([
        'restaurant_id' => $restaurant->id,
        'user_id' => $user->id
    ]);
    
    expect($restaurant->orders)->toHaveCount(2);
    expect($restaurant->orders->contains($order1))->toBeTrue();
    expect($restaurant->orders->contains($order2))->toBeTrue();
});
