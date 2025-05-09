@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Administration /</span> Gestion des avis
    </h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Liste des avis</h5>
            <div>
                <a href="{{ route('admin.reviews.create') }}" class="btn btn-primary me-2">
                    <i class="bx bx-plus me-1"></i> Ajouter un avis
                </a>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary me-2">
                    <i class="bx bx-arrow-back me-1"></i> Retour au tableau de bord
                </a>
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

            <!-- Filtres -->
            <div class="mb-4">
                <form action="{{ route('admin.reviews.index') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="restaurant_id" class="form-label">Restaurant</label>
                        <select class="form-select" id="restaurant_id" name="restaurant_id">
                            <option value="">Tous les restaurants</option>
                            @foreach(\App\Models\Restaurant::orderBy('name')->get() as $rest)
                                <option value="{{ $rest->id }}" {{ request('restaurant_id') == $rest->id ? 'selected' : '' }}>{{ $rest->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="rating" class="form-label">Note</label>
                        <select class="form-select" id="rating" name="rating">
                            <option value="">Toutes les notes</option>
                            <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 étoile</option>
                            <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 étoiles</option>
                            <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 étoiles</option>
                            <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 étoiles</option>
                            <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 étoiles</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bx bx-filter-alt me-1"></i> Filtrer
                        </button>
                        <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-reset me-1"></i> Réinitialiser
                        </a>
                    </div>
                </form>
            </div>

            @if($reviews->isEmpty())
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-1"></i>
                    Aucun avis trouvé.
                </div>
            @else
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Restaurant</th>
                                <th>Client</th>
                                <th>Note</th>
                                <th>Commentaire</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach($reviews as $review)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.restaurants.show', $review->restaurant_id) }}">
                                            {{ $review->restaurant->name }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.users', ['user_id' => $review->user_id]) }}">
                                            {{ $review->user->name }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="text-warning">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $review->rating)
                                                    <i class="bx bxs-star"></i>
                                                @else
                                                    <i class="bx bx-star"></i>
                                                @endif
                                            @endfor
                                        </div>
                                    </td>
                                    <td>{{ Str::limit($review->comment, 50) }}</td>
                                    <td>{{ $review->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('restaurants.show', $review->restaurant_id) }}#review-{{ $review->id }}">
                                                    <i class="bx bx-show me-1"></i> Voir
                                                </a>
                                                <form action="{{ route('reviews.destroy', $review->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet avis ?')">
                                                        <i class="bx bx-trash me-1"></i> Supprimer
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    {{ $reviews->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
