@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Restaurant /</span> Gestion des réservations
    </h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Réservations pour {{ $restaurant->name }}</h5>
            <div>
                <a href="{{ route('restaurants.show', $restaurant->id) }}" class="btn btn-outline-secondary me-2">
                    <i class="bx bx-arrow-back me-1"></i> Retour au restaurant
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
                <form action="{{ route('restaurant.reservations', $restaurant->id) }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tous les statuts</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmées</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulées</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Terminées</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="{{ request('date') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bx bx-filter-alt me-1"></i> Filtrer
                        </button>
                        <a href="{{ route('restaurant.reservations', $restaurant->id) }}" class="btn btn-outline-secondary">
                            <i class="bx bx-reset me-1"></i> Réinitialiser
                        </a>
                    </div>
                </form>
            </div>

            @if($reservations->isEmpty())
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-1"></i>
                    Aucune réservation trouvée pour ce restaurant.
                </div>
            @else
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Date & Heure</th>
                                <th>Personnes</th>
                                <th>Table</th>
                                <th>Statut</th>
                                <th>Commande</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach($reservations as $reservation)
                                <tr>
                                    <td><strong>{{ $reservation->user->name }}</strong></td>
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
                                        @if($reservation->order)
                                            <a href="{{ route('orders.show', $reservation->order->id) }}" class="btn btn-sm btn-outline-info">
                                                <i class="bx bx-receipt me-1"></i> Voir
                                            </a>
                                        @else
                                            <span class="badge bg-label-secondary">Aucune</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                @if($reservation->status === 'pending')
                                                    <form action="{{ route('reservations.confirm', $reservation->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item" onclick="return confirm('Confirmer cette réservation ?')">
                                                            <i class="bx bx-check-circle me-1"></i> Confirmer
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                @if($reservation->status === 'confirmed')
                                                    <form action="{{ route('reservations.complete', $reservation->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item" onclick="return confirm('Marquer cette réservation comme terminée ?')">
                                                            <i class="bx bx-check-double me-1"></i> Terminer
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                @if($reservation->status === 'pending' || $reservation->status === 'confirmed')
                                                    <form action="{{ route('reservations.cancel', $reservation->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                                                            <i class="bx bx-x-circle me-1"></i> Annuler
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    {{ $reservations->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
