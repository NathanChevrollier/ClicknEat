<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * Attributs assignables en masse
     */
    protected $fillable = [
        'order_id',
        'item_id',
        'quantity',
        'price',
        'menu_id',
    ];

    /**
     * La commande associée à cet item de commande
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Le plat associé à cet item de commande
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Le menu associé à cet item de commande (si applicable)
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
