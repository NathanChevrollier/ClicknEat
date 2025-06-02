@extends('layouts.public')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Catégories /</span> {{ $categoryName }}
    </h4>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Restaurants proposant des {{ strtolower($categoryName) }}</h5>
                    <a href="{{ url('/restaurants') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bx bx-arrow-back me-1"></i> Tous les restaurants
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @forelse($restaurants as $restaurant)
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <img src="{{ asset('assets/img/elements/restaurant-placeholder.jpg') }}" class="card-img-top" alt="{{ $restaurant->name }}" style="height: 180px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-semibold">{{ $restaurant->name }}</h5>
                        <p class="card-text text-truncate mb-3">{{ $restaurant->description ?: 'Restaurant proposant une variété de plats délicieux.' }}</p>
                        <div class="text-muted small mb-3">
                            <i class="bx bx-map me-1"></i>{{ $restaurant->address ?: 'Adresse non spécifiée' }}
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                @foreach($restaurant->categories->take(2) as $category)
                                    <span class="badge bg-label-primary me-1">{{ $category->name }}</span>
                                @endforeach
                            </div>
                        </div>
                        <a href="{{ url('/restaurants/'.$restaurant->id) }}" class="btn btn-primary mt-auto">Voir le restaurant</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center">
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-1"></i> Aucun restaurant disponible dans cette catégorie pour le moment.
                </div>
            </div>
        @endforelse
    </div>

    <div class="row mt-4">
        <div class="col-12 d-flex justify-content-center">
            {{ $restaurants->links() }}
        </div>
    </div>
</div>
@endsection
