<?php

namespace Database\Factories;

use App\Models\Table;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

class TableFactory extends Factory
{
    protected $model = Table::class;

    public function definition()
    {
        return [
            'restaurant_id' => Restaurant::factory(),
            'name' => $this->faker->randomElement(['Table', 'Table d\'angle', 'Banquette']) . ' ' . $this->faker->numberBetween(1, 20),
            'capacity' => $this->faker->numberBetween(2, 10),
            'location' => $this->faker->randomElement(['Terrasse', 'Intérieur', 'Fenêtre', 'Bar', 'Étage', 'Fond']),
            'is_available' => $this->faker->boolean(80),
        ];
    }
}
