@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Administration /</span> 
        Gestion des plats
        @if(isset($restaurant))
            <span class="text-muted fw-light">/ {{ $restaurant->name }}</span>
        @endif
    </h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                @if(isset($restaurant))
                    Plats du restaurant {{ $restaurant->name }}
                @else
                    Liste des plats
                @endif
            </h5>
            <div>
                @if(isset($restaurant))
                    <a href="{{ route('restaurants.show', $restaurant->id) }}" class="btn btn-secondary me-2">
                        <i class="bx bx-building me-1"></i> Voir le restaurant
                    </a>
                    <a href="{{ route('admin.items.create', ['restaurant_id' => $restaurant->id]) }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Ajouter un plat
                    </a>
                @else
                    <div class="dropdown d-inline-block me-2">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="restaurantFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-filter-alt me-1"></i> Filtrer par restaurant
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="restaurantFilterDropdown">
                            <li><a class="dropdown-item" href="{{ route('admin.items.index') }}">Tous les restaurants</a></li>
                            <li><hr class="dropdown-divider"></li>
                            @php
                                $restaurants = \App\Models\Restaurant::orderBy('name')->get();
                            @endphp
                            @foreach($restaurants as $rest)
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.items.index', ['restaurant_id' => $rest->id]) }}">
                                        {{ $rest->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <a href="{{ route('admin.items.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Ajouter un plat
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if(isset($restaurant))
                <div class="alert alert-info mb-3">
                    <i class="bx bx-info-circle me-1"></i> Vous consultez les plats du restaurant <strong>{{ $restaurant->name }}</strong>.
                    <div class="mt-2">
                        <a href="{{ route('admin.categories.index', ['restaurant_id' => $restaurant->id]) }}" class="btn btn-sm btn-outline-primary me-2">
                            <i class="bx bx-category me-1"></i> Voir les catégories de ce restaurant
                        </a>
                        <a href="{{ route('admin.menus.index', ['restaurant_id' => $restaurant->id]) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bx bx-restaurant me-1"></i> Voir les menus de ce restaurant
                        </a>
                    </div>
                </div>
            @endif
            
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Prix</th>
                            <th>Catégorie</th>
                            <th>Restaurant</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @php
                            $items = \App\Models\Item::with('category.restaurant')->get();
                        @endphp
                        @foreach($items as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ number_format($item->price / 100, 2) }} €</td>
                            <td>
                                <a href="{{ route('admin.items.index', ['category_id' => $item->category->id]) }}" class="text-body">
                                    {{ $item->category->name }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.items.index', ['restaurant_id' => $item->category->restaurant->id]) }}" class="text-body">
                                    {{ $item->category->restaurant->name }}
                                </a>
                            </td>
                            <td>
                                @if($item->is_available)
                                    <span class="badge bg-label-success">Actif</span>
                                @else
                                    <span class="badge bg-label-danger">Inactif</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="{{ route('admin.items.edit', $item->id) }}">
                                            <i class="bx bx-edit-alt me-1"></i> Modifier
                                        </a>
                                        <a class="dropdown-item" href="{{ route('admin.items.show', $item->id) }}">
                                            <i class="bx bx-show me-1"></i> Voir
                                        </a>
                                        <a class="dropdown-item" href="#" onclick="event.preventDefault(); if(confirm('Êtes-vous sûr de vouloir supprimer ce plat ?')) document.getElementById('delete-item-{{ $item->id }}').submit();">
                                            <i class="bx bx-trash me-1"></i> Supprimer
                                        </a>
                                        <form id="delete-item-{{ $item->id }}" action="{{ route('admin.items.destroy', $item->id) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="id" value="{{ $item->id }}">
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($items->isEmpty())
                <div class="alert alert-info mt-3">
                    <i class="bx bx-info-circle me-1"></i>
                    @if(isset($restaurant))
                        Aucun plat trouvé pour ce restaurant.
                    @else
                        Aucun plat trouvé.
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
