@extends('layouts.main')
@php use Illuminate\Support\Str; @endphp

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Administration / Restaurants /</span> {{ $restaurant->name }}
    </h4>

    <!-- Informations du restaurant -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Informations du restaurant</h5>
            <a href="{{ route('admin.restaurants.edit', $restaurant->id) }}" class="btn btn-primary btn-sm">
                <i class="bx bx-edit-alt me-1"></i> Modifier le restaurant
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>Nom :</strong> {{ $restaurant->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Adresse :</strong> {{ $restaurant->address ?: 'Non spécifiée' }}
                    </div>
                    <div class="mb-3">
                        <strong>Description :</strong> {{ $restaurant->description ?: 'Aucune description' }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>Propriétaire :</strong> {{ $restaurant->user->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Email :</strong> {{ $restaurant->user->email }}
                    </div>
                    <div class="mb-3">
                        <strong>Date de création :</strong> {{ $restaurant->created_at->format('d/m/Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Onglets pour les différentes sections -->
    <div class="nav-align-top mb-4">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-categories" aria-controls="navs-categories" aria-selected="true">
                    <i class="bx bx-category me-1"></i> Catégories
                    <span class="badge rounded-pill badge-center h-px-20 w-px-20 bg-label-primary ms-1">{{ $restaurant->categories->count() }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-items" aria-controls="navs-items" aria-selected="false">
                    <i class="bx bx-food-menu me-1"></i> Plats
                    <span class="badge rounded-pill badge-center h-px-20 w-px-20 bg-label-primary ms-1">{{ $itemsCount }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-menus" aria-controls="navs-menus" aria-selected="false">
                    <i class="bx bx-restaurant me-1"></i> Menus
                    <span class="badge rounded-pill badge-center h-px-20 w-px-20 bg-label-primary ms-1">{{ $restaurant->menus->count() }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-reviews" aria-controls="navs-reviews" aria-selected="false">
                    <i class="bx bx-star me-1"></i> Avis
                    <span class="badge rounded-pill badge-center h-px-20 w-px-20 bg-label-primary ms-1">{{ $reviews->count() }}</span>
                </button>
            </li>
        </ul>
        <div class="tab-content">
            <!-- Onglet Catégories -->
            <div class="tab-pane fade show active" id="navs-categories" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title">Catégories du restaurant</h5>
                    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-plus me-1"></i> Ajouter une catégorie
                    </a>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Description</th>
                                <th>Nombre de plats</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($restaurant->categories as $category)
                            <tr>
                                <td>{{ $category->id }}</td>
                                <td>{{ $category->name }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($category->description, 50) ?: 'Aucune description' }}</td>
                                <td>{{ $category->items->count() }}</td>
                                <td>
                                    <a href="{{ route('admin.categories.show', $category->id) }}" class="btn btn-sm btn-info">
                                        <i class="bx bx-show me-1"></i> Voir
                                    </a>
                                    <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-sm btn-primary">
                                        <i class="bx bx-edit-alt me-1"></i> Modifier
                                    </a>
                                    <a href="#" class="btn btn-sm btn-danger" onclick="event.preventDefault(); if(confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')) document.getElementById('delete-category-{{ $category->id }}').submit();">
                                        <i class="bx bx-trash me-1"></i> Supprimer
                                    </a>
                                    <form id="delete-category-{{ $category->id }}" action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Aucune catégorie trouvée</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Onglet Plats -->
            <div class="tab-pane fade" id="navs-items" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title">Plats du restaurant</h5>
                    <a href="{{ route('admin.items.create') }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-plus me-1"></i> Ajouter un plat
                    </a>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Catégorie</th>
                                <th>Prix</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($items as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->category->name }}</td>
                                <td>{{ number_format($item->price, 2) }} €</td>
                                <td>
                                    @if($item->is_active)
                                    <span class="badge bg-label-success">Actif</span>
                                    @else
                                    <span class="badge bg-label-danger">Inactif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.items.show', $item->id) }}" class="btn btn-sm btn-info">
                                        <i class="bx bx-show me-1"></i> Voir
                                    </a>
                                    <a href="{{ route('admin.items.edit', $item->id) }}" class="btn btn-sm btn-primary">
                                        <i class="bx bx-edit-alt me-1"></i> Modifier
                                    </a>
                                    <a href="#" class="btn btn-sm btn-danger" onclick="event.preventDefault(); if(confirm('Êtes-vous sûr de vouloir supprimer ce plat ?')) document.getElementById('delete-item-{{ $item->id }}').submit();">
                                        <i class="bx bx-trash me-1"></i> Supprimer
                                    </a>
                                    <form id="delete-item-{{ $item->id }}" action="{{ route('admin.items.destroy', $item->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Aucun plat trouvé</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Onglet Menus -->
            <div class="tab-pane fade" id="navs-menus" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title">Menus du restaurant</h5>
                    <a href="{{ route('admin.menus.create') }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-plus me-1"></i> Ajouter un menu
                    </a>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Description</th>
                                <th>Prix</th>
                                <th>Nombre de plats</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($restaurant->menus as $menu)
                            <tr>
                                <td>{{ $menu->id }}</td>
                                <td>{{ $menu->name }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($menu->description, 50) ?: 'Aucune description' }}</td>
                                <td>{{ number_format($menu->price, 2) }} €</td>
                                <td>{{ $menu->items->count() }}</td>
                                <td>
                                    <a href="{{ route('admin.menus.show', $menu->id) }}" class="btn btn-sm btn-info">
                                        <i class="bx bx-show me-1"></i> Voir
                                    </a>
                                    <a href="{{ route('admin.menus.edit', $menu->id) }}" class="btn btn-sm btn-primary">
                                        <i class="bx bx-edit-alt me-1"></i> Modifier
                                    </a>
                                    <a href="#" class="btn btn-sm btn-danger" onclick="event.preventDefault(); if(confirm('Êtes-vous sûr de vouloir supprimer ce menu ?')) document.getElementById('delete-menu-{{ $menu->id }}').submit();">
                                        <i class="bx bx-trash me-1"></i> Supprimer
                                    </a>
                                    <form id="delete-menu-{{ $menu->id }}" action="{{ route('admin.menus.destroy', $menu->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Aucun menu trouvé</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Onglet Avis -->
            <div class="tab-pane fade" id="navs-reviews" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title">Avis du restaurant</h5>
                    <a href="{{ route('admin.reviews.create') }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-plus me-1"></i> Ajouter un avis
                    </a>
                </div>
                
                <!-- Note moyenne -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h6>Note moyenne: {{ number_format($averageRating, 1) }}/5</h6>
                        <div class="text-warning mb-3">
                            @for ($i = 1; $i <= 5; $i++)
                                @if ($i <= round($averageRating))
                                    <i class="bx bxs-star me-1 fs-3"></i>
                                @else
                                    <i class="bx bx-star me-1 fs-3"></i>
                                @endif
                            @endfor
                            <span class="text-muted ms-2">({{ $reviews->count() }} avis)</span>
                        </div>
                    </div>
                </div>
                
                <!-- Liste des avis -->
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Note</th>
                                <th>Commentaire</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($reviews as $review)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.users.show', $review->user_id) }}">
                                        {{ $review->user->name }}
                                    </a>
                                </td>
                                <td>
                                    <div class="text-warning">
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= $review->rating)
                                                <i class="bx bxs-star"></i>
                                            @else
                                                <i class="bx bx-star"></i>
                                            @endif
                                        @endfor
                                    </div>
                                </td>
                                <td>{{ Str::limit($review->comment, 100) }}</td>
                                <td>{{ $review->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a class="btn btn-sm btn-info" href="{{ route('admin.reviews.show', $review->id) }}" title="Voir">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a class="btn btn-sm btn-primary" href="{{ route('admin.reviews.edit', $review->id) }}" title="Modifier">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet avis ?')" title="Supprimer">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Aucun avis trouvé</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
