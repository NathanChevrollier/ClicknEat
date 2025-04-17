@extends('layouts.main')

@php
    use Illuminate\Support\Str;
@endphp

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Catégories et Plats</h3>
            <div>
                <a href="{{ route('restaurants.show', $restaurant->id) }}" class="btn btn-secondary me-2">
                    <i class="bx bx-arrow-back me-1"></i> Retour au restaurant
                </a>
                <a href="{{ route('restaurants.categories.create', $restaurant->id) }}" class="btn btn-primary me-2">
                    <i class="bx bx-plus me-1"></i> Ajouter une catégorie
                </a>
                <a href="{{ route('items.create', ['restaurant_id' => $restaurant->id]) }}" class="btn btn-success">
                    <i class="bx bx-plus me-1"></i> Ajouter un plat
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-4">
                <i class="bx bx-info-circle me-1"></i> Vous consultez les catégories et plats du restaurant <strong>{{ $restaurant->name }}</strong>
            </div>
            
            @foreach($categories as $category)
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center bg-light">
                        <h5 class="m-0">{{ $category->name }} ({{ $category->items->count() }} plat(s))</h5>
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                Actions
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('restaurants.categories.edit', [$restaurant->id, $category->id]) }}">
                                        <i class="bx bx-edit me-1"></i> Modifier
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form action="{{ route('restaurants.categories.destroy', [$restaurant->id, $category->id]) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Voulez-vous vraiment supprimer cette catégorie ?')">
                                            <i class="bx bx-trash me-1"></i> Supprimer
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($category->items->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th style="width: 20%">Nom</th>
                                            <th style="width: 30%">Description</th>
                                            <th style="width: 15%">Prix</th>
                                            <th style="width: 15%">Statut</th>
                                            <th style="width: 20%" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($category->items as $item)
                                            <tr>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ Str::limit($item->description, 50) }}</td>
                                                <td>@formatPrice($item->price)</td>
                                                <td>
                                                    @if($item->is_active)
                                                        <span class="badge bg-success">Actif</span>
                                                    @else
                                                        <span class="badge bg-danger">Inactif</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a href="{{ route('items.show', $item->id) }}" class="btn btn-sm btn-info">
                                                            <i class="bx bx-show"></i>
                                                        </a>
                                                        <a href="{{ route('items.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                                            <i class="bx bx-edit"></i>
                                                        </a>
                                                        <form action="{{ route('items.destroy', $item->id) }}" method="POST" style="margin: 0;">
                                                            @csrf
                                                            @method('delete')
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer ce plat ?')">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="bx bx-info-circle me-1"></i> Aucun plat dans cette catégorie.
                                <a href="{{ route('items.create', ['restaurant_id' => $restaurant->id, 'category_id' => $category->id]) }}" class="alert-link">Ajouter un plat</a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
            
            @if($categories->count() === 0)
                <div class="alert alert-warning">
                    <i class="bx bx-info-circle me-1"></i> Aucune catégorie pour ce restaurant.
                    <a href="{{ route('restaurants.categories.create', $restaurant->id) }}" class="alert-link">Ajouter une catégorie</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
