@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Plats</h3>
            <div class="d-flex">
                @if(isset($restaurant))
                    <a href="{{ route('restaurants.show', $restaurant->id) }}" class="btn btn-secondary me-2">
                        <i class="bx bx-arrow-back me-1"></i> Retour au restaurant
                    </a>
                @endif
                <a href="{{ route('items.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Ajouter un plat
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(isset($restaurant))
                <div class="alert alert-info mb-4">
                    <i class="bx bx-info-circle me-1"></i> Vous consultez les plats du restaurant <strong>{{ $restaurant->name }}</strong>
                </div>
            @elseif(auth()->user()->isRestaurateur())
                <div class="mb-4">
                    <label for="restaurant-filter" class="form-label">Filtrer par restaurant</label>
                    <select id="restaurant-filter" class="form-select" onchange="window.location.href = this.value">
                        <option value="{{ route('items.index') }}">Tous les restaurants</option>
                        @foreach(auth()->user()->restaurants as $rest)
                            <option value="{{ route('items.index', ['restaurant' => $rest->id]) }}" {{ (isset($restaurant) && isset($restaurant->id) && $restaurant->id == $rest->id) ? 'selected' : '' }}>{{ $rest->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <form method="GET" class="row g-3 mb-3 align-items-end">
                <div class="col-md-3">
                    <label for="sort" class="form-label">Trier par</label>
                    <select class="form-select" id="sort" name="sort">
                        <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nom (A-Z)</option>
                        <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nom (Z-A)</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Trier</button>
                </div>
                @if(request('restaurant'))
                    <input type="hidden" name="restaurant" value="{{ request('restaurant') }}">
                @endif
            </form>

            @if($items->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 5%">ID</th>
                                <th style="width: 15%">Nom</th>
                                <th style="width: 10%">Prix</th>
                                <th style="width: 15%">Catégorie</th>
                                @if(!isset($restaurant))
                                    <th style="width: 20%">Restaurant</th>
                                @endif
                                <th style="width: 10%">Statut</th>
                                <th style="width: 20%" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ number_format($item->price / 100, 2) }} €</td>
                                    <td>
                                        @if(isset($item->category))
                                            {{ $item->category->name }}
                                        @else
                                            <span class="text-muted">Non assigné</span>
                                        @endif
                                    </td>
                                    @if(!isset($restaurant))
                                        <td>
                                            @if(isset($item->category) && isset($item->category->restaurant))
                                                <a href="{{ route('restaurants.show', $item->category->restaurant->id) }}" title="Voir le restaurant">
                                                    {{ $item->category->restaurant->name }}
                                                </a>
                                            @else
                                                <span class="text-muted">Non assigné</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td>
                                        @if($item->is_active)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-danger">Inactif</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('items.show', $item->id) }}">
                                                        <i class="bx bx-show me-1"></i> Voir
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('items.edit', $item->id) }}">
                                                        <i class="bx bx-edit me-1"></i> Modifier
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <form action="{{ route('items.destroy', $item->id) }}" method="POST" style="margin: 0;">
                                                        @csrf
                                                        @method('delete')
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Voulez-vous vraiment supprimer ce plat ?')">
                                                            <i class="bx bx-trash me-1"></i> Supprimer
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-1"></i> Aucun plat trouvé.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
