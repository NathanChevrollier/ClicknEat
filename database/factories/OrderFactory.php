<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * Modèle associé à cette factory
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Définit l'état par défaut du modèle
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create(['role' => 'client'])->id,
            'restaurant_id' => Restaurant::factory(),
            'status' => 'pending',
            'total_price' => $this->faker->numberBetween(1000, 10000),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Définit la commande comme en attente
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
            ];
        });
    }

    /**
     * Définit la commande comme confirmée
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function confirmed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'confirmed',
            ];
        });
    }

    /**
     * Définit la commande comme en préparation
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function preparing()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'preparing',
            ];
        });
    }

    /**
     * Définit la commande comme prête
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function ready()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'ready',
            ];
        });
    }

    /**
     * Définit la commande comme terminée
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
            ];
        });
    }

    /**
     * Définit la commande comme annulée
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function cancelled()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
            ];
        });
    }
}
