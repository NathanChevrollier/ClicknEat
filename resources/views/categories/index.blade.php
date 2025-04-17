@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Catégories</h3>
            <div>
                @if(isset($restaurant))
                    <a href="{{ route('restaurants.show', $restaurant->id) }}" class="btn btn-secondary me-2">
                        <i class="bx bx-arrow-back me-1"></i> Retour au restaurant
                    </a>
                    <a href="{{ route('restaurants.categories.create', $restaurant->id) }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Créer une catégorie
                    </a>
                @else
                    @if(auth()->user()->restaurants->count() > 0)
                        <a href="{{ route('restaurants.categories.create', auth()->user()->restaurants->first()->id) }}" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i> Créer une catégorie
                        </a>
                    @else
                        <a href="{{ route('restaurants.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i> Créer un restaurant d'abord
                        </a>
                    @endif
                @endif
            </div>
        </div>
        <div class="card-body">
            @if(isset($restaurant))
                <div class="alert alert-info mb-4">
                    <i class="bx bx-info-circle me-1"></i> Vous consultez les catégories du restaurant <strong>{{ $restaurant->name }}</strong>
                </div>
            @elseif(auth()->user()->isRestaurateur() && auth()->user()->restaurants->count() > 1)
                <div class="mb-4">
                    <label for="restaurant-filter" class="form-label">Filtrer par restaurant</label>
                    <form>
                        @if(request('restaurant'))
                            <input type="hidden" name="restaurant" value="{{ request('restaurant') }}">
                        @endif
                        <select id="restaurant-filter" class="form-select" onchange="this.form.submit()">
                            <option value="#">Tous les restaurants</option>
                            @foreach(auth()->user()->restaurants as $rest)
                                <option value="{{ route('restaurants.categories.index', $rest->id ?? '') }}" {{ (isset($restaurant) && isset($restaurant->id) && $restaurant->id == ($rest->id ?? '')) ? 'selected' : '' }}>{{ $rest->name ?? '' }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            @endif

            @if($categories->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Restaurant</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr>
                                    <td>{{ $category->id }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td>
                                        @if($category->restaurant)
                                            <a href="{{ route('restaurants.show', $category->restaurant->id) }}" title="Voir le restaurant">
                                                {{ $category->restaurant->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">Non assigné</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            @if($category->restaurant)
                                                <a href="{{ route('restaurants.categories.show', [$category->restaurant->id, $category->id]) }}" class="btn btn-sm btn-info me-2">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                                <a href="{{ route('restaurants.categories.edit', [$category->restaurant->id, $category->id]) }}" class="btn btn-sm btn-warning me-2">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                <form action="{{ route('restaurants.categories.destroy', [$category->restaurant->id, $category->id]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('delete')
                                                    <input type="hidden" name="id" value="{{ $category->id }}">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer cette catégorie ?')">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted">Actions non disponibles</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-1"></i> Aucune catégorie trouvée.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
@endsection