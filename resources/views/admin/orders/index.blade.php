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
        
        <!-- Formulaire de recherche et filtres -->
        <div class="card-body border-bottom">
            <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Rechercher par client, restaurant ou statut..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">Tous les statuts</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                @switch($status)
                                    @case('pending')
                                        En attente
                                        @break
                                    @case('confirmed')
                                        Confirmée
                                        @break
                                    @case('preparing')
                                        En préparation
                                        @break
                                    @case('ready')
                                        Prête
                                        @break
                                    @case('completed')
                                        Terminée
                                        @break
                                    @case('cancelled')
                                        Annulée
                                        @break
                                    @case('delivered')
                                        Livrée
                                        @break
                                    @default
                                        {{ ucfirst($status) }}
                                @endswitch
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ route('admin.orders.index', ['sort' => 'id', 'direction' => (request('sort') == 'id' && request('direction') == 'asc') ? 'desc' : 'asc', 'search' => request('search'), 'status' => request('status')]) }}" class="text-body">
                                    ID
                                    @if(request('sort') == 'id')
                                        <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.orders.index', ['sort' => 'user', 'direction' => (request('sort') == 'user' && request('direction') == 'asc') ? 'desc' : 'asc', 'search' => request('search'), 'status' => request('status')]) }}" class="text-body">
                                    Client
                                    @if(request('sort') == 'user')
                                        <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.orders.index', ['sort' => 'restaurant', 'direction' => (request('sort') == 'restaurant' && request('direction') == 'asc') ? 'desc' : 'asc', 'search' => request('search'), 'status' => request('status')]) }}" class="text-body">
                                    Restaurant
                                    @if(request('sort') == 'restaurant')
                                        <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.orders.index', ['sort' => 'total_amount', 'direction' => (request('sort') == 'total_amount' && request('direction') == 'asc') ? 'desc' : 'asc', 'search' => request('search'), 'status' => request('status')]) }}" class="text-body">
                                    Montant
                                    @if(request('sort') == 'total_amount')
                                        <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.orders.index', ['sort' => 'status', 'direction' => (request('sort') == 'status' && request('direction') == 'asc') ? 'desc' : 'asc', 'search' => request('search'), 'status' => request('status')]) }}" class="text-body">
                                    Statut
                                    @if(request('sort') == 'status')
                                        <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.orders.index', ['sort' => 'created_at', 'direction' => (request('sort') == 'created_at' && request('direction') == 'asc') ? 'desc' : 'asc', 'search' => request('search'), 'status' => request('status')]) }}" class="text-body">
                                    Date
                                    @if(request('sort') == 'created_at' || !request('sort'))
                                        <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach($orders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->user->name }}</td>
                            <td>{{ $order->restaurant->name }}</td>
                            <td>{{ number_format($order->total_amount / 100, 2) }} €</td>
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
                                <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bx bx-edit me-1"></i> Modifier
                                </a>
                                <a href="#" class="btn btn-sm btn-danger" onclick="event.preventDefault(); if(confirm('Êtes-vous sûr de vouloir supprimer cette commande ?')) document.getElementById('delete-order-{{ $order->id }}').submit();">
                                    <i class="bx bx-trash me-1"></i> Supprimer
                                </a>
                                <form id="delete-order-{{ $order->id }}" action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
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
            
            @if($orders->isEmpty())
                <div class="alert alert-info mt-4">
                    <i class="bx bx-info-circle me-1"></i> Aucune commande trouvée.
                    @if(request('search') || request('status'))
                        <p class="mb-0 mt-2">Essayez de modifier vos critères de recherche ou <a href="{{ route('admin.orders.index') }}">afficher toutes les commandes</a>.</p>
                    @endif
                </div>
            @endif
            
            <!-- Pas de pagination -->
        </div>
    </div>
</div>
@endsection
