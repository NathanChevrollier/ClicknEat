@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Administration /</span> 
        Gestion des catégories
        @if(isset($restaurant))
            <span class="text-muted fw-light">/ {{ $restaurant->name }}</span>
        @endif
    </h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                @if(isset($restaurant))
                    Catégories du restaurant {{ $restaurant->name }}
                @else
                    Liste des catégories
                @endif
            </h5>
            <div>
                @if(isset($restaurant))
                    <a href="{{ route('restaurants.show', $restaurant->id) }}" class="btn btn-secondary me-2">
                        <i class="bx bx-building me-1"></i> Voir le restaurant
                    </a>
                    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Ajouter une catégorie
                    </a>
                @else
                    <div class="dropdown d-inline-block me-2">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="restaurantFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-filter-alt me-1"></i> Filtrer par restaurant
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="restaurantFilterDropdown">
                            <li><a class="dropdown-item" href="{{ route('admin.categories.index') }}">Tous les restaurants</a></li>
                            <li><hr class="dropdown-divider"></li>
                            @php
                                $restaurants = \App\Models\Restaurant::orderBy('name')->get();
                            @endphp
                            @foreach($restaurants as $rest)
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.categories.index', ['restaurant_id' => $rest->id]) }}">
                                        {{ $rest->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Ajouter une catégorie
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if(isset($restaurant))
                <div class="alert alert-info mb-3">
                    <i class="bx bx-info-circle me-1"></i> Vous consultez les catégories du restaurant <strong>{{ $restaurant->name }}</strong>.
                    <div class="mt-2">
                        <a href="{{ route('admin.items.index', ['restaurant_id' => $restaurant->id]) }}" class="btn btn-sm btn-outline-primary me-2">
                            <i class="bx bx-food-menu me-1"></i> Voir les plats de ce restaurant
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
                            <th>Restaurant</th>
                            <th>Nombre de plats</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td>{{ $category->name }}</td>
                            <td>
                                <a href="{{ route('admin.categories.index', ['restaurant_id' => $category->restaurant->id]) }}" class="text-body">
                                    {{ $category->restaurant->name }}
                                </a>
                            </td>
                            <td>{{ $category->items->count() }}</td>
                            <td>
                                <div class="d-flex">
                                    <a href="{{ route('admin.categories.show', $category->id) }}" class="btn btn-sm btn-info me-2" title="Voir les détails">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-sm btn-warning me-2" title="Modifier">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')" title="Supprimer">
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
            
            @if($categories->isEmpty())
                <div class="alert alert-info mt-3">
                    <i class="bx bx-info-circle me-1"></i> Aucune catégorie trouvée.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
