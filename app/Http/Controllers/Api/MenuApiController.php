<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Item;
use Illuminate\Support\Facades\Log;

class MenuApiController extends Controller
{
    /**
     * Récupère tous les plats d'un menu spécifique
     * 
     * @param int $id ID du menu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getItems($id, Request $request)
    {
        try {
            // Simplification maximale - juste récupérer les plats par menu_id
            $items = Item::where('menu_id', $id)->get();
            
            // Retourner uniquement les ID et noms des plats pour débogage
            $simple_items = [];
            foreach ($items as $item) {
                $simple_items[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price
                ];
            }
            
            return response()->json($simple_items);
        } catch (\Exception $e) {
            // Si échec, retourner un message simpli
            return response()->json([
                'error' => $e->getMessage(),
                'id_requested' => $id
            ], 500);
        }
    }
}
