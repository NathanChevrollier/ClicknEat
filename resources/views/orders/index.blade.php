@extends('layouts.main')

@section('main')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">{{ auth()->user()->isRestaurateur() ? 'Commandes de mes restaurants' : 'Mes commandes' }}</h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
        @if(count($orders) > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>{{ auth()->user()->isRestaurateur() ? 'Client' : 'Restaurant' }}</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td>
                                    @if(auth()->user()->isRestaurateur())
                                        {{ $order->user->name }}
                                    @else
                                        {{ $order->restaurant->name }}
                                    @endif
                                </td>
                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'confirmed' ? 'info' : ($order->status === 'preparing' ? 'primary' : ($order->status === 'ready' ? 'success' : ($order->status === 'completed' ? 'success' : 'danger')))) }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>@formatPrice($order->total_price)</td>
                                <td>
                                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-info">Voir</a>
                                    
                                    @if(auth()->user()->isRestaurateur() && in_array($order->status, ['pending', 'confirmed', 'preparing', 'ready']))
                                        <form action="{{ route('orders.update.status', $order->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            @if(request('restaurant'))
                                                <input type="hidden" name="restaurant" value="{{ request('restaurant') }}">
                                            @endif
                                            @if($order->status === 'pending')
                                                <input type="hidden" name="status" value="confirmed">
                                                <button type="submit" class="btn btn-sm btn-primary">Confirmer</button>
                                            @elseif($order->status === 'confirmed')
                                                <input type="hidden" name="status" value="preparing">
                                                <button type="submit" class="btn btn-sm btn-primary">En préparation</button>
                                            @elseif($order->status === 'preparing')
                                                <input type="hidden" name="status" value="ready">
                                                <button type="submit" class="btn btn-sm btn-primary">Prêt à servir</button>
                                            @elseif($order->status === 'ready')
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="btn btn-sm btn-success">Terminer</button>
                                            @endif
                                        </form>
                                    @endif
                                    
                                    @if(in_array($order->status, ['pending', 'confirmed']))
                                        @if(auth()->user()->isRestaurateur() || (auth()->user()->isClient() && $order->user_id === auth()->id()))
                                            <form action="{{ route('orders.cancel', $order->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                @if(request('restaurant'))
                                                    <input type="hidden" name="restaurant" value="{{ request('restaurant') }}">
                                                @endif
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Voulez-vous vraiment annuler cette commande ?')">
                                                    Annuler
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info">
                @if(auth()->user()->isRestaurateur())
                    Aucune commande n'a été passée dans vos restaurants pour le moment.
                @else
                    Vous n'avez pas encore passé de commande. <a href="{{ route('restaurants.index') }}" class="alert-link">Découvrir les restaurants</a>
                @endif
            </div>
        @endif
    </div>
    <!-- /.card-body -->
</div>
@endsection
