@extends('layouts.main')
@php use Illuminate\Support\Str; @endphp

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Détails de la catégorie</h3>
            <div>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary me-2">
                    <i class="bx bx-arrow-back me-1"></i> Retour à la liste
                </a>
                <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-warning">
                    <i class="bx bx-edit me-1"></i> Modifier
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Informations de la catégorie</h5>
                    <div class="mb-3">
                        <strong>Nom:</strong> {{ $category->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Restaurant:</strong> 
                        <a href="{{ route('admin.categories.index', ['restaurant_id' => $category->restaurant->id]) }}">
                            {{ $category->restaurant->name }}
                        </a>
                    </div>
                    <div class="mb-3">
                        <strong>Propriétaire:</strong> {{ $category->restaurant->user->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Créée le:</strong> {{ $category->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>
                <div class="col-md-6">
                    <h5>Plats dans cette catégorie</h5>
                    @if($category->items->count() > 0)
                        <div class="list-group">
                            @foreach($category->items as $item)
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $item->name }}</h6>
                                        <small>{{ number_format($item->price / 100, 2, ',', ' ') }} €</small>
                                    </div>
                                    <p class="mb-1">{{ Str::limit($item->description, 50) }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-1"></i> Aucun plat dans cette catégorie.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
