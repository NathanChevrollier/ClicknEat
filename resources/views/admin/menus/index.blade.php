@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Administration /</span> 
        Gestion des menus
        @if(isset($restaurant))
            <span class="text-muted fw-light">/ {{ $restaurant->name }}</span>
        @endif
    </h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                @if(isset($restaurant))
                    Menus du restaurant {{ $restaurant->name }}
                @else
                    Liste des menus
                @endif
            </h5>
            <div>
                @if(isset($restaurant))
                    <a href="{{ route('restaurants.show', $restaurant->id) }}" class="btn btn-secondary me-2">
                        Voir le restaurant
                    </a>
                    <a href="{{ route('admin.menus.create', ['restaurant_id' => $restaurant->id]) }}" class="btn btn-primary">
                        Ajouter un menu
                    </a>
                @else
                    <div class="d-flex gap-2">
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Filtrer par restaurant
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                                <li><a class="dropdown-item" href="{{ route('admin.menus.index') }}">Tous les restaurants</a></li>
                                @php
                                    $restaurants = \App\Models\Restaurant::orderBy('name')->get();
                                @endphp
                                @foreach($restaurants as $rest)
                                    <li><a class="dropdown-item" href="{{ route('admin.menus.index', ['restaurant_id' => $rest->id]) }}">{{ e($rest->name) }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                        <a href="{{ route('admin.menus.create') }}" class="btn btn-primary">
                            Ajouter un menu
                        </a>
                    </div>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if(isset($restaurant))
                <div class="alert alert-info mb-3">
                    Vous consultez les menus du restaurant <strong>{{ $restaurant->name }}</strong>.
                    <div class="mt-2">
                        <a href="{{ route('admin.categories.index', ['restaurant_id' => $restaurant->id]) }}" class="btn btn-sm btn-outline-primary me-2">
                            Voir les catégories de ce restaurant
                        </a>
                        <a href="{{ route('admin.items.index', ['restaurant_id' => $restaurant->id]) }}" class="btn btn-sm btn-outline-warning">
                            Voir les plats de ce restaurant
                        </a>
                    </div>
                </div>
            @endif
            
            @if($menus->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover border-top">
                        <thead>
                            <tr class="text-nowrap">
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Restaurant</th>
                                <th>Prix</th>
                                <th>Plats inclus</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($menus as $menu)
                                <tr>
                                    <td>{{ $menu->id }}</td>
                                    <td>{{ e($menu->name) }}</td>
                                    <td>
                                        <a href="{{ route('admin.menus.index', ['restaurant_id' => $menu->restaurant->id]) }}" class="text-body" title="Filtrer par ce restaurant">
                                            {{ e($menu->restaurant->name) }}
                                        </a>
                                    </td>
                                    <td>{{ number_format($menu->price / 100, 2, ',', ' ') }} €</td>
                                    <td>{{ $menu->items->count() }}</td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('admin.menus.show', $menu->id) }}" class="btn btn-sm btn-info">
                                                Voir
                                            </a>
                                            <a href="{{ route('admin.menus.edit', $menu->id) }}" class="btn btn-sm btn-warning">
                                                Modifier
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="if(confirm('Voulez-vous vraiment supprimer ce menu ?')) document.getElementById('delete-menu-{{ $menu->id }}').submit();">
                                                Supprimer
                                            </button>
                                            <form id="delete-menu-{{ $menu->id }}" action="{{ route('admin.menus.destroy', $menu->id) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('delete')
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
                    @if(isset($restaurant))
                        Aucun menu trouvé pour ce restaurant.
                    @else
                        Aucun menu trouvé.
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const restaurantFilter = document.getElementById('restaurant-filter');
        const applyFilterBtn = document.getElementById('apply-filter');
        
        applyFilterBtn.addEventListener('click', function() {
            const selectedRestaurantId = restaurantFilter.value;
            if (selectedRestaurantId) {
                window.location.href = '{{ route("admin.menus.index") }}?restaurant_id=' + selectedRestaurantId;
            } else {
                window.location.href = '{{ route("admin.menus.index") }}';
            }
        });
    });
</script>
@endsection
