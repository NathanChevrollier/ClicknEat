<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    /**
     * Utilise la factory pour la création de données de test
     */
    use HasFactory;

    /**
     * Nom de la table en base de données
     */
    protected $table = 'items';

    /**
     * Attributs assignables en masse
     */
    protected $fillable = [
        'name',
        'description',
        'cost',
        'price',
        'is_available',
        'category_id',
        'restaurant_id',
        'menu_id',
    ];

    /**
     * Catégorie à laquelle appartient ce plat
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Menu auquel appartient ce plat (si applicable)
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
