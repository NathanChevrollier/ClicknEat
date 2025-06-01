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
            Log::info('Récupération des plats pour le menu ' . $id);
            Log::info('Paramètres : ' . json_encode($request->all()));
            
            // Récupérer le menu avec ses plats associés
            $menu = Menu::findOrFail($id);
            
            // Récupération des plats du menu
            $query = Item::where('menu_id', $id);
            
            // Filtre pour les plats actifs uniquement si demandé
            if ($request->has('active_only') && $request->active_only == 1) {
                $query->where('is_available', 1);
            }
            
            $items = $query->get();
            
            Log::info('Nombre de plats trouvés : ' . count($items));
            
            // Retourner les détails des plats
            $result = [];
            foreach ($items as $item) {
                $result[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'is_available' => $item->is_available
                ];
            }
            
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des plats du menu : ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'id_requested' => $id
            ], 500);
        }
    }
}
