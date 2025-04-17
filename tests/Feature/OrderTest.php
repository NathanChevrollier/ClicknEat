<?php

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\Item;
use App\Models\Order;

test('un client peut passer une commande', function () {
    $client = User::factory()->create(['role' => 'client']);
    $restaurateur = User::factory()->create(['role' => 'restaurateur']);
    $restaurant = Restaurant::factory()->create(['user_id' => $restaurateur->id]);
    $category = Category::factory()->create(['restaurant_id' => $restaurant->id]);
    $item1 = Item::factory()->create(['category_id' => $category->id, 'price' => 1000]);
    $item2 = Item::factory()->create(['category_id' => $category->id, 'price' => 1500]);
    
    $response = $this->actingAs($client)
        ->post(route('orders.store', $restaurant), [
            'items' => [
                $item1->id => ['quantity' => 2, 'id' => $item1->id],
                $item2->id => ['quantity' => 1, 'id' => $item2->id]
            ],
            'notes' => 'Test notes'
        ]);
    
    $response->assertRedirect(route('orders.index'));
    
    $this->assertDatabaseHas('orders', [
        'user_id' => $client->id,
        'restaurant_id' => $restaurant->id,
        'status' => 'pending',
        'total_price' => 3500, // 2 * 1000 + 1 * 1500
        'notes' => 'Test notes'
    ]);
    
    $order = Order::where('user_id', $client->id)->first();
    $this->assertNotNull($order);
    
    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'item_id' => $item1->id,
        'quantity' => 2,
        'price' => 1000
    ]);
    
    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'item_id' => $item2->id,
        'quantity' => 1,
        'price' => 1500
    ]);
});

test('un restaurateur peut mettre u00e0 jour le statut d\'une commande', function () {
    $client = User::factory()->create(['role' => 'client']);
    $restaurateur = User::factory()->create(['role' => 'restaurateur']);
    $restaurant = Restaurant::factory()->create(['user_id' => $restaurateur->id]);
    $order = Order::factory()->create([
        'user_id' => $client->id,
        'restaurant_id' => $restaurant->id,
        'status' => 'pending',
        'total_price' => 2000
    ]);
    
    $response = $this->actingAs($restaurateur)
        ->patch(route('orders.update.status', $order), [
            'status' => 'confirmed'
        ]);
    
    $response->assertRedirect(route('orders.show', $order));
    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'confirmed'
    ]);
});

test('un client peut annuler sa commande en attente', function () {
    $client = User::factory()->create(['role' => 'client']);
    $restaurant = Restaurant::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $client->id,
        'restaurant_id' => $restaurant->id,
        'status' => 'pending',
        'total_price' => 2000
    ]);
    
    $response = $this->actingAs($client)
        ->patch(route('orders.cancel', $order));
    
    $response->assertRedirect(route('orders.index'));
    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'cancelled'
    ]);
});

test('un client ne peut pas annuler une commande en pru00e9paration', function () {
    $client = User::factory()->create(['role' => 'client']);
    $restaurant = Restaurant::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $client->id,
        'restaurant_id' => $restaurant->id,
        'status' => 'preparing',
        'total_price' => 2000
    ]);
    
    $response = $this->actingAs($client)
        ->patch(route('orders.cancel', $order));
    
    $response->assertStatus(403);
    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'preparing'
    ]);
});
