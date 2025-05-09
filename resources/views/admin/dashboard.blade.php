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

    <!-- Gestion de l'application -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Gestion de l'application</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card bg-primary text-white text-center p-3 h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Utilisateurs</h5>
                                    <p class="card-text">Gérer les comptes utilisateurs, restaurateurs et administrateurs</p>
                                    <div class="text-center mt-3">
                                        <a href="{{ route('admin.users.index') }}" class="btn btn-light">Accéder</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card bg-warning text-white text-center p-3 h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Restaurants et leurs contenus</h5>
                                    <p class="card-text">Gérer les restaurants et tous leurs éléments associés (catégories, plats, menus)</p>
                                    <div class="text-center mt-3">
                                        <a href="{{ route('admin.restaurants.index') }}" class="btn btn-light mb-2">Liste des restaurants</a>
                                        <div class="dropdown mt-2">
                                            <button class="btn btn-outline-light dropdown-toggle" type="button" id="restaurantDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                Sélectionner un restaurant
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="restaurantDropdown">
                                                @php
                                                    $restaurants = \App\Models\Restaurant::orderBy('name')->get();
                                                @endphp
                                                @foreach($restaurants as $restaurant)
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.restaurants.show', $restaurant->id) }}">
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
                            <div class="card bg-info text-white text-center p-3 h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Catégories</h5>
                                    <p class="card-text">Gérer les catégories de plats par restaurant</p>
                                    <div class="text-center mt-3">
                                        <a href="{{ route('admin.categories.index') }}" class="btn btn-light mb-2">Toutes les catégories</a>
                                        <div class="dropdown mt-2">
                                            <button class="btn btn-outline-light dropdown-toggle" type="button" id="categoryRestaurantDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                Par restaurant
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="categoryRestaurantDropdown">
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
                            <div class="card bg-success text-white text-center p-3 h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Plats</h5>
                                    <p class="card-text">Gérer les plats par restaurant ou catégorie</p>
                                    <div class="text-center mt-3">
                                        <a href="{{ route('admin.items.index') }}" class="btn btn-light mb-2">Tous les plats</a>
                                        <div class="dropdown mt-2">
                                            <button class="btn btn-outline-light dropdown-toggle" type="button" id="itemRestaurantDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                Par restaurant
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="itemRestaurantDropdown">
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
                            <div class="card bg-secondary text-white text-center p-3 h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Menus</h5>
                                    <p class="card-text">Gérer les menus par restaurant</p>
                                    <div class="text-center mt-3">
                                        <a href="{{ route('admin.menus.index') }}" class="btn btn-light mb-2">Tous les menus</a>
                                        <div class="dropdown mt-2">
                                            <button class="btn btn-outline-light dropdown-toggle" type="button" id="menuRestaurantDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                Par restaurant
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="menuRestaurantDropdown">
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
                            <div class="card bg-danger text-white text-center p-3 h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Commandes</h5>
                                    <p class="card-text">Suivre et gérer toutes les commandes de la plateforme</p>
                                    <div class="text-center mt-3">
                                        <a href="{{ route('admin.orders.index') }}" class="btn btn-light">Accéder</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card bg-dark text-white text-center p-3 h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Réservations</h5>
                                    <p class="card-text">Gérer les réservations des clients</p>
                                    <div class="text-center mt-3">
                                        <a href="{{ route('admin.reservations.index') }}" class="btn btn-light">Accéder</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card bg-primary text-white text-center p-3 h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Avis</h5>
                                    <p class="card-text">Gérer les avis des clients sur les restaurants</p>
                                    <div class="text-center mt-3">
                                        <a href="{{ route('admin.reviews.index') }}" class="btn btn-light">Accéder</a>
                                    </div>
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
