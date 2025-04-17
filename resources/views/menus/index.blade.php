@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Menus</h3>
            <div>
                @if(isset($restaurant))
                    <a href="{{ route('restaurants.show', $restaurant->id) }}" class="btn btn-secondary me-2">
                        <i class="bx bx-arrow-back me-1"></i> Retour au restaurant
                    </a>
                @endif
                @if(isset($restaurant))
                    <a href="{{ route('restaurants.menus.create', $restaurant->id) }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Créer un menu
                    </a>
                @elseif(isset($restaurants) && $restaurants->count() > 0)
                    <div class="dropdown d-inline-block me-2">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="restaurantFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-filter-alt me-1"></i> Filtrer par restaurant
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="restaurantFilterDropdown">
                            <li><a class="dropdown-item" href="{{ route('menus.index') }}">Tous les restaurants</a></li>
                            <li><hr class="dropdown-divider"></li>
                            @foreach($restaurants as $rest)
                                <li>
                                    <a class="dropdown-item" href="{{ route('menus.index', ['restaurant' => $rest->id]) }}">
                                        {{ $rest->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <a href="{{ route('restaurants.menus.create', $restaurants->first()->id) }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Créer un menu
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if(isset($restaurant))
                <div class="alert alert-info mb-4">
                    <i class="bx bx-info-circle me-1"></i> Vous consultez les menus du restaurant <strong>{{ $restaurant->name }}</strong>
                </div>
            @elseif(isset($restaurants) && $restaurants->count() > 0)
                <div class="alert alert-info mb-4">
                    <i class="bx bx-info-circle me-1"></i> Vous consultez les menus de tous vos restaurants.
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

            @if($menus->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Restaurant</th>
                                <th>Prix</th>
                                <th>Nombre de plats</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($menus as $menu)
                                <tr>
                                    <td>{{ $menu->id }}</td>
                                    <td>{{ $menu->name }}</td>
                                    <td>{{ $menu->restaurant->name }}</td>
                                    <td>{{ number_format($menu->price, 2, ',', ' ') }} €</td>
                                    <td>{{ $menu->items->count() }}</td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="{{ route('restaurants.menus.show', [$menu->restaurant->id, $menu->id]) }}" class="btn btn-sm btn-info me-2">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            <a href="{{ route('restaurants.menus.edit', [$menu->restaurant->id, $menu->id]) }}" class="btn btn-sm btn-warning me-2">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            <form action="{{ route('restaurants.menus.destroy', [$menu->restaurant->id, $menu->id]) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer ce menu ?')">
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
            @else
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-1"></i> Aucun menu trouvé.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
