@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Carte de bienvenue -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="d-flex align-items-end row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Bienvenue {{ \Illuminate\Support\Facades\Auth::user()->name }} ! 🎉</h5>
                        <p class="mb-4">Vous êtes connecté en tant qu'<span class="fw-bold">Administrateur</span></p>
                        <p>Depuis ce tableau de bord, vous pouvez gérer tous les aspects de l'application Click'n Eat.</p>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}" height="140" alt="View Badge User" data-app-dark-img="illustrations/man-with-laptop-dark.png" data-app-light-img="illustrations/man-with-laptop-light.png">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques pour administrateurs -->
    <div class="row">
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Utilisateurs</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex flex-column align-items-center gap-1">
                            @php
                                $userCount = App\Models\User::count();
                            @endphp
                            <h2 class="mb-2">{{ $userCount }}</h2>
                            <span>Total</span>
                        </div>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-primary d-block">Gérer les utilisateurs</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Restaurants</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex flex-column align-items-center gap-1">
                            @php
                                $restaurantCount = App\Models\Restaurant::count();
                            @endphp
                            <h2 class="mb-2">{{ $restaurantCount }}</h2>
                            <span>Total</span>
                        </div>
                    </div>
                    <a href="{{ route('admin.restaurants.index') }}" class="btn btn-primary d-block">Gérer les restaurants</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Catégories</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex flex-column align-items-center gap-1">
                            @php
                                $categoryCount = App\Models\Category::count();
                            @endphp
                            <h2 class="mb-2">{{ $categoryCount }}</h2>
                            <span>Total</span>
                        </div>
                    </div>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-primary d-block">Gérer les catégories</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Plats</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex flex-column align-items-center gap-1">
                            @php
                                $itemCount = App\Models\Item::count();
                            @endphp
                            <h2 class="mb-2">{{ $itemCount }}</h2>
                            <span>Total</span>
                        </div>
                    </div>
                    <a href="{{ route('admin.items.index') }}" class="btn btn-primary d-block">Gérer les plats</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques des menus -->
    <div class="row">
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Menus</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex flex-column align-items-center gap-1">
                            @php
                                $menuCount = App\Models\Menu::count();
                            @endphp
                            <h2 class="mb-2">{{ $menuCount }}</h2>
                            <span>Total</span>
                        </div>
                    </div>
                    <a href="{{ route('admin.menus.index') }}" class="btn btn-primary d-block">Gérer les menus</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu d'administration -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Gestion de l'application</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card bg-primary text-white text-center p-3">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Utilisateurs</h5>
                                    <p class="card-text">Gérer les comptes utilisateurs, restaurateurs et administrateurs</p>
                                    <a href="{{ route('admin.users.index') }}" class="btn btn-light">Accéder</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 mb-3">
                            <div class="card bg-success text-white p-3">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Restaurants et leurs contenus</h5>
                                    <p class="card-text">Gérer les restaurants et tous leurs éléments associés (catégories, plats, menus)</p>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <a href="{{ route('admin.restaurants.index') }}" class="btn btn-light d-block mb-2">Liste des restaurants</a>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="dropdown">
                                                <button class="btn btn-light dropdown-toggle w-100" type="button" id="restaurantDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Sélectionner un restaurant
                                                </button>
                                                <ul class="dropdown-menu w-100" aria-labelledby="restaurantDropdown">
                                                    @php
                                                        $restaurants = \App\Models\Restaurant::orderBy('name')->get();
                                                    @endphp
                                                    @foreach($restaurants as $restaurant)
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('restaurants.show', $restaurant->id) }}">
                                                                {{ $restaurant->name }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card bg-info text-white text-center p-3">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Catégories</h5>
                                    <p class="card-text">Gérer les catégories de plats par restaurant</p>
                                    <div class="d-flex flex-column gap-2">
                                        <a href="{{ route('admin.categories.index') }}" class="btn btn-light">Toutes les catégories</a>
                                        <div class="dropdown mt-2">
                                            <button class="btn btn-outline-light dropdown-toggle w-100" type="button" id="categoryRestaurantDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                Par restaurant
                                            </button>
                                            <ul class="dropdown-menu w-100" aria-labelledby="categoryRestaurantDropdown">
                                                @foreach($restaurants as $restaurant)
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.categories.index', ['restaurant_id' => $restaurant->id]) }}">
                                                            {{ $restaurant->name }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card bg-warning text-white text-center p-3">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Plats</h5>
                                    <p class="card-text">Gérer les plats par restaurant ou catégorie</p>
                                    <div class="d-flex flex-column gap-2">
                                        <a href="{{ route('admin.items.index') }}" class="btn btn-light">Tous les plats</a>
                                        <div class="dropdown mt-2">
                                            <button class="btn btn-outline-light dropdown-toggle w-100" type="button" id="itemRestaurantDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                Par restaurant
                                            </button>
                                            <ul class="dropdown-menu w-100" aria-labelledby="itemRestaurantDropdown">
                                                @foreach($restaurants as $restaurant)
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.items.index', ['restaurant_id' => $restaurant->id]) }}">
                                                            {{ $restaurant->name }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card bg-secondary text-white text-center p-3">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Menus</h5>
                                    <p class="card-text">Gérer les menus par restaurant</p>
                                    <div class="d-flex flex-column gap-2">
                                        <a href="{{ route('admin.menus.index') }}" class="btn btn-light">Tous les menus</a>
                                        <div class="dropdown mt-2">
                                            <button class="btn btn-outline-light dropdown-toggle w-100" type="button" id="menuRestaurantDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                Par restaurant
                                            </button>
                                            <ul class="dropdown-menu w-100" aria-labelledby="menuRestaurantDropdown">
                                                @foreach($restaurants as $restaurant)
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.menus.index', ['restaurant_id' => $restaurant->id]) }}">
                                                            {{ $restaurant->name }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card bg-danger text-white text-center p-3">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Commandes</h5>
                                    <p class="card-text">Suivre et gérer toutes les commandes de la plateforme</p>
                                    <a href="{{ route('admin.orders.index') }}" class="btn btn-light">Accéder</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card bg-dark text-white text-center p-3">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Paramètres</h5>
                                    <p class="card-text">Configurer les paramètres de l'application</p>
                                    <a href="#" class="btn btn-light">Accéder</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
