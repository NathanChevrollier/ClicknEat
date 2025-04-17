@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Restaurants /</span> Avis pour {{ $restaurant->name }}
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tous les avis</h5>
                    <div>
                        <a href="{{ route('restaurants.show', $restaurant->id) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bx bx-arrow-back me-1"></i> Retour au restaurant
                        </a>
                        @auth
                            <a href="{{ route('restaurants.reviews.create', $restaurant->id) }}" class="btn btn-primary btn-sm">
                                <i class="bx bx-plus me-1"></i> Ajouter un avis
                            </a>
                        @endauth
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <h5 class="mb-0">{{ number_format($restaurant->average_rating, 1) }}</h5>
                                    <div>
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= round($restaurant->average_rating))
                                                <i class="bx bxs-star text-warning"></i>
                                            @else
                                                <i class="bx bx-star text-warning"></i>
                                            @endif
                                        @endfor
                                    </div>
                                </div>
                                <div>
                                    <span class="text-muted">{{ $restaurant->reviews_count }} avis</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="GET" class="row g-3 mb-3 align-items-end">
                        <div class="col-md-3">
                            <label for="sort" class="form-label">Trier par</label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="date_desc" {{ request('sort') == 'date_desc' ? 'selected' : '' }}>Date la plus récente</option>
                                <option value="date_asc" {{ request('sort') == 'date_asc' ? 'selected' : '' }}>Date la plus ancienne</option>
                                <option value="note_desc" {{ request('sort') == 'note_desc' ? 'selected' : '' }}>Note la plus élevée</option>
                                <option value="note_asc" {{ request('sort') == 'note_asc' ? 'selected' : '' }}>Note la plus basse</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Trier</button>
                        </div>
                    </form>

                    @if($restaurant->reviews->isEmpty())
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-1"></i>
                            Aucun avis pour ce restaurant pour le moment.
                        </div>
                    @else
                        <div class="reviews-list">
                            @foreach($restaurant->reviews as $review)
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="mb-0">{{ $review->user->name }}</h5>
                                                <div class="text-muted small">{{ $review->created_at->format('d/m/Y') }}</div>
                                            </div>
                                            <div>
                                                <div class="mb-1">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        @if($i <= $review->rating)
                                                            <i class="bx bxs-star text-warning"></i>
                                                        @else
                                                            <i class="bx bx-star text-warning"></i>
                                                        @endif
                                                    @endfor
                                                </div>
                                                @if(auth()->check() && (auth()->id() === $review->user_id || auth()->user()->isAdmin()))
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('restaurants.reviews.edit', [$restaurant->id, $review->id]) }}" class="btn btn-outline-primary">
                                                            <i class="bx bx-edit-alt"></i>
                                                        </a>
                                                        <form action="{{ route('restaurants.reviews.destroy', [$restaurant->id, $review->id]) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet avis ?')">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <p>{{ $review->comment }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
