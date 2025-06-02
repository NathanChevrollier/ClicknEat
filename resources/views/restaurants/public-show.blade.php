@extends('layouts.public')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Restaurants /</span> {{ $restaurant->name }}
    </h4>

    <div class="row">
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start align-items-sm-center gap-4">
                        <img src="{{ asset('assets/img/elements/restaurant-placeholder.jpg') }}" alt="{{ $restaurant->name }}" class="d-block rounded" height="100" width="100" style="object-fit: cover;">
                        <div class="d-flex flex-column">
                            <h3 class="mb-0">{{ $restaurant->name }}</h3>
                            <p class="text-muted mb-2">{{ $restaurant->description ?: 'Restaurant proposant une variété de plats délicieux.' }}</p>
                            <div class="mb-2">
                                <i class="bx bx-map text-primary me-1"></i>
                                <span>{{ $restaurant->address ?: 'Adresse non spécifiée' }}</span>
                            </div>
                            <div class="mb-2">
                                <i class="bx bx-phone text-primary me-1"></i>
                                <span>{{ $restaurant->phone ?: 'Téléphone non spécifié' }}</span>
                            </div>
                            <div>
                                @foreach($restaurant->categories as $category)
                                    <span class="badge bg-label-primary me-1">{{ $category->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ url('/login') }}" class="btn btn-primary rounded-pill">
                            <i class="bx bx-cart me-1"></i> Commander
                        </a>
                        <a href="{{ url('/login') }}" class="btn btn-outline-primary rounded-pill">
                            <i class="bx bx-calendar me-1"></i> Réserver une table
                        </a>
                        <a href="{{ url('/login') }}" class="btn btn-outline-secondary rounded-pill">
                            <i class="bx bx-star me-1"></i> Laisser un avis
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu par catégories -->
    @foreach($restaurant->categories as $category)
        @if($category->items->count() > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <h5 class="card-header">{{ $category->name }}</h5>
                        <div class="card-body">
                            <div class="row">
                                @foreach($category->items as $item)
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h5 class="card-title">{{ $item->name }}</h5>
                                                <p class="card-text">{{ $item->description ?: 'Délicieux plat à découvrir.' }}</p>
                                                <p class="fw-semibold">{{ number_format($item->price / 100, 2) }} €</p>
                                                <a href="{{ url('/login') }}" class="btn btn-sm btn-primary rounded-pill">
                                                    <i class="bx bx-cart me-1"></i> Commander
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <!-- Avis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Avis des clients</h5>
                    <a href="{{ url('/login') }}" class="btn btn-sm btn-primary rounded-pill">
                        <i class="bx bx-star me-1"></i> Laisser un avis
                    </a>
                </div>
                <div class="card-body">
                    @if($reviews->count() > 0)
                        @foreach($reviews as $review)
                            <div class="d-flex mb-3 pb-1 border-bottom">
                                <div>
                                    <h6 class="mb-0">{{ $review->user->name }}</h6>
                                    <div class="mb-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bx {{ $i <= $review->rating ? 'bxs-star text-warning' : 'bx-star text-muted' }}"></i>
                                        @endfor
                                        <span class="text-muted ms-1">{{ $review->created_at->format('d/m/Y') }}</span>
                                    </div>
                                    <p class="mb-0">{{ $review->comment }}</p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <i class="bx bx-message-alt-x fs-1 text-muted mb-2"></i>
                            <p class="mb-0">Aucun avis pour le moment.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
