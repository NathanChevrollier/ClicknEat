@extends('layouts.main')

@section('main')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Détails de la commande #{{ $order->id }}</h3>
        <div>
            <a href="{{ route('orders.index') }}" class="btn btn-secondary">Retour aux commandes</a>
        </div>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Informations générales</h5>
                <table class="table table-bordered">
                    <tr>
                        <th>Restaurant</th>
                        <td>{{ $order->restaurant->name }}</td>
                    </tr>
                    <tr>
                        <th>Client</th>
                        <td>{{ $order->user->name }}</td>
                    </tr>
                    <tr>
                        <th>Date de commande</th>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Statut</th>
                        <td>
                            <span class="badge bg-{{ $order->status === 'pending' ? 'warning' : ($order->status === 'confirmed' ? 'info' : ($order->status === 'preparing' ? 'primary' : ($order->status === 'ready' ? 'success' : ($order->status === 'completed' ? 'success' : 'danger')))) }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Prix total</th>
                        <td>@formatPrice($order->total_price)</td>
                    </tr>
                    @if($order->notes)
                    <tr>
                        <th>Notes</th>
                        <td>{{ $order->notes }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            @if(auth()->user()->isRestaurateur() && in_array($order->status, ['pending', 'confirmed', 'preparing', 'ready']))
            <div class="col-md-6">
                <h5>Mettre à jour le statut</h5>
                <form action="{{ route('orders.update.status', $order->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <select name="status" class="form-control">
                            @if($order->status === 'pending')
                                <option value="confirmed">Confirmer la commande</option>
                            @elseif($order->status === 'confirmed')
                                <option value="preparing">En préparation</option>
                            @elseif($order->status === 'preparing')
                                <option value="ready">Prêt à servir</option>
                            @elseif($order->status === 'ready')
                                <option value="completed">Terminée</option>
                            @endif
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </form>

                @if(in_array($order->status, ['pending', 'confirmed']))
                <div class="mt-3">
                    <form action="{{ route('orders.cancel', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Voulez-vous vraiment annuler cette commande ?')">
                            Annuler la commande
                        </button>
                    </form>
                </div>
                @endif
            </div>
            @elseif(auth()->user()->isClient() && in_array($order->status, ['pending', 'confirmed']) && $order->user_id === auth()->id())
            <div class="col-md-6">
                <div class="mt-3">
                    <form action="{{ route('orders.cancel', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Voulez-vous vraiment annuler cette commande ?')">
                            Annuler ma commande
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>

        <h5>Articles commandés</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Catégorie</th>
                    <th>Menu associé</th>
                    <th>Prix unitaire</th>
                    <th>Quantité</th>
                    <th>Sous-total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->category->name }}</td>
                    <td>
                        @if($item->pivot->menu_id)
                            @php
                                $menu = \App\Models\Menu::find($item->pivot->menu_id);
                            @endphp
                            @if($menu)
                                <span class="badge bg-primary">{{ $menu->name }}</span>
                            @else
                                -
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>@formatPrice($item->pivot->price)</td>
                    <td>{{ $item->pivot->quantity }}</td>
                    <td>@formatPrice($item->pivot->price * $item->pivot->quantity)</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end">Total</th>
                    <th>@formatPrice($order->total_price)</th>
                </tr>
            </tfoot>
        </table>
    </div>
    <!-- /.card-body -->
</div>
@endsection
