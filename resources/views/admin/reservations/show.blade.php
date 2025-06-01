@extends('layouts.main')
@php use Illuminate\Support\Str; @endphp

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Administration / <a href="{{ route('admin.reservations.index') }}">Réservations</a> /</span> Détails de la réservation
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Informations de la réservation #{{ $reservation->id }}</h5>
                    <div>
                        <a href="{{ route('admin.reservations.edit', $reservation) }}" class="btn btn-primary btn-sm">
                            <i class="bx bx-edit-alt me-1"></i> Modifier
                        </a>
                        @if($reservation->status === 'pending')
                            <form action="{{ route('admin.reservations.confirm', $reservation) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Confirmer cette réservation ?')">
                                    <i class="bx bx-check-circle me-1"></i> Confirmer
                                </button>
                            </form>
                        @endif
                        
                        @if($reservation->status === 'confirmed')
                            <form action="{{ route('admin.reservations.complete', $reservation) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Marquer cette réservation comme terminée ?')">
                                    <i class="bx bx-check-double me-1"></i> Terminer
                                </button>
                            </form>
                        @endif
                        
                        @if($reservation->status === 'pending' || $reservation->status === 'confirmed')
                            <form action="{{ route('admin.reservations.cancel', $reservation) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                                    <i class="bx bx-x-circle me-1"></i> Annuler
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Client</label>
                                <p>{{ $reservation->user->name }} ({{ $reservation->user->email }})</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Restaurant</label>
                                <p>{{ $reservation->restaurant->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Table</label>
                                <p>{{ $reservation->table->name }} ({{ $reservation->table->capacity }} personnes)</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Date et heure</label>
                                <p>{{ \Carbon\Carbon::parse($reservation->reservation_date)->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nombre de personnes</label>
                                <p>{{ $reservation->guests_number }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Statut</label>
                                <p>
                                    @switch($reservation->status)
                                        @case('pending')
                                            <span class="badge bg-label-warning">En attente</span>
                                            @break
                                        @case('confirmed')
                                            <span class="badge bg-label-info">Confirmée</span>
                                            @break
                                        @case('completed')
                                            <span class="badge bg-label-success">Terminée</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge bg-label-danger">Annulée</span>
                                            @break
                                    @endswitch
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Commande associée</label>
                                @php
                                    // Récupération de la commande associée à cette réservation
                                    // Si la réservation a un order_id, on récupère directement la commande
                                    $order = null;
                                    if ($reservation->order_id) {
                                        $order = \App\Models\Order::find($reservation->order_id);
                                    }
                                @endphp
                                
                                @if($order)
                                    <div class="mb-2">
                                        <span class="badge bg-label-info fs-6 mb-2">Commande #{{ $order->id }}</span>
                                        @if($order->status === 'cancelled')
                                            <span class="badge bg-label-danger fs-6">Commande annulée</span>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-receipt me-1"></i> Voir la commande
                                        </a>
                                        <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bx bx-edit me-1"></i> Modifier la commande
                                        </a>
                                    </div>
                                @else
                                    <p>Aucune commande associée</p>
                                    @if($reservation->status !== 'cancelled' && $reservation->status !== 'completed')
                                        <a href="{{ route('admin.orders.create', [
                                            'reservation_id' => $reservation->id,
                                            'user_id' => $reservation->user_id,
                                            'restaurant_id' => $reservation->restaurant_id,
                                            'table_id' => $reservation->table_id
                                        ]) }}" class="btn btn-sm btn-success">
                                            <i class="bx bx-plus me-1"></i> Créer une commande
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('admin.reservations.index') }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Retour à la liste
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
