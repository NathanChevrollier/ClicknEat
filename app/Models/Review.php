<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Restaurant;

class Review extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'restaurant_id',
        'rating',
        'comment',
        'is_approved',
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
    ];

    /**
     * Récupérer l'utilisateur qui a laissé l'avis.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Récupérer le restaurant concerné par l'avis.
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
