<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Item;
use App\Models\Category;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestaurantApiController extends Controller
{
    /**
     * Récupère les plats d'un restaurant
     */
    public function getItems($restaurantId)
    {
        try {
            // Vérifier que le restaurant existe
            $restaurant = Restaurant::findOrFail($restaurantId);
            
            // Récupérer les plats avec leur catégorie
            $items = Item::whereHas('category', function($query) use ($restaurantId) {
                $query->where('restaurant_id', $restaurantId);
            })
            ->with('category')
            ->get();
            
            return response()->json([
                'success' => true,
                'data' => $items,
                'count' => $items->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Récupère les catégories d'un restaurant
     */
    public function getCategories($restaurantId)
    {
        // Vérifier que le restaurant existe
        $restaurant = Restaurant::findOrFail($restaurantId);
        
        // Récupérer les catégories du restaurant
        $categories = Category::where('restaurant_id', $restaurantId)->get();
        
        return response()->json($categories);
    }

    /**
     * Récupère les menus d'un restaurant
     */
    public function getMenus($restaurantId)
    {
        try {
            // Vérifier que le restaurant existe
            $restaurant = Restaurant::findOrFail($restaurantId);
            
            // Récupérer les menus du restaurant
            $menus = Menu::where('restaurant_id', $restaurantId)
                ->with('items')
                ->get();
            
            return response()->json($menus);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
