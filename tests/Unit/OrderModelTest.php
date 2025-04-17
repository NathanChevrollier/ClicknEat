<?php

use App\Models\Order;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Item;
use App\Models\Category;

test('une commande appartient u00e0 un client', function () {
    $user = User::factory()->create(['role' => 'client']);
    $restaurant = Restaurant::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'restaurant_id' => $restaurant->id
    ]);
    
    expect($order->user->id)->toBe($user->id);
    expect($order->user->isClient())->toBeTrue();
});

test('une commande est associu00e9e u00e0 un restaurant', function () {
    $user = User::factory()->create(['role' => 'client']);
    $restaurant = Restaurant::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'restaurant_id' => $restaurant->id
    ]);
    
    expect($order->restaurant->id)->toBe($restaurant->id);
});

test('une commande peut contenir plusieurs items', function () {
    $user = User::factory()->create(['role' => 'client']);
    $restaurant = Restaurant::factory()->create();
    $category = Category::factory()->create(['restaurant_id' => $restaurant->id]);
    
    $item1 = Item::factory()->create(['category_id' => $category->id]);
    $item2 = Item::factory()->create(['category_id' => $category->id]);
    
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'restaurant_id' => $restaurant->id
    ]);
    
    $order->items()->attach([
        $item1->id => ['quantity' => 2, 'price' => $item1->price],
        $item2->id => ['quantity' => 1, 'price' => $item2->price]
    ]);
    
    expect($order->items)->toHaveCount(2);
    expect($order->items->contains($item1))->toBeTrue();
    expect($order->items->contains($item2))->toBeTrue();
    expect($order->items->find($item1->id)->pivot->quantity)->toBe(2);
    expect($order->items->find($item2->id)->pivot->quantity)->toBe(1);
});

test('une commande a un statut par du00e9faut', function () {
    $user = User::factory()->create(['role' => 'client']);
    $restaurant = Restaurant::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'restaurant_id' => $restaurant->id
    ]);
    
    expect($order->status)->toBe('pending');
});

test('le statut du0027une commande peut u00eatre mis u00e0 jour', function () {
    $user = User::factory()->create(['role' => 'client']);
    $restaurant = Restaurant::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'restaurant_id' => $restaurant->id,
        'status' => 'pending'
    ]);
    
    $order->status = 'confirmed';
    $order->save();
    
    expect($order->fresh()->status)->toBe('confirmed');
});
