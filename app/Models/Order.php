<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * Attributs assignables en masse
     */
    protected $fillable = [
        'user_id',
        'restaurant_id',
        'status',
        'total_amount',
        'delivery_address',
        'notes',
        'reservation_id',
    ];

    /**
     * Valeurs par défaut des attributs
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * Statuts possibles d'une commande
     */
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PREPARING = 'preparing';
    const STATUS_READY = 'ready';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Récupérer tous les statuts possibles
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_PREPARING,
            self::STATUS_READY,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    /**
     * Client qui a passé la commande
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Restaurant concerné par la commande
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Plats inclus dans la commande
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'order_items', 'order_id', 'item_id')
            ->withPivot('quantity', 'price', 'menu_id')
            ->withTimestamps()
            // Spécifier toutes les colonnes explicitement pour éviter l'ambiguïté
            ->select(
                'items.id',
                'items.name',
                'items.description',
                'items.price',
                'items.category_id',
                'items.restaurant_id',
                'items.is_available',
                'items.image',
                'items.created_at as item_created_at',
                'items.updated_at as item_updated_at',
                'items.menu_id as item_menu_id', // Renommer la colonne menu_id de items
                'order_items.menu_id as order_menu_id' // Renommer la colonne menu_id de order_items
            );
    }

    /**
     * Version alternative de la relation items qui évite l'ambiguïté avec menu_id
     */
    public function itemsWithoutAmbiguity()
    {
        return $this->belongsToMany(Item::class, 'order_items', 'order_id', 'item_id')
            ->withPivot('quantity', 'price', 'menu_id')
            ->withTimestamps()
            ->select('items.*', 'order_items.menu_id as order_menu_id');
    }

    /**
     * Récupère les menus de la commande
     */
    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'order_items', 'order_id', 'menu_id')
            ->distinct()
            ->withTimestamps();
    }

    /**
     * Réservation associée à la commande
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
    
    // Ancienne définition supprimée pour éviter les doublons
}
