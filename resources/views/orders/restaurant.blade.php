@extends('layouts.main')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Commandes</h3>
            <div class="d-flex">
                @if(isset($restaurant) && $restaurant)
                    <a href="{{ route('restaurants.show', $restaurant->id) }}" class="btn btn-secondary me-2">
                        <i class="bx bx-arrow-back me-1"></i> Retour au restaurant
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if(isset($restaurant) && $restaurant)
                <div class="alert alert-info mb-4">
                    <i class="bx bx-info-circle me-1"></i> Vous consultez les commandes du restaurant <strong>{{ $restaurant->name }}</strong>
                </div>
            @elseif(auth()->user()->isRestaurateur())
                <div class="mb-4">
                    <label for="restaurant-filter" class="form-label">Filtrer par restaurant</label>
                    <select id="restaurant-filter" class="form-select" onchange="window.location.href = this.value">
                        <option value="{{ route('restaurateur.orders') }}" selected>Tous les restaurants</option>
                        @foreach(auth()->user()->restaurants as $rest)
                            <option value="{{ route('restaurateur.orders', ['restaurant_id' => $rest->id]) }}">{{ $rest->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ request()->url() }}" method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="search">Rechercher</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                                        <input type="text" class="form-control" id="search" name="search" placeholder="Nom du client, numéro..." value="{{ request('search') }}">
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label" for="status">Statut</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">Tous les statuts</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="preparing" {{ request('status') == 'preparing' ? 'selected' : '' }}>En préparation</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Terminées</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulées</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label" for="date">Date</label>
                                    <input type="date" class="form-control" id="date" name="date" value="{{ request('date') }}">
                                </div>
                                
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary rounded-pill me-2">
                                        <i class="bx bx-filter-alt me-1"></i> Filtrer
                                    </button>
                                    <a href="{{ request()->url() }}" class="btn btn-outline-secondary rounded-pill">
                                        <i class="bx bx-reset me-1"></i> Réinitialiser
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white mb-3">
                        <div class="card-body">
                            <h5 class="card-title text-white">Total des commandes</h5>
                            <h2 class="text-white">{{ $orders->count() }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white mb-3">
                        <div class="card-body">
                            <h5 class="card-title text-white">Chiffre d'affaires</h5>
                            <h2 class="text-white">{{ number_format($orders->sum('total_amount') / 100, 2, ',', ' ') }} €</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white mb-3">
                        <div class="card-body">
                            <h5 class="card-title text-white">Commandes en attente</h5>
                            <h2 class="text-white">{{ $orders->where('status', 'pending')->count() }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white mb-3">
                        <div class="card-body">
                            <h5 class="card-title text-white">Commandes en préparation</h5>
                            <h2 class="text-white">{{ $orders->where('status', 'preparing')->count() }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            @if($orders->isEmpty())
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-1"></i> Aucune commande trouvée.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Restaurant</th>
                                <th>Client</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>{{ $order->id }}</td>
                                    <td>{{ $order->restaurant->name }}</td>
                                    <td>{{ $order->user->name }}</td>
                                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ number_format($order->total_amount / 100, 2, ',', ' ') }} €</td>
                                    <td>
                                        <span class="badge bg-{{ $order->status == 'completed' ? 'success' : ($order->status == 'cancelled' ? 'danger' : ($order->status == 'preparing' ? 'warning' : 'primary')) }}">
                                            {{ $order->status == 'pending' ? 'En attente' : ($order->status == 'preparing' ? 'En préparation' : ($order->status == 'completed' ? 'Terminée' : 'Annulée')) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="#" class="btn btn-sm btn-info rounded-pill" data-bs-toggle="modal" data-bs-target="#orderDetailsModal{{ $order->id }}">
                                                <i class="bx bx-show-alt"></i>
                                            </a>
                                            @if($order->status == 'pending')
                                                <form action="{{ route('orders.update-status', $order->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="preparing">
                                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill">
                                                        <i class="bx bx-play-circle"></i>
                                                    </button>
                                                </form>
                                            @elseif($order->status == 'preparing')
                                                <form action="{{ route('orders.update-status', $order->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="completed">
                                                    <button type="submit" class="btn btn-sm btn-success rounded-pill">
                                                        <i class="bx bx-check-circle"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @if($order->status != 'cancelled' && $order->status != 'completed')
                                                <form action="{{ route('orders.cancel', $order->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-danger rounded-pill" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette commande ?')">
                                                        <i class="bx bx-x-circle"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal pour les détails de la commande -->
                                <div class="modal fade" id="orderDetailsModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Détails de la commande #{{ $order->id }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <h6>Informations client</h6>
                                                        <p><strong>Nom :</strong> {{ $order->user->name }}</p>
                                                        <p><strong>Email :</strong> {{ $order->user->email }}</p>
                                                        <p><strong>Téléphone :</strong> {{ $order->user->phone ?? 'Non spécifié' }}</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Informations commande</h6>
                                                        <p><strong>Date :</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                                                        <p><strong>Statut :</strong> 
                                                            <span class="badge bg-{{ $order->status == 'completed' ? 'success' : ($order->status == 'cancelled' ? 'danger' : ($order->status == 'preparing' ? 'warning' : 'primary')) }}">
                                                                {{ $order->status == 'pending' ? 'En attente' : ($order->status == 'preparing' ? 'En préparation' : ($order->status == 'completed' ? 'Terminée' : 'Annulée')) }}
                                                            </span>
                                                        </p>
                                                        <p><strong>Total :</strong> {{ number_format($order->total_amount / 100, 2, ',', ' ') }} €</p>
                                                        
                                                        @php
                                                            // Calculer le temps de préparation total en minutes
                                                            $totalPrepTime = 0;
                                                            foreach($order->items as $item) {
                                                                $totalPrepTime += ($item->preparation_time ?? 10) * $item->pivot->quantity;
                                                            }
                                                            // Convertir en heures/minutes si nécessaire
                                                            $hours = floor($totalPrepTime / 60);
                                                            $minutes = $totalPrepTime % 60;
                                                        @endphp
                                                        
                                                        <div class="alert alert-warning mt-2 mb-2">
                                                            <i class="bx bx-time me-1"></i>
                                                            <strong>Temps de préparation estimé :</strong> 
                                                            @if($hours > 0)
                                                                {{ $hours }}h{{ $minutes > 0 ? ' ' . $minutes . 'min' : '' }}
                                                            @else
                                                                {{ $minutes }} minutes
                                                            @endif
                                                        </div>
                                                        
                                                        @if($order->reservation_id)
                                                        <div class="alert alert-info mt-2 mb-0">
                                                            <i class="bx bx-calendar me-1"></i>
                                                            <strong>Réservation associée :</strong> 
                                                            <a href="{{ route('reservations.show', $order->reservation_id) }}" class="alert-link">
                                                                Voir la réservation #{{ $order->reservation_id }}
                                                            </a>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <h6>Articles commandés</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Article</th>
                                                                <th>Prix unitaire</th>
                                                                <th>Quantité</th>
                                                                <th>Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($order->items as $item)
                                                                <tr>
                                                                    <td>{{ $item->name }}</td>
                                                                    <td>{{ number_format($item->pivot->price / 100, 2, ',', ' ') }} €</td>
                                                                    <td>{{ $item->pivot->quantity }}</td>
                                                                    <td>{{ number_format($item->pivot->price * $item->pivot->quantity / 100, 2, ',', ' ') }} €</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <tfoot>
                                                            <tr>
                                                                <td colspan="3" class="text-end"><strong>Total</strong></td>
                                                                <td><strong>{{ number_format($order->total_amount / 100, 2, ',', ' ') }} €</strong></td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">
                                                    <i class="bx bx-x me-1"></i> Fermer
                                                </button>
                                                
                                                <div class="d-flex gap-2">
                                                    @if($order->status == 'pending')
                                                        <form action="{{ route('orders.update-status', $order->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" name="status" value="preparing">
                                                            <button type="submit" class="btn btn-primary rounded-pill">
                                                                <i class="bx bx-play-circle me-1"></i> Commencer préparation
                                                            </button>
                                                        </form>
                                                    @elseif($order->status == 'preparing')
                                                        <form action="{{ route('orders.update-status', $order->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" name="status" value="completed">
                                                            <button type="submit" class="btn btn-success rounded-pill">
                                                                <i class="bx bx-check-circle me-1"></i> Marquer comme terminée
                                                            </button>
                                                        </form>
                                                    @endif
                                                    
                                                    @if($order->status != 'cancelled' && $order->status != 'completed')
                                                        <form action="{{ route('orders.cancel', $order->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit" class="btn btn-danger rounded-pill" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette commande ?')">
                                                                <i class="bx bx-x-circle me-1"></i> Annuler la commande
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
