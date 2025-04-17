<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;
use App\Models\Restaurant;

// Récupérer le restaurant de test
$restaurant = Restaurant::where('name', 'Restaurant Test')->first();

if ($restaurant) {
    // Supprimer toutes les catégories associées à ce restaurant
    $categories = Category::where('restaurant_id', $restaurant->id)->get();
    
    foreach ($categories as $category) {
        echo "Suppression de la catégorie '{$category->name}'...\n";
        $category->delete();
    }
    
    echo "Toutes les catégories du restaurant '{$restaurant->name}' ont été supprimées.\n";
} else {
    echo "Restaurant 'Restaurant Test' non trouvé.\n";
}
