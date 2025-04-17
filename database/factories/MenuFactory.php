<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition()
    {
        return [
            'restaurant_id' => Restaurant::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'is_active' => $this->faker->boolean(80),
        ];
    }
}
