@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Administration /</span> Gestion des réservations
    </h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Liste des réservations</h5>
            <div>
                <a href="{{ route('admin.reservations.create') }}" class="btn btn-primary me-2">
                    <i class="bx bx-plus me-1"></i> Ajouter une réservation
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
                <form action="{{ route('admin.reservations.index') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Recherche</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="search" name="search" placeholder="Client, restaurant, ID..." value="{{ request('search') }}">
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
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tous les statuts</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmées</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulées</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Terminées</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="{{ request('date') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bx bx-filter-alt me-1"></i> Filtrer
                        </button>
                        <a href="{{ route('admin.reservations.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-reset me-1"></i> Réinitialiser
                        </a>
                    </div>
                </form>
            </div>

            @if($reservations->isEmpty())
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-1"></i>
                    Aucune réservation trouvée.
                    @if(request('search') || request('status') || request('date') || request('restaurant_id'))
                        <p class="mb-0 mt-2">Essayez de modifier vos critères de recherche ou <a href="{{ route('admin.reservations.index') }}">afficher toutes les réservations</a>.</p>
                    @endif
                </div>
            @else
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <a href="{{ route('admin.reservations.index', ['sort' => 'restaurant', 'direction' => (request('sort') == 'restaurant' && request('direction') == 'asc') ? 'desc' : 'asc', 'search' => request('search'), 'status' => request('status'), 'date' => request('date'), 'restaurant_id' => request('restaurant_id')]) }}" class="text-body">
                                        Restaurant
                                        @if(request('sort') == 'restaurant')
                                            <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('admin.reservations.index', ['sort' => 'user', 'direction' => (request('sort') == 'user' && request('direction') == 'asc') ? 'desc' : 'asc', 'search' => request('search'), 'status' => request('status'), 'date' => request('date'), 'restaurant_id' => request('restaurant_id')]) }}" class="text-body">
                                        Client
                                        @if(request('sort') == 'user')
                                            <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('admin.reservations.index', ['sort' => 'reservation_date', 'direction' => (request('sort') == 'reservation_date' && request('direction') == 'asc') ? 'desc' : 'asc', 'search' => request('search'), 'status' => request('status'), 'date' => request('date'), 'restaurant_id' => request('restaurant_id')]) }}" class="text-body">
                                        Date et heure
                                        @if(request('sort') == 'reservation_date' || !request('sort'))
                                            <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('admin.reservations.index', ['sort' => 'guests_number', 'direction' => (request('sort') == 'guests_number' && request('direction') == 'asc') ? 'desc' : 'asc', 'search' => request('search'), 'status' => request('status'), 'date' => request('date'), 'restaurant_id' => request('restaurant_id')]) }}" class="text-body">
                                        Nombre
                                        @if(request('sort') == 'guests_number')
                                            <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Table</th>
                                <th>
                                    <a href="{{ route('admin.reservations.index', ['sort' => 'status', 'direction' => (request('sort') == 'status' && request('direction') == 'asc') ? 'desc' : 'asc', 'search' => request('search'), 'status' => request('status'), 'date' => request('date'), 'restaurant_id' => request('restaurant_id')]) }}" class="text-body">
                                        Statut
                                        @if(request('sort') == 'status')
                                            <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Commande</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach($reservations as $reservation)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.restaurants.show', $reservation->restaurant_id) }}">
                                            {{ $reservation->restaurant->name }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.users.show', $reservation->user_id) }}">
                                            {{ $reservation->user->name }}
                                        </a>
                                    </td>
                                    <td>{{ $reservation->reservation_date->format('d/m/Y à H:i') }}</td>
                                    <td>{{ $reservation->guests_number }} personnes</td>
                                    <td>{{ $reservation->table->name }}</td>
                                    <td>
                                        @switch($reservation->status)
                                            @case('pending')
                                                <span class="badge bg-label-warning">En attente</span>
                                                @break
                                            @case('confirmed')
                                                <span class="badge bg-label-success">Confirmée</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-label-danger">Annulée</span>
                                                @break
                                            @case('completed')
                                                <span class="badge bg-label-info">Terminée</span>
                                                @break
                                            @default
                                                <span class="badge bg-label-secondary">{{ $reservation->status }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($reservation->order_id)
                                            @php
                                                // On a besoin de l'objet Order pour les liens
                                                $order = \App\Models\Order::find($reservation->order_id);
                                            @endphp
                                            <span class="badge bg-label-primary">Commande #{{ $reservation->order_id }}</span>
                                            <div class="btn-group mt-1">
                                                <a href="{{ route('admin.orders.show', $reservation->order_id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bx bx-receipt me-1"></i> Voir
                                                </a>
                                                <a href="{{ route('admin.orders.edit', $reservation->order_id) }}" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bx bx-edit me-1"></i> Modifier
                                                </a>
                                            </div>
                                        @elseif($reservation->status !== 'cancelled' && $reservation->status !== 'completed')
                                            <a href="{{ route('admin.orders.create', ['reservation_id' => $reservation->id, 'user_id' => $reservation->user_id, 'restaurant_id' => $reservation->restaurant_id, 'table_id' => $reservation->table_id]) }}" class="btn btn-sm btn-outline-success">
                                                <i class="bx bx-plus me-1"></i> Créer une commande
                                            </a>
                                        @else
                                            <span class="badge bg-label-secondary">Aucune commande</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="/admin/reservations/{{ $reservation->id }}" class="btn btn-sm btn-info">
                                                <i class="bx bx-show me-1"></i> Détails
                                            </a>
                                            
                                            <a href="{{ route('admin.reservations.edit', $reservation) }}" class="btn btn-sm btn-primary">
                                                <i class="bx bx-edit me-1"></i> Modifier
                                            </a>
                                            
                                            @if($reservation->status === 'pending')
                                                <form action="{{ route('admin.reservations.confirm', $reservation) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Confirmer cette réservation ?')">
                                                        <i class="bx bx-check-circle me-1"></i> Confirmer
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if($reservation->status === 'confirmed')
                                                <form action="{{ route('admin.reservations.complete', $reservation) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Marquer cette réservation comme terminée ?')">
                                                        <i class="bx bx-check-double me-1"></i> Terminer
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if($reservation->status === 'pending' || $reservation->status === 'confirmed')
                                                <form action="{{ route('admin.reservations.cancel', $reservation) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                                                        <i class="bx bx-x-circle me-1"></i> Annuler
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <form action="{{ route('admin.reservations.destroy', $reservation) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?')">
                                                    <i class="bx bx-trash me-1"></i> Supprimer
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
