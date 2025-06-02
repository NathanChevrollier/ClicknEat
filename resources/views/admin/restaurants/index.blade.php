@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Administration /</span> Gestion des restaurants</h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Liste des restaurants</h5>
            <a href="{{ route('admin.restaurants.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Ajouter un restaurant
            </a>
        </div>
        
        <!-- Formulaire de recherche -->
        <div class="card-body border-bottom">
            <form action="{{ route('admin.restaurants.index') }}" method="GET" class="row g-3">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Rechercher par nom, adresse ou propriétaire..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ route('admin.restaurants.index', ['sort' => 'id', 'direction' => (request('sort') == 'id' && request('direction') == 'asc') ? 'desc' : 'asc', 'search' => request('search')]) }}" class="text-body">
                                    ID
                                    @if(request('sort') == 'id')
                                        <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.restaurants.index', ['sort' => 'name', 'direction' => (request('sort') == 'name' && request('direction') == 'asc') ? 'desc' : 'asc', 'search' => request('search')]) }}" class="text-body">
                                    Nom
                                    @if(request('sort') == 'name')
                                        <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.restaurants.index', ['sort' => 'address', 'direction' => (request('sort') == 'address' && request('direction') == 'asc') ? 'desc' : 'asc', 'search' => request('search')]) }}" class="text-body">
                                    Adresse
                                    @if(request('sort') == 'address')
                                        <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.restaurants.index', ['sort' => 'user', 'direction' => (request('sort') == 'user' && request('direction') == 'asc') ? 'desc' : 'asc', 'search' => request('search')]) }}" class="text-body">
                                    Propriétaire
                                    @if(request('sort') == 'user')
                                        <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.restaurants.index', ['sort' => 'created_at', 'direction' => (request('sort') == 'created_at' && request('direction') == 'asc') ? 'desc' : 'asc', 'search' => request('search')]) }}" class="text-body">
                                    Date de création
                                    @if(request('sort') == 'created_at' || !request('sort'))
                                        <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                    @endif
                                </a>
                            </th>
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
                                <a href="{{ route('admin.restaurants.edit', $restaurant->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bx bx-edit-alt me-1"></i> Modifier
                                </a>
                                <a href="#" class="btn btn-sm btn-danger" onclick="event.preventDefault(); if(confirm('Êtes-vous sûr de vouloir supprimer ce restaurant ?')) document.getElementById('delete-restaurant-{{ $restaurant->id }}').submit();">
                                    <i class="bx bx-trash me-1"></i> Supprimer
                                </a>
                                <form id="delete-restaurant-{{ $restaurant->id }}" action="{{ route('admin.restaurants.destroy', $restaurant->id) }}" method="POST" style="display: none;">
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
            
            @if($restaurants->isEmpty())
                <div class="alert alert-info mt-4">
                    <i class="bx bx-info-circle me-1"></i> Aucun restaurant trouvé.
                    @if(request('search'))
                        <p class="mb-0 mt-2">Essayez de modifier votre recherche ou <a href="{{ route('admin.restaurants.index') }}">afficher tous les restaurants</a>.</p>
                    @endif
                </div>
            @endif
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $restaurants->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
