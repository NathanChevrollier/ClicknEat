@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Plats /</span> Détails du plat
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ $item->name }}</h5>
                    <div>
                        <a href="{{ route('items.index', ['restaurant_id' => $item->category->restaurant->id]) }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Retour
                        </a>
                        @if(auth()->user()->isRestaurateur() && auth()->user()->restaurants->contains($item->category->restaurant_id))
                            <a href="{{ route('items.edit', $item->id) }}" class="btn btn-warning">
                                <i class="bx bx-edit me-1"></i> Modifier
                            </a>
                            <form action="{{ route('items.destroy', $item->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer ce plat ?')">
                                    <i class="bx bx-trash me-1"></i> Supprimer
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="fw-semibold">Informations générales</h6>
                                    <hr>
                                    <div class="mb-3">
                                        <strong>ID:</strong> {{ $item->id }}
                                    </div>
                                    <div class="mb-3">
                                        <strong>Nom:</strong> {{ $item->name }}
                                    </div>
                                    <div class="mb-3">
                                        <strong>Prix:</strong> {{ number_format($item->price / 100, 2) }} €
                                    </div>
                                    <div class="mb-3">
                                        <strong>Coût:</strong> {{ $item->cost ? number_format($item->cost / 100, 2) . ' €' : 'Non défini' }}
                                    </div>
                                    <div class="mb-3">
                                        <strong>Statut:</strong> 
                                        @if($item->is_active)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-danger">Inactif</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="fw-semibold">Informations associées</h6>
                                    <hr>
                                    <div class="mb-3">
                                        <strong>Catégorie:</strong> 
                                        <a href="{{ route('restaurants.categories.show', [$item->category->restaurant->id, $item->category->id]) }}">
                                            {{ $item->category->name }}
                                        </a>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Restaurant:</strong> 
                                        <a href="{{ route('restaurants.show', $item->category->restaurant->id) }}">
                                            {{ $item->category->restaurant->name }}
                                        </a>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Date de création:</strong> {{ $item->created_at->format('d/m/Y H:i') }}
                                    </div>
                                    <div class="mb-3">
                                        <strong>Dernière modification:</strong> {{ $item->updated_at->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
