@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Restaurant /</span> Gestion des tables
    </h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                @if(isset($restaurant))
                    Tables du restaurant {{ $restaurant->name }}
                @elseif(isset($restaurants) && $restaurants->count() > 0)
                    Tables de tous vos restaurants
                @else
                    Tables
                @endif
            </h5>
            <div>
                @if(isset($restaurant))
                    <a href="{{ route('restaurants.tables.create', $restaurant->id) }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Ajouter une table
                    </a>
                @elseif(isset($restaurants) && $restaurants->count() > 0)
                    <div class="dropdown d-inline-block me-2">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="restaurantFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-filter-alt me-1"></i> Filtrer par restaurant
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="restaurantFilterDropdown">
                            <li><a class="dropdown-item" href="{{ route('restaurants.tables.index', ['restaurant' => 'all']) }}">Tous les restaurants</a></li>
                            <li><hr class="dropdown-divider"></li>
                            @foreach($restaurants as $rest)
                                <li>
                                    <a class="dropdown-item" href="{{ route('restaurants.tables.index', ['restaurant' => $rest->id]) }}">
                                        {{ $rest->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <a href="{{ route('restaurants.tables.create', $restaurants->first()->id) }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Ajouter une table
                    </a>
                @endif
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

            @if(isset($restaurant))
                <div class="alert alert-info mb-4">
                    <i class="bx bx-info-circle me-1"></i> Vous consultez les tables du restaurant <strong>{{ $restaurant->name }}</strong>
                </div>
            @elseif(isset($restaurants) && $restaurants->count() > 0)
                <div class="alert alert-info mb-4">
                    <i class="bx bx-info-circle me-1"></i> Vous consultez les tables de tous vos restaurants.
                </div>
            @endif

            <form method="GET" class="row g-3 mb-3 align-items-end">
                <div class="col-md-3">
                    <label for="sort" class="form-label">Trier par</label>
                    <select class="form-select" id="sort" name="sort">
                        <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nom (A-Z)</option>
                        <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nom (Z-A)</option>
                        <option value="capacity_asc" {{ request('sort') == 'capacity_asc' ? 'selected' : '' }}>Capacité croissante</option>
                        <option value="capacity_desc" {{ request('sort') == 'capacity_desc' ? 'selected' : '' }}>Capacité décroissante</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Trier</button>
                </div>
                @if(request('restaurant'))
                    <input type="hidden" name="restaurant" value="{{ request('restaurant') }}">
                @endif
            </form>

            @if(count($tables) === 0)
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-1"></i>
                    Aucune table n'a été ajoutée pour ce restaurant.
                </div>
            @else
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Capacité</th>
                                <th>Emplacement</th>
                                <th>Disponibilité</th>
                                <th>Restaurant</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach($tables as $table)
                                <tr>
                                    <td><strong>{{ $table->name }}</strong></td>
                                    <td>{{ $table->capacity }} personnes</td>
                                    <td>{{ $table->location ?: 'Non spécifié' }}</td>
                                    <td>
                                        @if($table->is_available)
                                            <span class="badge bg-label-success">Disponible</span>
                                        @else
                                            <span class="badge bg-label-danger">Indisponible</span>
                                        @endif
                                        <form action="{{ route('restaurants.tables.toggle-availability', [$table->restaurant->id ?? $restaurant->id, $table->id]) }}" method="POST" class="d-inline ms-2">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $table->is_available ? 'btn-outline-danger' : 'btn-outline-success' }}" title="{{ $table->is_available ? 'Marquer comme indisponible' : 'Marquer comme disponible' }}">
                                                <i class="bx {{ $table->is_available ? 'bx-x' : 'bx-check' }}"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        @if(isset($table->restaurant))
                                            <span class="badge bg-label-primary">{{ $table->restaurant->name }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('restaurants.tables.show', [$table->restaurant->id ?? $restaurant->id, $table->id]) }}" class="btn btn-sm btn-info" title="Détails">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('restaurants.tables.edit', [$table->restaurant->id ?? $restaurant->id, $table->id]) }}" class="btn btn-sm btn-primary" title="Modifier">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                        <form action="{{ route('restaurants.tables.destroy', [$table->restaurant->id ?? $restaurant->id, $table->id]) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette table ?')" title="Supprimer">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
