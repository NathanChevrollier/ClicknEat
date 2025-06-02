@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Restaurant /</span> Mes réservations
    </h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Liste de vos réservations</h5>
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

            @if($reservations->isEmpty())
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-1"></i>
                    Vous n'avez pas encore de réservations.
                </div>
                <div class="text-center mt-4">
                    <a href="{{ route('restaurants.index') }}" class="btn btn-primary">
                        <i class="bx bx-search me-1"></i> Découvrir les restaurants
                    </a>
                </div>
            @else
                <form method="GET" class="row g-3 mb-3 align-items-end">
                    @if(request('restaurant'))
                        <input type="hidden" name="restaurant" value="{{ request('restaurant') }}">
                    @endif
                    <div class="col-md-3">
                        <label for="sort" class="form-label">Trier par</label>
                        <select class="form-select" id="sort" name="sort">
                            <option value="date_asc" {{ request('sort') == 'date_asc' ? 'selected' : '' }}>Date croissante</option>
                            <option value="date_desc" {{ request('sort') == 'date_desc' ? 'selected' : '' }}>Date décroissante</option>
                            <option value="guests_asc" {{ request('sort') == 'guests_asc' ? 'selected' : '' }}>Nb personnes croissant</option>
                            <option value="guests_desc" {{ request('sort') == 'guests_desc' ? 'selected' : '' }}>Nb personnes décroissant</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Trier</button>
                    </div>
                </form>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Restaurant</th>
                                <th>Date & Heure</th>
                                <th>Personnes</th>
                                <th>Table</th>
                                <th>Commande</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @foreach($reservations as $reservation)
                                <tr>
                                    <td><strong>{{ $reservation->restaurant->name }}</strong></td>
                                    <td>{{ $reservation->reservation_date->format('d/m/Y à H:i') }}</td>
                                    <td>{{ $reservation->guests_number }} personnes</td>
                                    <td>{{ $reservation->table->name }}</td>
                                    <td>
                                        @if($reservation->order_id)
                                            <a href="{{ route('orders.show', $reservation->order_id) }}" class="btn btn-sm btn-outline-warning rounded-pill">
                                                <i class="bx bx-food-menu me-1"></i>Commande #{{ $reservation->order_id }}
                                            </a>
                                        @else
                                            @if($reservation->status !== 'cancelled' && $reservation->status !== 'completed')
                                                <form action="{{ route('reservations.add-order', $reservation->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill">
                                                        <i class="bx bx-plus-circle me-1"></i>Ajouter une commande
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted">Aucune commande</span>
                                            @endif
                                        @endif
                                    </td>
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
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="{{ route('reservations.show', $reservation->id) }}" class="btn btn-sm btn-info rounded-pill" title="Détails">
                                                <i class="bx bx-show me-1"></i> Détails
                                            </a>
                                            
                                            {{-- Le bouton modifier a été retiré à la demande du client --}}
                                            
                                            @if($reservation->canBeCancelled())
                                                <form action="{{ route('reservations.cancel', $reservation->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" title="Annuler" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ? ' + (@if($reservation->order_id) 'La commande associée sera également annulée.' @else '' @endif))">
                                                        <i class="bx bx-x-circle me-1"></i> Annuler
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if(auth()->user()->isRestaurateur() && $reservation->status === 'pending')
                                                <form action="{{ route('reservations.confirm', $reservation->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success rounded-pill" title="Confirmer">
                                                        <i class="bx bx-check-circle me-1"></i> Confirmer
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if(auth()->user()->isRestaurateur() && $reservation->status === 'confirmed')
                                                <form action="{{ route('reservations.complete', $reservation->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success rounded-pill" title="Terminer">
                                                        <i class="bx bx-check-double me-1"></i> Terminer
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
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
