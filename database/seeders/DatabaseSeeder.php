<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\Item;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\DemoDataSeeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Si vous souhaitez utiliser les données de démonstration complètes,
        // décommentez la ligne ci-dessous et commentez le reste du code
        // $this->call(DemoDataSeeder::class);
        
        // Création des utilisateurs
        $client1 = User::factory()->create([
            'name' => 'Client Test',
            'email' => 'client@example.com',
            'password' => Hash::make('password'),
            'role' => 'client'
        ]);

        $client2 = User::factory()->create([
            'name' => 'Client Demo',
            'email' => 'demo@example.com',
            'password' => Hash::make('password'),
            'role' => 'client'
        ]);
        
        $restaurateur1 = User::factory()->create([
            'name' => 'Restaurateur Test',
            'email' => 'resto@example.com',
            'password' => Hash::make('password'),
            'role' => 'restaurateur'
        ]);

        $restaurateur2 = User::factory()->create([
            'name' => 'Restaurateur Demo',
            'email' => 'resto2@example.com',
            'password' => Hash::make('password'),
            'role' => 'restaurateur'
        ]);

        // Création des restaurants
        $restaurant1 = Restaurant::factory()->create([
            'name' => 'Pizza Deluxe',
            'user_id' => $restaurateur1->id
        ]);

        $restaurant2 = Restaurant::factory()->create([
            'name' => 'Burger House',
            'user_id' => $restaurateur1->id
        ]);

        $restaurant3 = Restaurant::factory()->create([
            'name' => 'Sushi Master',
            'user_id' => $restaurateur2->id
        ]);

        // Création des catégories
        $pizzaCategory = Category::factory()->create([
            'name' => 'Pizzas',
            'restaurant_id' => $restaurant1->id
        ]);

        $pastaCategory = Category::factory()->create([
            'name' => 'Pâtes',
            'restaurant_id' => $restaurant1->id
        ]);

        $burgerCategory = Category::factory()->create([
            'name' => 'Burgers',
            'restaurant_id' => $restaurant2->id
        ]);

        $friesCategory = Category::factory()->create([
            'name' => 'Accompagnements',
            'restaurant_id' => $restaurant2->id
        ]);

        $sushiCategory = Category::factory()->create([
            'name' => 'Sushis',
            'restaurant_id' => $restaurant3->id
        ]);

        $makiCategory = Category::factory()->create([
            'name' => 'Makis',
            'restaurant_id' => $restaurant3->id
        ]);

        // Création des plats
        $margherita = Item::factory()->create([
            'name' => 'Pizza Margherita',
            'description' => 'Tomate, mozzarella, basilic',
            'price' => 1000, // 10.00 €
            'is_active' => true,
            'category_id' => $pizzaCategory->id
        ]);

        $regina = Item::factory()->create([
            'name' => 'Pizza Regina',
            'description' => 'Tomate, mozzarella, jambon, champignons',
            'price' => 1200, // 12.00 €
            'is_active' => true,
            'category_id' => $pizzaCategory->id
        ]);

        $carbonara = Item::factory()->create([
            'name' => 'Pâtes Carbonara',
            'description' => 'Crème, lardons, oeuf, parmesan',
            'price' => 1100, // 11.00 €
            'is_active' => true,
            'category_id' => $pastaCategory->id
        ]);

        $classicBurger = Item::factory()->create([
            'name' => 'Classic Burger',
            'description' => 'Boeuf, salade, tomate, oignon, sauce burger',
            'price' => 950, // 9.50 €
            'is_active' => true,
            'category_id' => $burgerCategory->id
        ]);

        $cheeseBurger = Item::factory()->create([
            'name' => 'Cheese Burger',
            'description' => 'Boeuf, cheddar, salade, tomate, oignon, sauce burger',
            'price' => 1050, // 10.50 €
            'is_active' => true,
            'category_id' => $burgerCategory->id
        ]);

        $fries = Item::factory()->create([
            'name' => 'Frites',
            'description' => 'Portion de frites maison',
            'price' => 350, // 3.50 €
            'is_active' => true,
            'category_id' => $friesCategory->id
        ]);

        $saumonSushi = Item::factory()->create([
            'name' => 'Sushi Saumon',
            'description' => 'Riz vinaigré, saumon frais',
            'price' => 150, // 1.50 € par pièce
            'is_active' => true,
            'category_id' => $sushiCategory->id
        ]);

        $thonSushi = Item::factory()->create([
            'name' => 'Sushi Thon',
            'description' => 'Riz vinaigré, thon frais',
            'price' => 180, // 1.80 € par pièce
            'is_active' => true,
            'category_id' => $sushiCategory->id
        ]);

        $californiaRoll = Item::factory()->create([
            'name' => 'California Roll',
            'description' => 'Riz vinaigré, avocat, surimi, concombre',
            'price' => 800, // 8.00 € les 6 pièces
            'is_active' => true,
            'category_id' => $makiCategory->id
        ]);

        // Création des commandes
        $order1 = Order::factory()->create([
            'user_id' => $client1->id,
            'restaurant_id' => $restaurant1->id,
            'status' => 'completed',
            'total_price' => 2200, // 22.00 €
            'notes' => 'Sans oignon s\'il vous plaît',
            'created_at' => now()->subDays(5)
        ]);

        $order1->items()->attach([
            $margherita->id => ['quantity' => 1, 'price' => $margherita->price],
            $carbonara->id => ['quantity' => 1, 'price' => $carbonara->price]
        ]);

        $order2 = Order::factory()->create([
            'user_id' => $client1->id,
            'restaurant_id' => $restaurant2->id,
            'status' => 'confirmed',
            'total_price' => 1400, // 14.00 €
            'notes' => 'Sauce burger à part',
            'created_at' => now()->subHours(2)
        ]);

        $order2->items()->attach([
            $classicBurger->id => ['quantity' => 1, 'price' => $classicBurger->price],
            $fries->id => ['quantity' => 1, 'price' => $fries->price]
        ]);

        $order3 = Order::factory()->create([
            'user_id' => $client2->id,
            'restaurant_id' => $restaurant3->id,
            'status' => 'pending',
            'total_price' => 1600, // 16.00 €
            'notes' => '',
            'created_at' => now()->subMinutes(30)
        ]);

        $order3->items()->attach([
            $saumonSushi->id => ['quantity' => 4, 'price' => $saumonSushi->price],
            $thonSushi->id => ['quantity' => 4, 'price' => $thonSushi->price],
            $californiaRoll->id => ['quantity' => 1, 'price' => $californiaRoll->price]
        ]);

        // Création de données supplémentaires
        User::factory(5)->create(['role' => 'client']);
        User::factory(3)->create(['role' => 'restaurateur']);
        
        // Créer quelques restaurants, catégories et items supplémentaires
        Restaurant::factory(3)
            ->create(['user_id' => User::where('role', 'restaurateur')->inRandomOrder()->first()->id])
            ->each(function ($restaurant) {
                $categories = Category::factory(rand(1, 3))->create(['restaurant_id' => $restaurant->id]);
                
                foreach ($categories as $category) {
                    Item::factory(rand(3, 6))->create(['category_id' => $category->id]);
                }
            });
    }
}
