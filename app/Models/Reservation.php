<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Order;
use App\Models\Item;

class Reservation extends Model
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
        'table_id',
        'order_id',
        'reservation_date',
        'guests_number',
        'status',
        'special_requests',
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reservation_date' => 'datetime',
        'guests_number' => 'integer',
    ];

    /**
     * Statuts possibles d'une réservation
     */
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    /**
     * Récupérer tous les statuts possibles
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_CANCELLED,
            self::STATUS_COMPLETED,
        ];
    }

    /**
     * Obtenir l'utilisateur qui a fait la réservation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir le restaurant concerné par la réservation.
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Obtenir la table réservée.
     */
    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Obtenir la commande associée à la réservation.
     */
    public function order()
    {
        return $this->hasOne(Order::class);
    }

    /**
     * Obtenir les plats précommandés pour cette réservation.
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'reservation_items')
            ->withPivot('quantity', 'price', 'special_instructions')
            ->withTimestamps();
    }

    /**
     * Vérifier si la réservation peut être annulée.
     *
     * @return bool
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]) && 
               $this->reservation_date > now()->addHours(2);
    }

    /**
     * Vérifier si la réservation peut être modifiée.
     *
     * @return bool
     */
    public function canBeModified(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]) && 
               $this->reservation_date > now()->addHours(2);
    }
}
