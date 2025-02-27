<?php

use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/restaurants', [RestaurantController::class, 'index'])->name('restaurants.index');
Route::get('/restaurants/{id}/show', [RestaurantController::class, 'show'])->name('restaurants.show');
Route::get('/restaurants/create', [RestaurantController::class, 'create'])->name('restaurants.create');
Route::post('/restaurants', [RestaurantController::class, 'store'])->name('restaurants.store');
Route::get('/restaurants/{id}/edit', [RestaurantController::class, 'edit'])->name('restaurants.edit');
Route::put('/restaurants/{id}/update', [RestaurantController::class, 'update'])->name('restaurants.update');
Route::delete('/restaurants/{id}/destroy', [RestaurantController::class, 'destroy'])->name('restaurants.destroy');

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{id}/show', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
Route::put('/categories/{id}/update', [CategoryController::class, 'update'])->name('categories.update');
Route::delete('/categories/{id}/destroy', [CategoryController::class, 'destroy'])->name('categories.destroy');

Route::get('/items', [ItemController::class, 'index'])->name('items.index');
Route::get('/items/{id}/show', [ItemController::class, 'show'])->name('items.show');
Route::get('/items/create', [ItemController::class, 'create'])->name('items.create');
Route::post('/items', [ItemController::class, 'store'])->name('items.store');
Route::get('/items/{id}/edit', [ItemController::class, 'edit'])->name('items.edit');
Route::put('/items/{id}/update', [ItemController::class, 'update'])->name('items.update');
Route::delete('/items/{id}/destroy', [ItemController::class, 'destroy'])->name('items.destroy');