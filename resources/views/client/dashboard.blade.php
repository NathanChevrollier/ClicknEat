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
                        <p class="mb-4">Vous êtes connecté en tant que <span class="fw-bold">Client</span></p>
                        <a href="{{ route('restaurants.index') }}" class="btn btn-sm btn-outline-primary">Découvrir les restaurants</a>
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

    <!-- Statistiques pour clients -->
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title m-0 me-2">Mes commandes</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex flex-column align-items-center gap-1">
                        <h2 class="mb-2">{{ \Illuminate\Support\Facades\Auth::user()->orders->count() }}</h2>
                        <span>Total</span>
                    </div>
                </div>
                <a href="{{ route('orders.index') }}" class="btn btn-primary d-block">Voir mes commandes</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title m-0 me-2">Commandes en cours</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex flex-column align-items-center gap-1">
                        @php
                            $activeOrders = \Illuminate\Support\Facades\Auth::user()->orders()
                                ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready'])
                                ->count();
                        @endphp
                        <h2 class="mb-2">{{ $activeOrders }}</h2>
                        <span>En cours</span>
                    </div>
                </div>
                <a href="{{ route('orders.index') }}" class="btn btn-primary d-block">Suivre mes commandes</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4 mb-4">
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
                        <span>Disponibles</span>
                    </div>
                </div>
                <a href="{{ route('restaurants.index') }}" class="btn btn-primary d-block">Explorer les restaurants</a>
            </div>
        </div>
    </div>
</div>
@endsection
