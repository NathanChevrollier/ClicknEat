@extends('layouts.main')
@php use Illuminate\Support\Str; @endphp

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

            <!-- Filtres -->
            <div class="mb-4">
                <form action="{{ route('admin.reviews.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Recherche</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="search" name="search" placeholder="Nom client, restaurant, commentaire..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search"></i>
                            </button>
                        </div>
                    </div>
                    
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
                    @if(request('restaurant_id'))
                        Aucun avis trouvé pour ce restaurant.
                    @elseif(request('rating'))
                        Aucun avis trouvé avec cette note.
                    @elseif(request('search'))
                        Aucun avis correspondant à votre recherche "{{ request('search') }}".
                    @else
                        Aucun avis trouvé.
                    @endif
                    
                    @if(request('search') || request('restaurant_id') || request('rating'))
                        <p class="mb-0 mt-2">Essayez de modifier vos critères de recherche ou 
                            <a href="{{ route('admin.reviews.index') }}">afficher tous les avis</a>.
                        </p>
                    @endif
                </div>
            @else
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <a href="{{ route('admin.reviews.index', array_merge(request()->except(['sort', 'direction']), ['sort' => 'restaurant', 'direction' => (request('sort') == 'restaurant' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="text-body">
                                        Restaurant
                                        @if(request('sort') == 'restaurant')
                                            <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('admin.reviews.index', array_merge(request()->except(['sort', 'direction']), ['sort' => 'user', 'direction' => (request('sort') == 'user' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="text-body">
                                        Client
                                        @if(request('sort') == 'user')
                                            <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('admin.reviews.index', array_merge(request()->except(['sort', 'direction']), ['sort' => 'rating', 'direction' => (request('sort') == 'rating' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="text-body">
                                        Note
                                        @if(request('sort') == 'rating')
                                            <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('admin.reviews.index', array_merge(request()->except(['sort', 'direction']), ['sort' => 'comment', 'direction' => (request('sort') == 'comment' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="text-body">
                                        Commentaire
                                        @if(request('sort') == 'comment')
                                            <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('admin.reviews.index', array_merge(request()->except(['sort', 'direction']), ['sort' => 'created_at', 'direction' => (request('sort') == 'created_at' && request('direction') == 'asc') ? 'desc' : 'asc'])) }}" class="text-body">
                                        Date
                                        @if(request('sort') == 'created_at' || !request('sort'))
                                            <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                        @endif
                                    </a>
                                </th>
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
                                        <a href="{{ route('admin.users.show', $review->user_id) }}">
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
                                        <div class="d-flex gap-2">
                                            <a class="btn btn-sm btn-info" href="{{ route('admin.reviews.show', $review->id) }}" title="Voir">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            <a class="btn btn-sm btn-primary" href="{{ route('admin.reviews.edit', $review->id) }}" title="Modifier">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet avis ?')" title="Supprimer">
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
                
                <!-- Pas de pagination -->
            @endif
        </div>
    </div>
</div>
@endsection
