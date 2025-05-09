@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Détails du menu</h3>
            <div>
                <a href="{{ route('admin.menus.index') }}" class="btn btn-secondary me-2">
                    <i class="bx bx-arrow-back me-1"></i> Retour à la liste
                </a>
                <a href="{{ route('admin.menus.edit', $menu->id) }}" class="btn btn-warning">
                    <i class="bx bx-edit me-1"></i> Modifier
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Informations du menu</h5>
                    <div class="mb-3">
                        <strong>Nom:</strong> {{ $menu->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Description:</strong> 
                        <p>{{ $menu->description ?: 'Aucune description' }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Prix:</strong> {{ number_format($menu->price / 100, 2, ',', ' ') }} €
                    </div>
                    <div class="mb-3">
                        <strong>Restaurant:</strong> 
                        <a href="{{ route('admin.menus.index', ['restaurant_id' => $menu->restaurant->id]) }}">
                            {{ $menu->restaurant->name }}
                        </a>
                    </div>
                    <div class="mb-3">
                        <strong>Propriétaire:</strong> {{ $menu->restaurant->user->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Créé le:</strong> {{ $menu->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="mb-3">
                        <strong>Dernière modification:</strong> {{ $menu->updated_at->format('d/m/Y H:i') }}
                    </div>
                </div>
                <div class="col-md-6">
                    <h5>Plats inclus dans ce menu</h5>
                    @if($menu->items->count() > 0)
                        <div class="list-group">
                            @foreach($menu->items as $item)
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $item->name }}</h6>
                                        <small>{{ number_format($item->price / 100, 2, ',', ' ') }} €</small>
                                    </div>
                                    <p class="mb-1">{{ \Illuminate\Support\Str::limit($item->description, 50) }}</p>
                                    <small>Category: {{ $item->category->name }}</small>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <strong>Prix total des plats:</strong> {{ number_format($menu->items->sum('price') / 100, 2, ',', ' ') }} €
                        </div>
                        <div class="mt-2">
                            <strong>Économie:</strong> {{ number_format(($menu->items->sum('price') - $menu->price) / 100, 2, ',', ' ') }} €
                            ({{ number_format(100 - ($menu->price / $menu->items->sum('price') * 100), 1, ',', ' ') }}%)
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="bx bx-error-circle me-1"></i> Ce menu ne contient aucun plat.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
