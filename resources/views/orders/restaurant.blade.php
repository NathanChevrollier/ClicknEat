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
                            <h2 class="text-white">{{ number_format($orders->sum('total_price') / 100, 2, ',', ' ') }} €</h2>
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
                                    <td>{{ number_format($order->total_price / 100, 2, ',', ' ') }} €</td>
                                    <td>
                                        <span class="badge bg-{{ $order->status == 'completed' ? 'success' : ($order->status == 'cancelled' ? 'danger' : ($order->status == 'preparing' ? 'warning' : 'primary')) }}">
                                            {{ $order->status == 'pending' ? 'En attente' : ($order->status == 'preparing' ? 'En préparation' : ($order->status == 'completed' ? 'Terminée' : 'Annulée')) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#orderDetailsModal{{ $order->id }}">
                                                    <i class="bx bx-show-alt me-1"></i> Voir détails
                                                </a>
                                                @if($order->status == 'pending')
                                                    <form action="{{ route('orders.update-status', $order->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="status" value="preparing">
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="bx bx-play-circle me-1"></i> Commencer préparation
                                                        </button>
                                                    </form>
                                                @elseif($order->status == 'preparing')
                                                    <form action="{{ route('orders.update-status', $order->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="status" value="completed">
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="bx bx-check-circle me-1"></i> Marquer comme terminée
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
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
                                                        <p><strong>Total :</strong> {{ number_format($order->total_price / 100, 2, ',', ' ') }} €</p>
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
                                                                <td><strong>{{ number_format($order->total_price / 100, 2, ',', ' ') }} €</strong></td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                @if($order->status == 'pending')
                                                    <form action="{{ route('orders.update-status', $order->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="status" value="preparing">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="bx bx-play-circle me-1"></i> Commencer préparation
                                                        </button>
                                                    </form>
                                                @elseif($order->status == 'preparing')
                                                    <form action="{{ route('orders.update-status', $order->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="status" value="completed">
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="bx bx-check-circle me-1"></i> Marquer comme terminée
                                                        </button>
                                                    </form>
                                                @endif
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
