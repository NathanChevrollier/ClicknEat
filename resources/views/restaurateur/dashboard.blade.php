@extends('layouts.main')

@section('main')
<div class="row">
    <!-- Carte de bienvenue -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="d-flex align-items-end row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Bienvenue {{ \Illuminate\Support\Facades\Auth::user()->name }} ! 🎉</h5>
                        <p class="mb-4">Vous êtes connecté en tant que <span class="fw-bold">Restaurateur</span></p>
                        <div class="d-flex gap-2">
                            <a href="{{ route('restaurants.index') }}" class="btn btn-sm btn-outline-primary">Gérer mes restaurants</a>
                            @if(\Illuminate\Support\Facades\Auth::user()->restaurants->count() > 0)
                                <a href="{{ route('restaurants.menus.index', \Illuminate\Support\Facades\Auth::user()->restaurants->first()->id) }}" class="btn btn-sm btn-outline-primary">Gérer mes menus</a>
                                <a href="{{ route('items.index') }}" class="btn btn-sm btn-outline-primary">Gérer mes plats</a>
                            @endif
                        </div>
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

    <!-- Statistiques pour restaurateurs -->
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title m-0 me-2">Mes restaurants</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex flex-column align-items-center gap-1">
                        <h2 class="mb-2">{{ \Illuminate\Support\Facades\Auth::user()->restaurants->count() }}</h2>
                        <span>Total</span>
                    </div>
                </div>
                <a href="{{ route('restaurants.create') }}" class="btn btn-primary d-block">Ajouter un restaurant</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title m-0 me-2">Commandes récentes</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex flex-column align-items-center gap-1">
                        @php
                            $restaurantIds = \Illuminate\Support\Facades\Auth::user()->restaurants->pluck('id');
                            $pendingOrders = App\Models\Order::whereIn('restaurant_id', $restaurantIds)
                                ->where('status', 'pending')
                                ->count();
                        @endphp
                        <h2 class="mb-2">{{ $pendingOrders }}</h2>
                        <span>En attente</span>
                    </div>
                </div>
                <a href="{{ route('restaurateur.orders') }}" class="btn btn-primary d-block">Voir toutes les commandes</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title m-0 me-2">Plats</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex flex-column align-items-center gap-1">
                        @php
                            $categoryIds = App\Models\Category::whereIn('restaurant_id', $restaurantIds)->pluck('id');
                            $itemCount = App\Models\Item::whereIn('category_id', $categoryIds)->count();
                        @endphp
                        <h2 class="mb-2">{{ $itemCount }}</h2>
                        <span>Total</span>
                    </div>
                </div>
                <a href="{{ route('items.index') }}" class="btn btn-primary d-block">Gérer mes plats</a>
            </div>
        </div>
    </div>

    <!-- Liste des restaurants -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Mes restaurants</h5>
                <a href="{{ route('restaurants.create') }}" class="btn btn-primary btn-sm">Ajouter un restaurant</a>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Adresse</th>
                            <th>Téléphone</th>
                            <th>Catégories</th>
                            <th>Plats</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach(\Illuminate\Support\Facades\Auth::user()->restaurants as $restaurant)
                        <tr>
                            <td><strong>{{ $restaurant->name }}</strong></td>
                            <td>{{ $restaurant->address }}</td>
                            <td>{{ $restaurant->phone }}</td>
                            <td>
                                @php
                                    $categoryCount = App\Models\Category::where('restaurant_id', $restaurant->id)->count();
                                @endphp
                                {{ $categoryCount }}
                            </td>
                            <td>
                                @php
                                    $categoryIds = App\Models\Category::where('restaurant_id', $restaurant->id)->pluck('id');
                                    $itemCount = App\Models\Item::whereIn('category_id', $categoryIds)->count();
                                @endphp
                                {{ $itemCount }}
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('restaurants.edit', $restaurant) }}" class="btn btn-sm btn-warning" title="Modifier">
                                        <i class="bx bx-edit-alt"></i>
                                    </a>
                                    <a href="{{ route('restaurants.show', $restaurant) }}" class="btn btn-sm btn-info" title="Voir">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    <form action="{{ route('restaurants.destroy', $restaurant) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce restaurant?')" title="Supprimer">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
