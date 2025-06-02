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
                        <td>@formatPrice($order->total_amount)</td>
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
        
        <!-- Récupérer les menus et les plats individuels séparément -->
        @php
            // Récupérer les ID des menus présents dans cette commande
            // Éviter l'ambiguïté en utilisant la collection et non une nouvelle requête SQL
            $menuIds = $order->items->filter(function($item) {
                return $item->order_menu_id !== null;
            })->pluck('order_menu_id')->unique();
            
            // Charger tous les menus concernés
            $menus = \App\Models\Menu::whereIn('id', $menuIds)->get();
            
            // Plats individuels (sans menu_id)
            // Éviter l'ambiguïté en utilisant la collection et non une nouvelle requête SQL
            $individualItems = $order->items->filter(function($item) {
                return $item->order_menu_id === null;
            });
            
            // Créer un tableau associatif pour stocker les quantités de menus
            $menuQuantities = [];
            foreach ($order->items as $item) {
                if ($item->order_menu_id !== null) {
                    if (!isset($menuQuantities[$item->order_menu_id])) {
                        $menuQuantities[$item->order_menu_id] = $item->pivot->quantity;
                    }
                }
            }
        @endphp
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Menus commandés</h5>
            </div>
            <div class="card-body">
                @if($menus->count() > 0)
                    @foreach($menus as $menu)
                        @php
                            // Utiliser le tableau de quantités de menus précalculé
                            $menuQuantity = $menuQuantities[$menu->id] ?? 0;
                            // Prix total pour ce menu
                            $menuTotal = $menu->price * $menuQuantity;
                        @endphp
                        <div class="menu-container border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h5 class="text-primary mb-0">
                                        <i class="bx bx-food-menu me-1"></i>
                                        {{ $menuQuantity }}x Menu {{ $menu->name }}
                                    </h5>
                                </div>
                                <span class="badge bg-primary rounded-pill">@formatPrice($menuTotal)</span>
                            </div>
                            
                            <!-- Plats du menu -->
                            <div class="ps-4 mt-2">
                                <h6 class="text-muted">Contenu du menu:</h6>
                                <ul class="list-group list-group-flush">
                                    @php
                                        // Récupérer les items du menu sans ambiguïté SQL
                                        $menuItems = $order->items->filter(function($item) use ($menu) {
                                            return $item->order_menu_id == $menu->id;
                                        });
                                    @endphp
                                    @foreach($menuItems as $item)
                                    <li class="list-group-item border-0 py-1 d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bx bx-dish text-secondary me-1"></i>
                                            {{ $item->name }}
                                        </div>
                                        <small class="text-muted">@formatPrice($item->price)</small>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-info">Aucun menu commandé</div>
                @endif
            </div>
        </div>
        
        <!-- Plats individuels -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Plats individuels commandés</h5>
            </div>
            <div class="card-body">
                @if($individualItems->count() > 0)
                    <table class="table table-borderless">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Catégorie</th>
                                <th>Prix unitaire</th>
                                <th>Quantité</th>
                                <th>Sous-total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($individualItems as $item)
                            <tr>
                                <td>
                                    <i class="bx bx-dish text-info me-1"></i>
                                    {{ $item->name }}
                                </td>
                                <td>{{ $item->category->name }}</td>
                                <td>@formatPrice($item->pivot->price)</td>
                                <td>{{ $item->pivot->quantity }}</td>
                                <td>@formatPrice($item->pivot->price * $item->pivot->quantity)</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-info">Aucun plat individuel commandé</div>
                @endif
            </div>
        </div>
        
        <!-- Total de la commande -->
        <div class="card bg-success text-white">
            <div class="card-body d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Total de la commande</h5>
                <h4 class="mb-0">@formatPrice($order->total_amount)</h4>
            </div>
        </div>
    </div>
    <!-- /.card-body -->
</div>
@endsection
