<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StaticPageController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ItemController as AdminItemController;
use App\Http\Controllers\Admin\MenuController as AdminMenuController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\Item;

// Routes publiques
Route::get('/', function () {
    return view('home');
})->name('welcome');

// Routes publiques pour les restaurants
Route::get('/restaurants', [RestaurantController::class, 'index'])->name('restaurants.index');
Route::get('/restaurants/{restaurant}', [RestaurantController::class, 'show'])->name('restaurants.show');

// Route publique pour afficher les restaurants par catégorie
Route::get('/categories/{category}', [CategoryController::class, 'publicRestaurants']);

Route::middleware(['auth', 'verified'])->group(function () {
    // Redirection selon le rôle
    Route::get('/dashboard', function () {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif (auth()->user()->isRestaurateur()) {
            return redirect()->route('restaurateur.dashboard');
        } else {
            return redirect()->route('client.dashboard');
        }
    })->name('dashboard');

    // Profil utilisateur
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Routes communes pour tous les utilisateurs authentifiés
    Route::get('/mes-avis', [ReviewController::class, 'userReviews'])->name('reviews.user');
    Route::resource('orders', OrderController::class);
    Route::get('/orders/reservation/{reservation}', [OrderController::class, 'createFromReservation'])->name('orders.createFromReservation');
    Route::patch('/orders/{order}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update.status');
    Route::put('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
    Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');

    // Routes pour les clients
    Route::middleware(['client'])->group(function () {
        Route::get('/client/dashboard', function () {
            return view('client.dashboard');
        })->name('client.dashboard');

        // Actions spécifiques aux clients
        Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

        // Réservations pour les clients
        Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
        Route::get('/reservations/{reservation}/edit', [ReservationController::class, 'edit'])->name('reservations.edit');
        Route::put('/reservations/{reservation}', [ReservationController::class, 'update'])->name('reservations.update');
        Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');
        Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    });

    // Routes pour les restaurateurs
    Route::middleware(['restaurateur'])->group(function () {
        Route::get('/restaurateur/dashboard', function () {
            return view('restaurateur.dashboard');
        })->name('restaurateur.dashboard');

        // Restaurants
        Route::resource('restaurants', RestaurantController::class);
        
        // Catégories
        Route::resource('restaurants.categories', CategoryController::class);
        
        // Plats
        Route::resource('restaurants.items', ItemController::class);
        
        // Commandes
        Route::get('/restaurants/{restaurant}/orders', [OrderController::class, 'restaurantOrders'])->name('restaurants.orders.index');
        Route::get('/restaurants/{restaurant}/orders/{order}', [OrderController::class, 'restaurantOrder'])->name('restaurants.orders.show');
        Route::patch('/restaurants/{restaurant}/orders/{order}/update-status', [OrderController::class, 'restaurantUpdateStatus'])->name('restaurants.orders.update.status');

        // Réservations pour restaurateurs
        Route::get('/restaurants/{restaurant}/reservations', [ReservationController::class, 'restaurantReservations'])->name('restaurant.reservations.index');
        Route::get('/restaurants/{restaurant}/reservations/{reservation}', [ReservationController::class, 'restaurantReservation'])->name('restaurant.reservations.show');
        Route::patch('/restaurants/{restaurant}/reservations/{reservation}/update-status', [ReservationController::class, 'restaurantUpdateStatus'])->name('restaurant.reservations.update.status');

        // Menus
        Route::resource('restaurants.menus', MenuController::class);

        // Tables
        Route::resource('restaurants.tables', TableController::class);
        Route::patch('/restaurants/{restaurant}/tables/{table}/toggle-availability', [TableController::class, 'toggleAvailability'])->name('restaurants.tables.toggle-availability');
        Route::get('/restaurants/{restaurant}/tables/availability', [TableController::class, 'availability'])->name('restaurants.tables.availability');

        // Réservations pour les restaurateurs
        Route::get('/restaurant/{restaurant}/reservations', [ReservationController::class, 'index'])->name('restaurant.reservations');
        Route::post('/reservations/{reservation}/confirm', [ReservationController::class, 'confirm'])->name('reservations.confirm');
        Route::post('/reservations/{reservation}/complete', [ReservationController::class, 'complete'])->name('reservations.complete');
        
        // Routes pour les menus des restaurateurs
        Route::get('/restaurants/{restaurant}/menus', [App\Http\Controllers\MenuController::class, 'index'])->name('restaurants.menus.index');
        Route::get('/restaurants/{restaurant}/menus/create', [App\Http\Controllers\MenuController::class, 'create'])->name('restaurants.menus.create');
        Route::post('/restaurants/{restaurant}/menus', [App\Http\Controllers\MenuController::class, 'store'])->name('restaurants.menus.store');
        Route::get('/restaurants/{restaurant}/menus/{menu}', [App\Http\Controllers\MenuController::class, 'show'])->name('restaurants.menus.show');
        Route::get('/restaurants/{restaurant}/menus/{menu}/edit', [App\Http\Controllers\MenuController::class, 'edit'])->name('restaurants.menus.edit');
        Route::put('/restaurants/{restaurant}/menus/{menu}', [App\Http\Controllers\MenuController::class, 'update'])->name('restaurants.menus.update');
        Route::delete('/restaurants/{restaurant}/menus/{menu}', [App\Http\Controllers\MenuController::class, 'destroy'])->name('restaurants.menus.destroy');
        
        // Routes pour les items (plats) des restaurateurs
        Route::get('/items', [App\Http\Controllers\ItemController::class, 'index'])->name('items.index');
        Route::get('/items/create', [App\Http\Controllers\ItemController::class, 'create'])->name('items.create');
        Route::post('/items', [App\Http\Controllers\ItemController::class, 'store'])->name('items.store');
        Route::get('/items/{item}', [App\Http\Controllers\ItemController::class, 'show'])->name('items.show');
        Route::get('/items/{item}/edit', [App\Http\Controllers\ItemController::class, 'edit'])->name('items.edit');
        Route::put('/items/{item}', [App\Http\Controllers\ItemController::class, 'update'])->name('items.update');
        Route::delete('/items/{item}', [App\Http\Controllers\ItemController::class, 'destroy'])->name('items.destroy');
        
        // Routes pour les commandes des restaurateurs
        Route::get('/orders/restaurant', [App\Http\Controllers\OrderController::class, 'restaurantOrders'])->name('restaurateur.orders');
        Route::get('/orders/restaurant/{restaurant}', [App\Http\Controllers\OrderController::class, 'restaurantOrders'])->name('restaurateur.restaurant.orders');
    });

    // Routes pour les administrateurs
    Route::prefix('admin')
        ->middleware(['admin'])
        ->name('admin.')
        ->group(function () {
            Route::get('/dashboard', function () {
                return view('admin.dashboard');
            })->name('dashboard');
            
            // Gestion complète des utilisateurs admin (CRUD)
            Route::resource('users', App\Http\Controllers\Admin\UserController::class);

            Route::get('/restaurants', [AdminController::class, 'restaurants'])->name('restaurants.index');
            Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
            // Route orders est définie comme resource plus bas
            // Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
            Route::get('/reservations', [AdminController::class, 'reservations'])->name('reservations');
            Route::get('/reviews', [AdminController::class, 'reviews'])->name('reviews');

            // Catégories (admin)
            Route::resource('categories', AdminCategoryController::class);

            // Plats (admin)
            Route::resource('items', AdminItemController::class);

            // Menus (admin)
            Route::resource('menus', AdminMenuController::class);

            // Gestion complète des commandes admin (CRUD)
            Route::resource('orders', App\Http\Controllers\Admin\OrderController::class);

            // Gestion complète des restaurants admin (CRUD)
            Route::resource('restaurants', App\Http\Controllers\Admin\RestaurantController::class);

            // Gestion complète des réservations admin (CRUD)
            Route::get('reservations/get-tables', [App\Http\Controllers\Admin\ReservationController::class, 'getTables'])->name('reservations.get-tables');
            Route::post('reservations/get-tables', [App\Http\Controllers\Admin\ReservationController::class, 'getTables'])->name('reservations.get-tables'); // Supporte aussi POST
            Route::get('reservations/get-all-tables', [App\Http\Controllers\Admin\ReservationController::class, 'getAllRestaurantTables'])->name('reservations.get-all-tables');
            Route::get('test-tables', [App\Http\Controllers\Admin\ReservationController::class, 'getTablesTest'])->name('reservations.test-tables');
            Route::resource('reservations', App\Http\Controllers\Admin\ReservationController::class);
            Route::post('reservations/{reservation}/confirm', [App\Http\Controllers\Admin\ReservationController::class, 'confirm'])->name('reservations.confirm');
            Route::post('reservations/{reservation}/complete', [App\Http\Controllers\Admin\ReservationController::class, 'complete'])->name('reservations.complete');
            Route::post('reservations/{reservation}/cancel', [App\Http\Controllers\Admin\ReservationController::class, 'cancel'])->name('reservations.cancel');

            // Gestion complète des avis admin (CRUD)
            Route::resource('reviews', App\Http\Controllers\Admin\ReviewController::class);

            // Route spécifique pour annuler une commande
            Route::patch('/orders/{order}/cancel', [App\Http\Controllers\Admin\OrderController::class, 'cancel'])->name('orders.cancel');
        });
});

// Routes pour les reviews
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/restaurants/{restaurant}/reviews', [App\Http\Controllers\ReviewController::class, 'index'])->name('restaurants.reviews.index');
    Route::get('/restaurants/{restaurant}/reviews/create', [App\Http\Controllers\ReviewController::class, 'create'])->name('restaurants.reviews.create');
    Route::post('/restaurants/{restaurant}/reviews', [App\Http\Controllers\ReviewController::class, 'store'])->name('restaurants.reviews.store');
    Route::get('/restaurants/{restaurant}/reviews/{review}', [App\Http\Controllers\ReviewController::class, 'show'])->name('restaurants.reviews.show');
    Route::get('/restaurants/{restaurant}/reviews/{review}/edit', [App\Http\Controllers\ReviewController::class, 'edit'])->name('restaurants.reviews.edit');
    Route::put('/restaurants/{restaurant}/reviews/{review}', [App\Http\Controllers\ReviewController::class, 'update'])->name('restaurants.reviews.update');
    Route::delete('/restaurants/{restaurant}/reviews/{review}', [App\Http\Controllers\ReviewController::class, 'destroy'])->name('restaurants.reviews.destroy');
    Route::post('/reviews/{review}/approve', [App\Http\Controllers\ReviewController::class, 'approve'])->name('reviews.approve');
});

// Routes pour les réservations
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/reservations', [App\Http\Controllers\ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/create/{restaurantId}', [App\Http\Controllers\ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [App\Http\Controllers\ReservationController::class, 'store'])->name('reservations.store');
    Route::get('/reservations/{reservation}', [App\Http\Controllers\ReservationController::class, 'show'])->name('reservations.show');
    Route::get('/reservations/{reservation}/edit', [App\Http\Controllers\ReservationController::class, 'edit'])->name('reservations.edit');
    Route::put('/reservations/{reservation}', [App\Http\Controllers\ReservationController::class, 'update'])->name('reservations.update');
    Route::delete('/reservations/{reservation}', [App\Http\Controllers\ReservationController::class, 'destroy'])->name('reservations.destroy');
    Route::post('/reservations/{reservation}/cancel', [App\Http\Controllers\ReservationController::class, 'cancel'])->name('reservations.cancel');
    Route::post('/reservations/{reservation}/confirm', [App\Http\Controllers\ReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::post('/reservations/{reservation}/complete', [App\Http\Controllers\ReservationController::class, 'complete'])->name('reservations.complete');
    Route::post('/reservations/{reservation}/add-order', [App\Http\Controllers\ReservationController::class, 'addOrder'])->name('reservations.add-order');
    Route::post('/tables/available', [App\Http\Controllers\TableController::class, 'getAvailableTables'])->name('tables.available');
    Route::get('/restaurant/{restaurantId}/reservations', [App\Http\Controllers\ReservationController::class, 'restaurantReservations'])->name('restaurant.reservations');
    Route::post('/restaurants/{restaurant}/check-availability', [App\Http\Controllers\RestaurantController::class, 'checkAvailability'])->name('restaurants.check-availability');
});

// Routes pour les tables
Route::middleware(['auth'])->group(function () {
    Route::get('/restaurants/{restaurant}/tables', [TableController::class, 'index'])->name('restaurants.tables.index');
    Route::get('/restaurants/{restaurant}/tables/create', [TableController::class, 'create'])->name('restaurants.tables.create');
    Route::post('/restaurants/{restaurant}/tables', [TableController::class, 'store'])->name('restaurants.tables.store');
    Route::get('/restaurants/{restaurant}/tables/{table}', [TableController::class, 'show'])->name('restaurants.tables.show');
    Route::get('/restaurants/{restaurant}/tables/{table}/edit', [TableController::class, 'edit'])->name('restaurants.tables.edit');
    Route::put('/restaurants/{restaurant}/tables/{table}', [TableController::class, 'update'])->name('restaurants.tables.update');
    Route::delete('/restaurants/{restaurant}/tables/{table}', [TableController::class, 'destroy'])->name('restaurants.tables.destroy');
    Route::put('/restaurants/{restaurant}/tables/{table}/toggle', [TableController::class, 'toggleAvailability'])->name('restaurants.tables.toggle');
    Route::get('/restaurants/{restaurant}/tables/availability', [TableController::class, 'availability'])->name('restaurants.tables.availability');
    Route::post('/tables/available', [TableController::class, 'getAvailableTables'])->name('tables.available');
});

// Routes pour les avis (accessibles à tous les utilisateurs authentifiés)
Route::middleware(['auth'])->group(function () {
    Route::get('/restaurants/{restaurant}/reviews', [ReviewController::class, 'index'])->name('restaurants.reviews.index');
    Route::get('/restaurants/{restaurant}/reviews/create', [ReviewController::class, 'create'])->name('restaurants.reviews.create');
    Route::post('/restaurants/{restaurant}/reviews', [ReviewController::class, 'store'])->name('restaurants.reviews.store');
    Route::get('/restaurants/{restaurant}/reviews/{review}', [ReviewController::class, 'show'])->name('restaurants.reviews.show');
    Route::get('/restaurants/{restaurant}/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('restaurants.reviews.edit');
    Route::put('/restaurants/{restaurant}/reviews/{review}', [ReviewController::class, 'update'])->name('restaurants.reviews.update');
    Route::delete('/restaurants/{restaurant}/reviews/{review}', [ReviewController::class, 'destroy'])->name('restaurants.reviews.destroy');
});

// Route pour approuver/rejeter les avis (administrateurs uniquement)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('/reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
});

// Routes pour les notifications (accessibles à tous les utilisateurs authentifiés)
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications/destroy-all-read', [NotificationController::class, 'destroyAllRead'])->name('notifications.destroy-all-read');
});

// Pages statiques
Route::get('/about', [StaticPageController::class, 'about'])->name('about');
Route::get('/contact', [StaticPageController::class, 'contact'])->name('contact');
Route::post('/contact', [StaticPageController::class, 'sendContact'])->name('contact.send');
Route::get('/mentions-legales', [StaticPageController::class, 'legalNotice'])->name('legal.notice');
Route::get('/conditions-generales-utilisation', [StaticPageController::class, 'termsOfService'])->name('terms.of.service');
Route::get('/politique-confidentialite', [StaticPageController::class, 'privacyPolicy'])->name('privacy.policy');

Route::middleware(['auth'])->group(function () {
    Route::get('/restaurants', [RestaurantController::class, 'index'])->name('restaurants.index');
    Route::get('/restaurants/create', [RestaurantController::class, 'create'])->name('restaurants.create');
    Route::post('/restaurants', [RestaurantController::class, 'store'])->name('restaurants.store');
    Route::get('/restaurants/{restaurant}', [RestaurantController::class, 'show'])->name('restaurants.show');
    Route::get('/restaurants/{restaurant}/edit', [RestaurantController::class, 'edit'])->name('restaurants.edit');
    Route::put('/restaurants/{restaurant}', [RestaurantController::class, 'update'])->name('restaurants.update');
    Route::delete('/restaurants/{restaurant}', [RestaurantController::class, 'destroy'])->name('restaurants.destroy');
    Route::get('/restaurants/{restaurant}/categories-items', [App\Http\Controllers\RestaurantController::class, 'categoriesItems'])->name('restaurants.categories_items');
});

require __DIR__.'/auth.php';
