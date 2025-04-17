<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    /**
     * Utilise la factory pour la création de données de test
     */
    use HasFactory;

    /**
     * Nom de la table en base de données
     */
    protected $table = "restaurants";

    /**
     * Attributs assignables en masse
     */
    protected $fillable = [
        "name",
        "phone",
        "address",
        "description",
        "user_id",
        "is_open",
        "accepts_reservations"
    ];

    /**
     * Attributs à convertir
     */
    protected $casts = [
        'is_open' => 'boolean',
        'accepts_reservations' => 'boolean',
    ];

    /**
     * Catégories du restaurant
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Propriétaire du restaurant
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Plats du restaurant (via les catégories)
     */
    public function items()
    {
        return $this->hasManyThrough(Item::class, Category::class);
    }
    
    /**
     * Commandes du restaurant
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Tables du restaurant
     */
    public function tables()
    {
        return $this->hasMany(Table::class);
    }

    /**
     * Réservations du restaurant
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
    
    /**
     * Avis du restaurant
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    
    /**
     * Calcule la note moyenne du restaurant
     * 
     * @return float
     */
    public function getAvgRatingAttribute()
    {
        return $this->reviews()->where('is_approved', true)->avg('rating') ?? 0;
    }
    
    /**
     * Retourne le nombre d'avis du restaurant
     * 
     * @return int
     */
    public function getReviewCountAttribute()
    {
        return $this->reviews()->where('is_approved', true)->count();
    }
    
    /**
     * Moyenne des notes du restaurant (ancienne compatibilité)
     * @return float
     */
    public function averageRating()
    {
        // Pour compatibilité avec la vue, on renvoie la même logique que getAvgRatingAttribute
        return $this->reviews()->where('is_approved', true)->avg('rating') ?? 0;
    }

    /**
     * Vérifier la disponibilité des tables pour une date et un nombre de personnes donnés
     *
     * @param \DateTime $dateTime
     * @param int $guestsNumber
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableTables(\DateTime $dateTime, int $guestsNumber)
    {
        // Récupérer toutes les tables du restaurant qui peuvent accueillir le nombre de personnes
        $tables = $this->tables()
            ->where('is_available', true)
            ->where('capacity', '>=', $guestsNumber)
            ->get();

        // Filtrer les tables qui n'ont pas de réservation à cette date/heure
        return $tables->filter(function ($table) use ($dateTime) {
            return $table->isAvailableAt($dateTime);
        });
    }
}
