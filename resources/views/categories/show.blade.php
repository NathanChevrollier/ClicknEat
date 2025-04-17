@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Catégories /</span> Détails de la catégorie
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ $category->name }}</h5>
                    <div>
                        <a href="{{ route('restaurants.categories.index', $category->restaurant->id) }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Retour
                        </a>
                        <a href="{{ route('restaurants.categories.edit', [$category->restaurant->id, $category->id]) }}" class="btn btn-warning">
                            <i class="bx bx-edit me-1"></i> Modifier
                        </a>
                        <form action="{{ route('restaurants.categories.destroy', [$category->restaurant->id, $category->id]) }}" method="POST" class="d-inline">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer cette catégorie ?')">
                                <i class="bx bx-trash me-1"></i> Supprimer
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-semibold">ID</h6>
                            <p>{{ $category->id }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-semibold">Restaurant</h6>
                            <p>
                                <a href="{{ route('restaurants.show', $category->restaurant->id) }}">
                                    {{ $category->restaurant->name }}
                                </a>
                            </p>
                        </div>
                    </div>

                    <h6 class="fw-semibold mt-4 mb-3">Plats dans cette catégorie</h6>
                    @if($category->items->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Prix</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($category->items as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ number_format($item->price, 2) }} €</td>
                                            <td>
                                                @if($item->is_active)
                                                    <span class="badge bg-success">Actif</span>
                                                @else
                                                    <span class="badge bg-danger">Inactif</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <a href="{{ route('items.show', $item->id) }}" class="btn btn-sm btn-info me-2">
                                                        <i class="bx bx-show"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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