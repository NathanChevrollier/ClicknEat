@extends('layouts.main')

@section('title', 'Détails de la réservation')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Restaurant / Réservations /</span> Détails
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

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Détails de la réservation #{{ $reservation->id }}</h5>
                    <a href="{{ route('restaurant.reservations.index', $restaurant->id) }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Retour à la liste
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Client</label>
                                <p>{{ $reservation->user->name }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email</label>
                                <p>{{ $reservation->user->email }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Téléphone</label>
                                <p>{{ $reservation->user->phone ?? 'Non renseigné' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Date et heure</label>
                                <p>{{ \Carbon\Carbon::parse($reservation->reservation_date)->format('d/m/Y à H:i') }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nombre de personnes</label>
                                <p>{{ $reservation->guests_number }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Table</label>
                                <p>{{ $reservation->table->name ?? 'Non assignée' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Statut actuel</label>
                        <div>
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
                            <span class="badge {{ $statusClass }} fs-6 px-3 py-2">{{ $statusText }}</span>
                        </div>
                    </div>
                    
                    @if($reservation->notes)
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notes</label>
                            <div class="p-3 bg-light rounded">
                                {{ $reservation->notes }}
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="d-flex gap-2">
                        @if($reservation->status == 'pending')
                            <form action="{{ route('restaurant.reservations.update.status', ['restaurant' => $restaurant->id, 'reservation' => $reservation->id]) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="confirmed">
                                <button type="submit" class="btn btn-success">
                                    <i class="bx bx-check me-1"></i> Confirmer
                                </button>
                            </form>
                        @endif
                        
                        @if(in_array($reservation->status, ['pending', 'confirmed']))
                            <form action="{{ route('restaurant.reservations.update.status', ['restaurant' => $restaurant->id, 'reservation' => $reservation->id]) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="btn btn-danger">
                                    <i class="bx bx-x me-1"></i> Annuler
                                </button>
                            </form>
                        @endif
                        
                        @if($reservation->status == 'confirmed')
                            <form action="{{ route('restaurant.reservations.update.status', ['restaurant' => $restaurant->id, 'reservation' => $reservation->id]) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="btn btn-info">
                                    <i class="bx bx-check-double me-1"></i> Marquer terminée
                                </button>
                            </form>
                        @endif
                        
                        @if(!$reservation->order && in_array($reservation->status, ['confirmed', 'pending']))
                            <form action="{{ route('reservations.add-order', $reservation->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-food-menu me-1"></i> Ajouter commande
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-lg-5">
            @if($reservation->order)
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Commande associée</h5>
                        <a href="{{ route('orders.show', $reservation->order->id) }}" class="btn btn-sm btn-primary">
                            <i class="bx bx-detail me-1"></i> Voir en détail
                        </a>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Statut:</strong> 
                            @php
                                $orderStatusClass = [
                                    'pending' => 'bg-label-warning',
                                    'confirmed' => 'bg-label-success',
                                    'cancelled' => 'bg-label-danger',
                                    'completed' => 'bg-label-info',
                                    'in_progress' => 'bg-label-primary',
                                    'ready' => 'bg-label-secondary',
                                    'delivered' => 'bg-label-dark'
                                ][$reservation->order->status] ?? 'bg-label-secondary';
                                
                                $orderStatusText = [
                                    'pending' => 'En attente',
                                    'confirmed' => 'Confirmée',
                                    'cancelled' => 'Annulée',
                                    'completed' => 'Terminée',
                                    'in_progress' => 'En préparation',
                                    'ready' => 'Prête',
                                    'delivered' => 'Livrée'
                                ][$reservation->order->status] ?? 'Inconnu';
                            @endphp
                            <span class="badge {{ $orderStatusClass }}">{{ $orderStatusText }}</span>
                        </p>
                        <p class="mb-1"><strong>Total:</strong> {{ number_format($reservation->order->total_amount/100, 2, ',', ' ') }} €</p>
                        <p class="mb-1"><strong>Nombre d'articles:</strong> {{ $reservation->order->items->count() }}</p>
                        
                        @if($reservation->order->notes)
                            <div class="mt-3">
                                <p class="mb-1"><strong>Notes:</strong></p>
                                <div class="p-2 bg-light rounded">
                                    {{ $reservation->order->notes }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="card mb-4">
                    <div class="card-body text-center py-5">
                        <div class="mb-3">
                            <i class="bx bx-food-menu fs-1 text-primary"></i>
                        </div>
                        <h5>Aucune commande associée</h5>
                        <p class="text-muted">Cette réservation n'a pas de commande associée pour le moment.</p>
                        
                        @if(in_array($reservation->status, ['confirmed', 'pending']))
                            <form action="{{ route('reservations.add-order', $reservation->id) }}" method="POST" class="mt-3">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-plus me-1"></i> Créer une commande
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations du restaurant</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        @if($restaurant->logo)
                            <img src="{{ asset('storage/' . $restaurant->logo) }}" alt="{{ $restaurant->name }}" class="me-3" style="width: 64px; height: 64px; object-fit: cover; border-radius: 8px;">
                        @else
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="bx bx-restaurant"></i>
                                </span>
                            </div>
                        @endif
                        <div>
                            <h5 class="mb-0">{{ $restaurant->name }}</h5>
                            <small class="text-muted">{{ $restaurant->address }}</small>
                        </div>
                    </div>
                    
                    <p class="mb-1"><i class="bx bx-phone me-1"></i> {{ $restaurant->phone }}</p>
                    <p class="mb-1"><i class="bx bx-envelope me-1"></i> {{ $restaurant->email }}</p>
                    
                    @if($restaurant->opening_hours)
                        <div class="mt-3">
                            <p class="mb-1"><strong>Horaires d'ouverture:</strong></p>
                            <div class="p-2 bg-light rounded">
                                {!! nl2br(e($restaurant->opening_hours)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
