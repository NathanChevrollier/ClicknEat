@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Administration /</span> Gestion des commandes</h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Liste des commandes</h5>
            <a href="{{ route('admin.orders.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Ajouter une commande
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Restaurant</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @php
                            $orders = \App\Models\Order::with(['user', 'restaurant'])->get();
                        @endphp
                        @foreach($orders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->user->name }}</td>
                            <td>{{ $order->restaurant->name }}</td>
                            <td>{{ number_format($order->total_price / 100, 2) }} €</td>
                            <td>
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
                            </td>
                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-info">
                                    <i class="bx bx-show me-1"></i> Voir
                                </a>
                                @if(in_array($order->status, ['pending', 'confirmed']))
                                <a href="#" class="btn btn-sm btn-danger" onclick="event.preventDefault(); if(confirm('Êtes-vous sûr de vouloir annuler cette commande ?')) document.getElementById('cancel-order-{{ $order->id }}').submit();">
                                    <i class="bx bx-x-circle me-1"></i> Annuler
                                </a>
                                <form id="cancel-order-{{ $order->id }}" action="{{ route('admin.orders.cancel', $order->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('PATCH')
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
