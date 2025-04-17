<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\Item;
use App\Models\Menu;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Exécute le seeder pour créer des données de démonstration.
     */
    public function run()
    {
        // Création de restaurateurs
        $restaurateur1 = User::create([
            'name' => 'Pierre Dupont',
            'email' => 'pierre@restaurant.com',
            'password' => Hash::make('password'),
            'role' => 'restaurateur',
        ]);

        $restaurateur2 = User::create([
            'name' => 'Marie Martin',
            'email' => 'marie@restaurant.com',
            'password' => Hash::make('password'),
            'role' => 'restaurateur',
        ]);

        // Création de clients
        $client1 = User::create([
            'name' => 'Jean Client',
            'email' => 'jean@client.com',
            'password' => Hash::make('password'),
            'role' => 'client',
        ]);

        $client2 = User::create([
            'name' => 'Sophie Cliente',
            'email' => 'sophie@client.com',
            'password' => Hash::make('password'),
            'role' => 'client',
        ]);

        // Création des restaurants pour le premier restaurateur
        $restaurant1 = Restaurant::create([
            'name' => 'La Belle Assiette',
            'description' => 'Restaurant gastronomique français proposant des plats raffinés dans un cadre élégant.',
            'address' => '15 rue de la Gastronomie, 75001 Paris',
            'phone' => '01 23 45 67 89',
            'user_id' => $restaurateur1->id,
        ]);

        // Création des catégories pour le premier restaurant
        $entrees1 = Category::create([
            'name' => 'Entrées',
            'restaurant_id' => $restaurant1->id,
        ]);

        $plats1 = Category::create([
            'name' => 'Plats principaux',
            'restaurant_id' => $restaurant1->id,
        ]);

        $desserts1 = Category::create([
            'name' => 'Desserts',
            'restaurant_id' => $restaurant1->id,
        ]);

        // Création des plats pour les catégories du premier restaurant
        // Entrées
        $item1 = Item::create([
            'name' => 'Foie Gras Maison',
            'description' => 'Foie gras mi-cuit, chutney de figues et pain brioché toasté',
            'price' => 1850, // 18.50€
            'is_active' => true,
            'category_id' => $entrees1->id,
        ]);

        $item2 = Item::create([
            'name' => 'Salade de Chèvre Chaud',
            'description' => 'Mesclun, toasts de chèvre, miel, noix et vinaigrette balsamique',
            'price' => 1250, // 12.50€
            'is_active' => true,
            'category_id' => $entrees1->id,
        ]);

        // Plats
        $item3 = Item::create([
            'name' => 'Filet de Bœuf Rossini',
            'description' => 'Filet de bœuf, escalope de foie gras poêlée, sauce aux truffes et purée maison',
            'price' => 2950, // 29.50€
            'is_active' => true,
            'category_id' => $plats1->id,
        ]);

        $item4 = Item::create([
            'name' => 'Risotto aux Cèpes',
            'description' => 'Risotto crémeux aux cèpes et parmesan affiné 24 mois',
            'price' => 1850, // 18.50€
            'is_active' => true,
            'category_id' => $plats1->id,
        ]);

        // Desserts
        $item5 = Item::create([
            'name' => 'Crème Brûlée à la Vanille',
            'description' => 'Crème brûlée à la vanille de Madagascar',
            'price' => 950, // 9.50€
            'is_active' => true,
            'category_id' => $desserts1->id,
        ]);

        $item6 = Item::create([
            'name' => 'Fondant au Chocolat',
            'description' => 'Fondant au chocolat noir 70%, cœur coulant et glace vanille',
            'price' => 1050, // 10.50€
            'is_active' => true,
            'category_id' => $desserts1->id,
        ]);

        // Création des menus pour le premier restaurant
        $menu1 = Menu::create([
            'name' => 'Menu Découverte',
            'description' => 'Notre menu découverte en 3 plats',
            'price' => 3950, // 39.50€
            'is_active' => true,
            'restaurant_id' => $restaurant1->id,
        ]);

        // Ajout des plats au menu
        $menu1->items()->attach([$item1->id, $item3->id, $item5->id]);

        $menu2 = Menu::create([
            'name' => 'Menu Végétarien',
            'description' => 'Notre menu végétarien en 3 plats',
            'price' => 3250, // 32.50€
            'is_active' => true,
            'restaurant_id' => $restaurant1->id,
        ]);

        // Ajout des plats au menu végétarien
        $menu2->items()->attach([$item2->id, $item4->id, $item6->id]);

        // Création du deuxième restaurant pour le premier restaurateur
        $restaurant2 = Restaurant::create([
            'name' => 'Sushi Master',
            'description' => 'Restaurant japonais authentique proposant des sushis frais et des spécialités japonaises.',
            'address' => '8 rue des Sushis, 75002 Paris',
            'phone' => '01 98 76 54 32',
            'user_id' => $restaurateur1->id,
        ]);

        // Création des catégories pour le deuxième restaurant
        $sushis = Category::create([
            'name' => 'Sushis',
            'restaurant_id' => $restaurant2->id,
        ]);

        $makis = Category::create([
            'name' => 'Makis',
            'restaurant_id' => $restaurant2->id,
        ]);

        $specialites = Category::create([
            'name' => 'Spécialités',
            'restaurant_id' => $restaurant2->id,
        ]);

        // Création des plats pour les catégories du deuxième restaurant
        // Sushis
        $item7 = Item::create([
            'name' => 'Sushi Saumon',
            'description' => 'Sushi au saumon frais (2 pièces)',
            'price' => 450, // 4.50€
            'is_active' => true,
            'category_id' => $sushis->id,
        ]);

        $item8 = Item::create([
            'name' => 'Sushi Thon',
            'description' => 'Sushi au thon rouge (2 pièces)',
            'price' => 550, // 5.50€
            'is_active' => true,
            'category_id' => $sushis->id,
        ]);

        // Makis
        $item9 = Item::create([
            'name' => 'Maki Californien',
            'description' => 'Maki avec avocat, surimi et concombre (6 pièces)',
            'price' => 850, // 8.50€
            'is_active' => true,
            'category_id' => $makis->id,
        ]);

        $item10 = Item::create([
            'name' => 'Maki Spicy Tuna',
            'description' => 'Maki épicé au thon (6 pièces)',
            'price' => 950, // 9.50€
            'is_active' => true,
            'category_id' => $makis->id,
        ]);

        // Spécialités
        $item11 = Item::create([
            'name' => 'Ramen au Porc',
            'description' => 'Soupe ramen avec porc, œuf mollet et légumes',
            'price' => 1450, // 14.50€
            'is_active' => true,
            'category_id' => $specialites->id,
        ]);

        $item12 = Item::create([
            'name' => 'Gyoza',
            'description' => 'Raviolis japonais grillés au porc (6 pièces)',
            'price' => 750, // 7.50€
            'is_active' => true,
            'category_id' => $specialites->id,
        ]);

        // Création des menus pour le deuxième restaurant
        $menu3 = Menu::create([
            'name' => 'Menu Découverte Japonaise',
            'description' => 'Assortiment de nos meilleures spécialités',
            'price' => 2950, // 29.50€
            'is_active' => true,
            'restaurant_id' => $restaurant2->id,
        ]);

        // Ajout des plats au menu
        $menu3->items()->attach([$item7->id, $item9->id, $item11->id]);

        $menu4 = Menu::create([
            'name' => 'Menu Sushi Lover',
            'description' => 'Pour les amateurs de sushis',
            'price' => 2450, // 24.50€
            'is_active' => true,
            'restaurant_id' => $restaurant2->id,
        ]);

        // Ajout des plats au menu
        $menu4->items()->attach([$item8->id, $item10->id, $item12->id]);

        // Création d'un restaurant pour le deuxième restaurateur
        $restaurant3 = Restaurant::create([
            'name' => 'Pizzeria Napoli',
            'description' => 'Authentique pizzeria napolitaine avec four à bois.',
            'address' => '42 rue de Naples, 75008 Paris',
            'phone' => '01 45 67 89 12',
            'user_id' => $restaurateur2->id,
        ]);

        // Création des catégories pour le troisième restaurant
        $pizzas = Category::create([
            'name' => 'Pizzas',
            'restaurant_id' => $restaurant3->id,
        ]);

        $antipasti = Category::create([
            'name' => 'Antipasti',
            'restaurant_id' => $restaurant3->id,
        ]);

        $desserts3 = Category::create([
            'name' => 'Desserts',
            'restaurant_id' => $restaurant3->id,
        ]);

        // Création des plats pour les catégories du troisième restaurant
        // Antipasti
        $item13 = Item::create([
            'name' => 'Bruschetta',
            'description' => 'Pain grillé, tomates fraîches, ail et basilic',
            'price' => 750, // 7.50€
            'is_active' => true,
            'category_id' => $antipasti->id,
        ]);

        $item14 = Item::create([
            'name' => 'Burrata',
            'description' => 'Burrata crémeuse, tomates cerises et huile d\'olive',
            'price' => 1050, // 10.50€
            'is_active' => true,
            'category_id' => $antipasti->id,
        ]);

        // Pizzas
        $item15 = Item::create([
            'name' => 'Pizza Margherita',
            'description' => 'Sauce tomate, mozzarella fior di latte, basilic frais',
            'price' => 1150, // 11.50€
            'is_active' => true,
            'category_id' => $pizzas->id,
        ]);

        $item16 = Item::create([
            'name' => 'Pizza Napoli',
            'description' => 'Sauce tomate, mozzarella, anchois, câpres, olives',
            'price' => 1350, // 13.50€
            'is_active' => true,
            'category_id' => $pizzas->id,
        ]);

        // Desserts
        $item17 = Item::create([
            'name' => 'Tiramisu',
            'description' => 'Le classique tiramisu au café et mascarpone',
            'price' => 750, // 7.50€
            'is_active' => true,
            'category_id' => $desserts3->id,
        ]);

        $item18 = Item::create([
            'name' => 'Panna Cotta',
            'description' => 'Panna cotta à la vanille et coulis de fruits rouges',
            'price' => 650, // 6.50€
            'is_active' => true,
            'category_id' => $desserts3->id,
        ]);

        // Création des menus pour le troisième restaurant
        $menu5 = Menu::create([
            'name' => 'Menu Napolitain',
            'description' => 'Le meilleur de Naples en 3 plats',
            'price' => 2250, // 22.50€
            'is_active' => true,
            'restaurant_id' => $restaurant3->id,
        ]);

        // Ajout des plats au menu
        $menu5->items()->attach([$item13->id, $item15->id, $item17->id]);

        $menu6 = Menu::create([
            'name' => 'Menu Gourmand',
            'description' => 'Pour les gourmands',
            'price' => 2450, // 24.50€
            'is_active' => true,
            'restaurant_id' => $restaurant3->id,
        ]);

        // Ajout des plats au menu
        $menu6->items()->attach([$item14->id, $item16->id, $item18->id]);

        $this->command->info('Données de démonstration créées avec succès!');
    }
}
