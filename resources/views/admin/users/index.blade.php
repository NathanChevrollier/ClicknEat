@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Administration /</span> Gestion des utilisateurs</h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Liste des utilisateurs</h5>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Ajouter un utilisateur
            </a>
        </div>
        
        <!-- Formulaire de recherche -->
        <div class="card-body border-bottom">
            <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Rechercher par nom ou email..." value="{{ request('search') }}">
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
                                <a href="{{ route('admin.users.index', ['sort' => 'id', 'direction' => (request('sort') == 'id' && request('direction') == 'asc') ? 'desc' : 'asc', 'search' => request('search')]) }}" class="text-body">
                                    ID
                                    @if(request('sort') == 'id')
                                        <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.users.index', ['sort' => 'name', 'direction' => (request('sort') == 'name' && request('direction') == 'asc') ? 'desc' : 'asc', 'search' => request('search')]) }}" class="text-body">
                                    Nom
                                    @if(request('sort') == 'name')
                                        <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.users.index', ['sort' => 'email', 'direction' => (request('sort') == 'email' && request('direction') == 'asc') ? 'desc' : 'asc', 'search' => request('search')]) }}" class="text-body">
                                    Email
                                    @if(request('sort') == 'email')
                                        <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.users.index', ['sort' => 'role', 'direction' => (request('sort') == 'role' && request('direction') == 'asc') ? 'desc' : 'asc', 'search' => request('search')]) }}" class="text-body">
                                    Rôle
                                    @if(request('sort') == 'role')
                                        <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.users.index', ['sort' => 'created_at', 'direction' => (request('sort') == 'created_at' && request('direction') == 'asc') ? 'desc' : 'asc', 'search' => request('search')]) }}" class="text-body">
                                    Date d'inscription
                                    @if(request('sort') == 'created_at' || !request('sort'))
                                        <i class="bx {{ request('direction') == 'asc' ? 'bx-sort-up' : 'bx-sort-down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->isAdmin())
                                    <span class="badge bg-label-primary">Administrateur</span>
                                @elseif($user->isRestaurateur())
                                    <span class="badge bg-label-success">Restaurateur</span>
                                @else
                                    <span class="badge bg-label-info">Client</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary me-1">
                                    <i class="bx bx-edit-alt"></i>
                                </a>
                                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-info me-1">
                                    <i class="bx bx-show"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-danger" onclick="event.preventDefault(); if(confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) document.getElementById('delete-user-{{ $user->id }}').submit();">
                                    <i class="bx bx-trash"></i>
                                </a>
                                <form id="delete-user-{{ $user->id }}" action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($users->isEmpty())
                <div class="alert alert-info mt-3">
                    <i class="bx bx-info-circle me-1"></i> Aucun utilisateur trouvé.
                    @if(request('search'))
                        <p class="mb-0 mt-2">Essayez de modifier votre recherche ou <a href="{{ route('admin.users.index') }}">afficher tous les utilisateurs</a>.</p>
                    @endif
                </div>
            @endif
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
