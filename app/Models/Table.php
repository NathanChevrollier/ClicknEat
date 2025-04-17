<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Restaurant;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class Table extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'restaurant_id',
        'name',
        'capacity',
        'is_available',
        'location',
        'description',
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_available' => 'boolean',
        'capacity' => 'integer',
    ];

    /**
     * Obtenir le restaurant auquel appartient cette table.
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Obtenir les réservations pour cette table.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Vérifier si la table est disponible pour une date et heure spécifiques.
     *
     * @param \DateTime $dateTime
     * @return bool
     */
    public function isAvailableAt(\DateTime $dateTime): bool
    {
        // Vérifier si la table est généralement disponible
        if (!$this->is_available) {
            return false;
        }

        // Vérifier s'il n'y a pas de réservation à cette date/heure
        $conflictingReservations = $this->reservations()
            ->where('status', '!=', 'cancelled')
            ->where('reservation_date', '<=', $dateTime->format('Y-m-d H:i:s'))
            ->where(\DB::raw('DATE_ADD(reservation_date, INTERVAL 2 HOUR)'), '>=', $dateTime->format('Y-m-d H:i:s'))
            ->count();

        return $conflictingReservations === 0;
    }
}
