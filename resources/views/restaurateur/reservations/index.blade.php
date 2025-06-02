@extends('layouts.main')

@section('title', 'Gestion des réservations')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Restaurant /</span> Gestion des réservations
    </h4>

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

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Réservations pour {{ $restaurant->name }}</h5>
            <div class="d-flex">
                <div class="dropdown me-2">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bx bx-sort me-1"></i> Trier par
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="sortDropdown">
                        <li><a class="dropdown-item" href="{{ route('restaurant.reservations.index', ['restaurant' => $restaurant->id, 'sort' => 'date_asc']) }}">Date (plus ancienne)</a></li>
                        <li><a class="dropdown-item" href="{{ route('restaurant.reservations.index', ['restaurant' => $restaurant->id, 'sort' => 'date_desc']) }}">Date (plus récente)</a></li>
                        <li><a class="dropdown-item" href="{{ route('restaurant.reservations.index', ['restaurant' => $restaurant->id, 'sort' => 'guests_asc']) }}">Nombre d'invités (croissant)</a></li>
                        <li><a class="dropdown-item" href="{{ route('restaurant.reservations.index', ['restaurant' => $restaurant->id, 'sort' => 'guests_desc']) }}">Nombre d'invités (décroissant)</a></li>
                    </ul>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bx bx-filter me-1"></i> Filtrer par statut
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                        <li><a class="dropdown-item" href="{{ route('restaurant.reservations.index', ['restaurant' => $restaurant->id]) }}">Tous</a></li>
                        <li><a class="dropdown-item" href="{{ route('restaurant.reservations.index', ['restaurant' => $restaurant->id, 'status' => 'pending']) }}">En attente</a></li>
                        <li><a class="dropdown-item" href="{{ route('restaurant.reservations.index', ['restaurant' => $restaurant->id, 'status' => 'confirmed']) }}">Confirmées</a></li>
                        <li><a class="dropdown-item" href="{{ route('restaurant.reservations.index', ['restaurant' => $restaurant->id, 'status' => 'cancelled']) }}">Annulées</a></li>
                        <li><a class="dropdown-item" href="{{ route('restaurant.reservations.index', ['restaurant' => $restaurant->id, 'status' => 'completed']) }}">Terminées</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Date & Heure</th>
                        <th>Nombre de personnes</th>
                        <th>Table</th>
                        <th>Statut</th>
                        <th>Commande</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($reservations as $reservation)
                        <tr>
                            <td>{{ $reservation->id }}</td>
                            <td>{{ $reservation->user->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($reservation->reservation_date)->format('d/m/Y H:i') }}</td>
                            <td>{{ $reservation->guests_number }}</td>
                            <td>{{ $reservation->table->name ?? 'Non assignée' }}</td>
                            <td>
                                @php
                                    $statusClass = [
                                        'pending' => 'bg-label-warning',
                                        'confirmed' => 'bg-label-success',
                                        'cancelled' => 'bg-label-danger',
                                        'completed' => 'bg-label-info'
                                    ][$reservation->status] ?? 'bg-label-secondary';
                                    
                                    $statusText = [
                                        'pending' => 'En attente',
                                        'confirmed' => 'Confirmée',
                                        'cancelled' => 'Annulée',
                                        'completed' => 'Terminée'
                                    ][$reservation->status] ?? 'Inconnu';
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                            </td>
                            <td>
                                @if($reservation->order)
                                    <a href="{{ route('orders.show', $reservation->order->id) }}" class="badge bg-primary">
                                        Voir la commande
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
                                        <a class="dropdown-item" href="{{ route('restaurant.reservations.show', ['restaurant' => $restaurant->id, 'reservation' => $reservation->id]) }}">
                                            <i class="bx bx-show-alt me-1"></i> Détails
                                        </a>
                                        @if($reservation->status == 'pending')
                                            <form action="{{ route('restaurant.reservations.update.status', ['restaurant' => $restaurant->id, 'reservation' => $reservation->id]) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="confirmed">
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bx bx-check me-1"></i> Confirmer
                                                </button>
                                            </form>
                                        @endif
                                        @if(in_array($reservation->status, ['pending', 'confirmed']))
                                            <form action="{{ route('restaurant.reservations.update.status', ['restaurant' => $restaurant->id, 'reservation' => $reservation->id]) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bx bx-x me-1"></i> Annuler
                                                </button>
                                            </form>
                                        @endif
                                        @if($reservation->status == 'confirmed')
                                            <form action="{{ route('restaurant.reservations.update.status', ['restaurant' => $restaurant->id, 'reservation' => $reservation->id]) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bx bx-check-double me-1"></i> Marquer terminée
                                                </button>
                                            </form>
                                        @endif
                                        @if(!$reservation->order && in_array($reservation->status, ['confirmed', 'pending']))
                                            <form action="{{ route('reservations.add-order', $reservation->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bx bx-food-menu me-1"></i> Ajouter commande
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bx bx-calendar-x fs-1 text-primary mb-2"></i>
                                    <h5>Aucune réservation trouvée</h5>
                                    <p class="text-muted">Vous n'avez pas encore de réservations pour ce restaurant</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($reservations->count() > 0)
            <div class="card-footer">
                {{ $reservations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
