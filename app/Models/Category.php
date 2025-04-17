<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * Utilise la factory pour la création de données de test
     */
    use HasFactory;

    /**
     * Nom de la table en base de données
     */
    protected $table = "categories";
    
    /**
     * Attributs assignables en masse
     */
	protected $fillable = [
		"name",
		"restaurant_id"
	];

    /**
     * Restaurant auquel appartient cette catégorie
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
    
    /**
     * Plats de cette catégorie
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
