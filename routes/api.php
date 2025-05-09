<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RestaurantApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Routes API pour les restaurants
Route::get('/restaurants/{restaurant}/items', [RestaurantApiController::class, 'getItems']);
Route::get('/restaurants/{restaurant}/categories', [RestaurantApiController::class, 'getCategories']);
Route::get('/restaurants/{restaurant}/menus', [RestaurantApiController::class, 'getMenus']);

// Routes API protégées par authentification
Route::middleware('auth')->group(function () {
    // Autres routes API protégées
});
