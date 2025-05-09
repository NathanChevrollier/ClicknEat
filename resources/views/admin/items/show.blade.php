@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Détails du plat</h3>
            <div>
                <a href="{{ route('admin.items.index') }}" class="btn btn-secondary me-2">
                    <i class="bx bx-arrow-back me-1"></i> Retour à la liste
                </a>
                <a href="{{ route('admin.items.edit', $item->id) }}" class="btn btn-warning">
                    <i class="bx bx-edit me-1"></i> Modifier
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Informations du plat</h5>
                    <div class="mb-3">
                        <strong>Nom:</strong> {{ e($item->name) }}
                    </div>
                    <div class="mb-3">
                        <strong>Description:</strong> 
                        <p>{{ e($item->description ?: 'Aucune description') }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Prix:</strong> {{ number_format($item->price / 100, 2, ',', ' ') }} €
                    </div>
                    <div class="mb-3">
                        <strong>Statut:</strong> 
                        @if($item->is_available)
                            <span class="badge bg-success">Actif</span>
                        @else
                            <span class="badge bg-danger">Inactif</span>
                        @endif
                    </div>
                    <div class="mb-3">
                        <strong>Catégorie:</strong> 
                        <a href="{{ route('admin.categories.show', $item->category->id) }}">
                            {{ e($item->category->name) }}
                        </a>
                    </div>
                    <div class="mb-3">
                        <strong>Restaurant:</strong> 
                        <a href="{{ route('admin.items.index', ['restaurant_id' => $item->category->restaurant->id]) }}">
                            {{ e($item->category->restaurant->name) }}
                        </a>
                    </div>
                    <div class="mb-3">
                        <strong>Propriétaire:</strong> {{ e($item->category->restaurant->user->name) }}
                    </div>
                    <div class="mb-3">
                        <strong>Créé le:</strong> {{ $item->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="mb-3">
                        <strong>Dernière modification:</strong> {{ $item->updated_at->format('d/m/Y H:i') }}
                    </div>
                </div>
                <div class="col-md-6">
                    <h5>Menu associé au plat</h5>
                    @if(isset($item->menu) && $item->menu)
                        <div class="list-group">
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ e($item->menu->name) }}</h6>
                                    <small>{{ number_format($item->menu->price / 100, 2, ',', ' ') }} €</small>
                                </div>
                                <p class="mb-1">{{ e(Str::limit($item->menu->description, 50)) }}</p>
                                <small>Restaurant: {{ e($item->menu->restaurant->name) }}</small>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-1"></i> Ce plat n'est associé à aucun menu.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
