<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\Item;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Constructeur du contrôleur
     */
    public function __construct()
    {
        // La vérification des droits admin se fait dans checkAdmin()
    }
    
    /**
     * Vérifie si l'utilisateur est admin
     */
    private function checkAdmin()
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Accès réservé aux administrateurs.');
        }
    }

    /**
     * Affiche le tableau de bord admin
     */
    public function dashboard()
    {
        $this->checkAdmin();
        return view('admin.dashboard');
    }

    /**
     * Liste des utilisateurs
     */
    public function users()
    {
        $this->checkAdmin();
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Formulaire de création d'utilisateur
     */
    public function createUser()
    {
        $this->checkAdmin();
        return view('admin.users.create');
    }

    /**
     * Enregistre un nouvel utilisateur
     */
    public function storeUser(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:client,restaurateur,admin',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur créé avec succès');
    }

    /**
     * Formulaire d'édition d'utilisateur
     */
    public function editUser(User $user)
    {
        $this->checkAdmin();
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Met à jour un utilisateur
     */
    public function updateUser(Request $request, User $user)
    {
        $this->checkAdmin();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:client,restaurateur,admin',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        
        // Mise à jour du mot de passe si fourni
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);
            $user->password = Hash::make($request->password);
        }
        
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur mis à jour avec succès');
    }

    /**
     * Supprime un utilisateur
     */
    public function destroyUser(User $user)
    {
        $this->checkAdmin();
        // Protection contre la suppression de son propre compte
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé avec succès');
    }

    /**
     * Liste des restaurants
     */
    public function restaurants()
    {
        $this->checkAdmin();
        $restaurants = Restaurant::with('user')->paginate(10);
        return view('admin.restaurants.index', compact('restaurants'));
    }

    /**
     * Détails d'un restaurant
     */
    public function showRestaurant($id)
    {
        $this->checkAdmin();
        $restaurant = Restaurant::with(['user', 'categories', 'menus', 'menus.items'])->findOrFail($id);
        
        // Récupérer les plats du restaurant
        $items = Item::whereHas('category', function($query) use ($id) {
            $query->where('restaurant_id', $id);
        })->with('category')->get();
        
        $itemsCount = $items->count();
        
        return view('admin.restaurants.show', compact('restaurant', 'items', 'itemsCount'));
    }

    /**
     * Liste des catégories
     */
    public function categories(Request $request)
    {
        $this->checkAdmin();
        
        // Filtre par restaurant si demandé
        $restaurant = null;
        $categories = null;
        
        if ($request->has('restaurant_id')) {
            $restaurantId = $request->restaurant_id;
            $restaurant = Restaurant::findOrFail($restaurantId);
            $categories = Category::where('restaurant_id', $restaurantId)
                ->with(['restaurant', 'items'])
                ->get();
        } else {
            $categories = Category::with(['restaurant', 'items'])->get();
        }
        
        return view('admin.categories.index', compact('categories', 'restaurant'));
    }

    /**
     * Liste des plats
     */
    public function items(Request $request)
    {
        $this->checkAdmin();
        
        // Filtre par restaurant ou catégorie si demandé
        $restaurant = null;
        $category = null;
        $query = Item::with(['category', 'category.restaurant']);
        
        if ($request->has('restaurant_id')) {
            $restaurantId = $request->restaurant_id;
            $restaurant = Restaurant::findOrFail($restaurantId);
            $query->whereHas('category', function($q) use ($restaurantId) {
                $q->where('restaurant_id', $restaurantId);
            });
        }
        
        if ($request->has('category_id')) {
            $categoryId = $request->category_id;
            $category = Category::findOrFail($categoryId);
            $query->where('category_id', $categoryId);
        }
        
        $items = $query->get();
        
        return view('admin.items.index', compact('items', 'restaurant', 'category'));
    }

    /**
     * Liste des menus
     */
    public function menus(Request $request)
    {
        $this->checkAdmin();
        
        // Filtre par restaurant si demandé
        $restaurant = null;
        $query = Menu::with(['restaurant', 'items']);
        
        if ($request->has('restaurant_id')) {
            $restaurantId = $request->restaurant_id;
            $restaurant = Restaurant::findOrFail($restaurantId);
            $query->where('restaurant_id', $restaurantId);
        }
        
        $menus = $query->get();
        
        return view('admin.menus.index', compact('menus', 'restaurant'));
    }

    /**
     * Liste des commandes
     */
    public function orders(Request $request)
    {
        $this->checkAdmin();
        
        // Filtre par restaurant ou utilisateur si demandé
        $restaurant = null;
        $user = null;
        $query = Order::with(['user', 'restaurant', 'items']);
        
        if ($request->has('restaurant_id')) {
            $restaurantId = $request->restaurant_id;
            $restaurant = Restaurant::findOrFail($restaurantId);
            $query->where('restaurant_id', $restaurantId);
        }
        
        if ($request->has('user_id')) {
            $userId = $request->user_id;
            $user = User::findOrFail($userId);
            $query->where('user_id', $userId);
        }
        
        // Filtre par statut si demandé
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->get();
        
        return view('admin.orders.index', compact('orders', 'restaurant', 'user'));
    }

    /**
     * Liste des réservations
     */
    public function reservations(Request $request)
    {
        $this->checkAdmin();
        
        // Filtre par restaurant ou utilisateur si demandé
        $restaurant = null;
        $user = null;
        $query = Reservation::with(['user', 'restaurant', 'table', 'order']);
        
        if ($request->has('restaurant_id')) {
            $restaurantId = $request->restaurant_id;
            $restaurant = Restaurant::findOrFail($restaurantId);
            $query->where('restaurant_id', $restaurantId);
        }
        
        if ($request->has('user_id')) {
            $userId = $request->user_id;
            $user = User::findOrFail($userId);
            $query->where('user_id', $userId);
        }
        
        // Filtre par statut si demandé
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtre par date si demandé
        if ($request->has('date')) {
            $date = $request->date;
            $query->whereDate('reservation_date', $date);
        }
        
        $reservations = $query->orderBy('reservation_date', 'desc')->paginate(10);
        
        return view('admin.reservations.index', compact('reservations', 'restaurant', 'user'));
    }

    /**
     * Liste des avis
     */
    public function reviews(Request $request)
    {
        $this->checkAdmin();
        
        // Filtre par restaurant ou utilisateur si demandé
        $restaurant = null;
        $user = null;
        $query = Review::with(['user', 'restaurant']);
        
        if ($request->has('restaurant_id')) {
            $restaurantId = $request->restaurant_id;
            $restaurant = Restaurant::findOrFail($restaurantId);
            $query->where('restaurant_id', $restaurantId);
        }
        
        if ($request->has('user_id')) {
            $userId = $request->user_id;
            $user = User::findOrFail($userId);
            $query->where('user_id', $userId);
        }
        
        // Filtre par note si demandé
        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }
        
        $reviews = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('admin.reviews.index', compact('reviews', 'restaurant', 'user'));
    }
}
