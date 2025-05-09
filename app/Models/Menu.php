<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    /**
     * Utilise la factory pour la création de données de test
     */
    use HasFactory;
    
    /**
     * Attributs assignables en masse
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'restaurant_id',
    ];
    
    /**
     * Restaurant associé au menu
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
    
    /**
     * Plats associés au menu
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
