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
                        <i class="bx bx-building me-1"></i> Voir le restaurant
                    </a>
                    <a href="{{ route('menus.create', ['restaurant_id' => $restaurant->id]) }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Ajouter un menu
                    </a>
                @else
                    <div class="dropdown d-inline-block me-2">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="restaurantFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-filter-alt me-1"></i> Filtrer par restaurant
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="restaurantFilterDropdown">
                            <li><a class="dropdown-item" href="{{ route('admin.menus.index') }}">Tous les restaurants</a></li>
                            <li><hr class="dropdown-divider"></li>
                            @php
                                $restaurants = \App\Models\Restaurant::orderBy('name')->get();
                            @endphp
                            @foreach($restaurants as $rest)
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.menus.index', ['restaurant_id' => $rest->id]) }}">
                                        {{ $rest->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <a href="{{ route('menus.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Ajouter un menu
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if(isset($restaurant))
                <div class="alert alert-info mb-3">
                    <i class="bx bx-info-circle me-1"></i> Vous consultez les menus du restaurant <strong>{{ $restaurant->name }}</strong>.
                    <div class="mt-2">
                        <a href="{{ route('admin.categories.index', ['restaurant_id' => $restaurant->id]) }}" class="btn btn-sm btn-outline-primary me-2">
                            <i class="bx bx-category me-1"></i> Voir les catégories de ce restaurant
                        </a>
                        <a href="{{ route('admin.items.index', ['restaurant_id' => $restaurant->id]) }}" class="btn btn-sm btn-outline-warning">
                            <i class="bx bx-food-menu me-1"></i> Voir les plats de ce restaurant
                        </a>
                    </div>
                </div>
            @endif
            
            @if($menus->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover border-top">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Restaurant</th>
                                <th>Prix</th>
                                <th>Plats inclus</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($menus as $menu)
                                <tr>
                                    <td>{{ $menu->id }}</td>
                                    <td>{{ $menu->name }}</td>
                                    <td>
                                        <a href="{{ route('admin.menus.index', ['restaurant_id' => $menu->restaurant->id]) }}" class="text-body" title="Filtrer par ce restaurant">
                                            {{ $menu->restaurant->name }}
                                        </a>
                                    </td>
                                    <td>{{ number_format($menu->price, 2, ',', ' ') }} u20ac</td>
                                    <td>{{ $menu->items->count() }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a href="{{ route('menus.show', $menu->id) }}" class="dropdown-item">
                                                    <i class="bx bx-show me-1"></i> Voir
                                                </a>
                                                <a href="{{ route('menus.edit', $menu->id) }}" class="dropdown-item">
                                                    <i class="bx bx-edit me-1"></i> Modifier
                                                </a>
                                                <a href="javascript:void(0);" class="dropdown-item text-danger" 
                                                   onclick="event.preventDefault(); 
                                                   if(confirm('Voulez-vous vraiment supprimer ce menu ?')) 
                                                   document.getElementById('delete-menu-{{ $menu->id }}').submit();">
                                                    <i class="bx bx-trash me-1"></i> Supprimer
                                                </a>
                                                <form id="delete-menu-{{ $menu->id }}" action="{{ route('menus.destroy', $menu->id) }}" method="POST" style="display: none;">
                                                    @csrf
                                                    @method('delete')
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-1"></i>
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
