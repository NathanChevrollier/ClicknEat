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
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create/{restaurant}', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
    Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::put('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

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

        // Commandes des restaurants (uniquement pour les restaurateurs)
        Route::get('/restaurateur/orders', [OrderController::class, 'restaurantOrders'])->name('restaurateur.orders');
        Route::put('/orders/{order}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update-status');

        // Gestion des restaurants (uniquement pour les restaurateurs)
        Route::resource('restaurants', RestaurantController::class)->except(['index', 'show']);

        // Catégories
        Route::resource('restaurants.categories', CategoryController::class);

        // Plats
        Route::resource('categories.items', ItemController::class);
        Route::resource('items', ItemController::class);
        Route::resource('restaurants.items', ItemController::class);
        Route::get('/items/add', [ItemController::class, 'add'])->name('items.add');
        Route::post('/items/store-direct', [ItemController::class, 'storeDirect'])->name('items.store-direct');

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

            Route::get('/restaurants', [AdminController::class, 'restaurants'])->name('restaurants');
            Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
            Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
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
            Route::resource('reservations', App\Http\Controllers\Admin\ReservationController::class);

            // Gestion complète des avis admin (CRUD)
            Route::resource('reviews', App\Http\Controllers\Admin\ReviewController::class);
        });
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
