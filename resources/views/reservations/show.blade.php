@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Réservations /</span> Détails de la réservation
    </h4>

    <div class="row">
        <div class="col-md-12">
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

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Réservation #{{ $reservation->id }}</h5>
                    <div>
                        {{-- Le bouton modifier a été retiré à la demande du client --}}
                        
                        @if($reservation->canBeCancelled())
                            <form action="{{ route('reservations.cancel', $reservation->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                                    <i class="bx bx-x-circle me-1"></i> Annuler
                                </button>
                            </form>
                        @endif
                        
                        @if(auth()->user()->isRestaurateur() && $reservation->status === 'pending')
                            <form action="{{ route('reservations.confirm', $reservation->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="bx bx-check-circle me-1"></i> Confirmer
                                </button>
                            </form>
                        @endif
                        
                        @if(auth()->user()->isRestaurateur() && $reservation->status === 'confirmed')
                            <form action="{{ route('reservations.complete', $reservation->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-info btn-sm">
                                    <i class="bx bx-check-double me-1"></i> Terminer
                                </button>
                            </form>
                        @endif
                        
                        @if(!$reservation->order_id && $reservation->status !== 'cancelled' && $reservation->status !== 'completed')
                            <form action="{{ route('reservations.add-order', $reservation->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm">
                                    <i class="bx bx-food-menu me-1"></i> Ajouter une commande
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-semibold">Informations sur la réservation</h6>
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="badge bg-label-primary me-2">
                                        <i class="bx bx-calendar-event"></i>
                                    </div>
                                    <span>Date et heure : <strong>{{ $reservation->reservation_date->format('d/m/Y à H:i') }}</strong></span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="badge bg-label-primary me-2">
                                        <i class="bx bx-user"></i>
                                    </div>
                                    <span>Nombre de personnes : <strong>{{ $reservation->guests_number }}</strong></span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="badge bg-label-primary me-2">
                                        <i class="bx bx-table"></i>
                                    </div>
                                    <span>Table : <strong>{{ $reservation->table->name }}</strong> (capacité : {{ $reservation->table->capacity }} personnes)</span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="badge bg-label-primary me-2">
                                        <i class="bx bx-info-circle"></i>
                                    </div>
                                    <span>Statut : 
                                        @switch($reservation->status)
                                            @case('pending')
                                                <span class="badge bg-warning">En attente</span>
                                                @break
                                            @case('confirmed')
                                                <span class="badge bg-success">Confirmée</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-danger">Annulée</span>
                                                @break
                                            @case('completed')
                                                <span class="badge bg-info">Terminée</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ $reservation->status }}</span>
                                        @endswitch
                                    </span>
                                </div>
                            </div>
                            
                            @if($reservation->special_requests)
                                <div class="mt-3">
                                    <h6 class="fw-semibold">Demandes spéciales</h6>
                                    <p class="mb-0">{{ $reservation->special_requests }}</p>
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="fw-semibold">Informations sur le restaurant</h6>
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="badge bg-label-primary me-2">
                                        <i class="bx bx-store"></i>
                                    </div>
                                    <span>Restaurant : <strong>{{ $reservation->restaurant->name }}</strong></span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="badge bg-label-primary me-2">
                                        <i class="bx bx-map"></i>
                                    </div>
                                    <span>Adresse : <strong>{{ $reservation->restaurant->address }}</strong></span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="badge bg-label-primary me-2">
                                        <i class="bx bx-phone"></i>
                                    </div>
                                    <span>Téléphone : <strong>{{ $reservation->restaurant->phone }}</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($reservation->order && $reservation->order->items->count() > 0)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6 class="fw-semibold">Plats précommandés</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Plat</th>
                                                <th>Quantité</th>
                                                <th>Prix unitaire</th>
                                                <th>Total</th>
                                                <th>Instructions spéciales</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($reservation->order->items as $item)
                                                <tr>
                                                    <td>{{ $item->name }}</td>
                                                    <td>{{ $item->pivot->quantity }}</td>
                                                    <td>{{ number_format($item->pivot->price, 2) }} €</td>
                                                    <td>{{ number_format($item->pivot->price * $item->pivot->quantity, 2) }} €</td>
                                                    <td>{{ $item->pivot->special_instructions ?: 'Aucune' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-end fw-bold">Total</td>
                                                <td class="fw-bold">{{ number_format($reservation->order->total_price, 2) }} €</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @elseif($reservation->order && $reservation->order->items->count() == 0)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="bx bx-info-circle me-1"></i>
                                    <span>Vous avez créé une commande pour cette réservation, mais vous n'avez pas encore ajouté de plats.</span>
                                    <div class="mt-3">
                                        <a href="{{ route('orders.edit', $reservation->order_id) }}" class="btn btn-sm btn-primary">
                                            <i class="bx bx-edit me-1"></i> Compléter ma commande
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif(!$reservation->order_id && $reservation->status !== 'cancelled' && $reservation->status !== 'completed')
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="bx bx-food-menu me-1"></i>
                                    <span>Vous n'avez pas encore passé de commande pour cette réservation.</span>
                                    <div class="mt-3">
                                        <form action="{{ route('reservations.add-order', $reservation->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning">
                                                <i class="bx bx-food-menu me-1"></i> Ajouter une commande
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <div class="mt-4">
                        <a href="{{ route('reservations.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Retour aux réservations
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
