@extends('layouts.main')
@php use Illuminate\Support\Str; @endphp

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Administration / Commandes /</span> Détail de la commande
    </h4>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Commande #{{ $order->id }}</h5>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <h6>Informations client</h6>
                    <p><strong>Client:</strong> {{ $order->user->name }}</p>
                    <p><strong>Email:</strong> {{ $order->user->email }}</p>
                </div>
                <div class="col-md-6">
                    <h6>Informations restaurant</h6>
                    <p><strong>Restaurant:</strong> {{ $order->restaurant->name }}</p>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <h6>Détails de la commande</h6>
                    <p><strong>Statut:</strong> 
                        @switch($order->status)
                            @case('pending')
                                <span class="badge bg-label-warning">En attente</span>
                                @break
                            @case('confirmed')
                                <span class="badge bg-label-info">Confirmée</span>
                                @break
                            @case('preparing')
                                <span class="badge bg-label-primary">En préparation</span>
                                @break
                            @case('ready')
                                <span class="badge bg-label-info">Prête</span>
                                @break
                            @case('completed')
                                <span class="badge bg-label-success">Terminée</span>
                                @break
                            @case('cancelled')
                                <span class="badge bg-label-danger">Annulée</span>
                                @break
                            @default
                                <span class="badge bg-label-secondary">{{ $order->status }}</span>
                        @endswitch
                    </p>
                    <p><strong>Date de création:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Montant total:</strong> {{ number_format($order->total_amount / 100, 2) }} €</p>
                </div>
                <div class="col-md-6">
                    <h6>Adresse de livraison</h6>
                    <p>{{ $order->delivery_address ?: 'Non spécifiée' }}</p>
                    
                    <h6>Notes</h6>
                    <p>{{ $order->notes ?: 'Aucune note' }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <h6>Plats commandés</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Plat</th>
                                    <th>Quantité</th>
                                    <th>Prix unitaire</th>
                                    <th>Sous-total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        @if($item->pivot->menu_id)
                                            {{ $item->name }}
                                            <span class="badge bg-label-info">Menu #{{ $item->pivot->menu_id }}</span>
                                        @else
                                            {{ $item->name }}
                                            <span class="badge bg-label-primary">Individuel</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->pivot->quantity }}</td>
                                    <td>{{ number_format($item->pivot->price / 100, 2) }} €</td>
                                    <td>{{ number_format(($item->pivot->price * $item->pivot->quantity) / 100, 2) }} €</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
