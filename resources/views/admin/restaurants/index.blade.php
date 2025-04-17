@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Administration /</span> Gestion des restaurants</h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Liste des restaurants</h5>
            <a href="{{ route('restaurants.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Ajouter un restaurant
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Adresse</th>
                            <th>Propriétaire</th>
                            <th>Date de création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach($restaurants as $restaurant)
                        <tr>
                            <td>{{ $restaurant->id }}</td>
                            <td>
                                <a href="{{ route('admin.restaurants.show', $restaurant->id) }}" class="fw-bold">
                                    {{ $restaurant->name }}
                                </a>
                            </td>
                            <td>{{ $restaurant->address ?: 'Non spécifiée' }}</td>
                            <td>{{ $restaurant->user->name }}</td>
                            <td>{{ $restaurant->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('admin.restaurants.show', $restaurant->id) }}" class="btn btn-sm btn-info">
                                    <i class="bx bx-show me-1"></i> Voir
                                </a>
                                <a href="{{ route('restaurants.edit', $restaurant->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bx bx-edit-alt me-1"></i> Modifier
                                </a>
                                <a href="#" class="btn btn-sm btn-danger" onclick="event.preventDefault(); if(confirm('Êtes-vous sûr de vouloir supprimer ce restaurant ?')) document.getElementById('delete-restaurant-{{ $restaurant->id }}').submit();">
                                    <i class="bx bx-trash me-1"></i> Supprimer
                                </a>
                                <form id="delete-restaurant-{{ $restaurant->id }}" action="{{ route('restaurants.destroy', $restaurant->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="id" value="{{ $restaurant->id }}">
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-4">
                {{ $restaurants->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
