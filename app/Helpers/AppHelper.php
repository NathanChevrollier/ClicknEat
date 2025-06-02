<?php

namespace App\Helpers;

class AppHelper
{
    /**
     * Formate un prix pour l'affichage
     * 
     * @param float $price Le prix à formater
     * @param bool $divideBy100 Si le prix doit être divisé par 100 (false par défaut)
     * @return string Le prix formaté
     */
    public static function formatPrice($price, $divideBy100 = false)
    {
        if ($divideBy100) {
            $price = $price / 100;
        }
        
        return number_format($price, 2, ',', ' ') . ' €';
    }
}
