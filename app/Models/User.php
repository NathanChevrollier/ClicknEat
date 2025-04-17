<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Attributs assignables en masse
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * Attributs masqués lors de la sérialisation
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attributs à convertir
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Vérifie si l'utilisateur est restaurateur
     *
     * @return bool
     */
    public function isRestaurateur(): bool
    {
        return $this->role === 'restaurateur';
    }

    /**
     * Vérifie si l'utilisateur est client
     *
     * @return bool
     */
    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    /**
     * Vérifie si l'utilisateur est administrateur
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Restaurants de cet utilisateur
     */
    public function restaurants()
    {
        return $this->hasMany(Restaurant::class);
    }

    /**
     * Commandes de cet utilisateur
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Réservations de cet utilisateur
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
    
    /**
     * Avis laissés par cet utilisateur
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Obtenir les notifications de l'utilisateur
     */
    public function customNotifications()
    {
        return $this->hasMany(Notification::class);
    }
    
    /**
     * Obtenir le nombre de notifications non lues
     */
    public function getUnreadNotificationsCountAttribute()
    {
        return $this->customNotifications()->unread()->count();
    }
}
