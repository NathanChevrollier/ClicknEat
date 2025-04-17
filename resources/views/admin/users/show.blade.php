@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Administration / Utilisateurs /</span> Détails de l'utilisateur
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Informations de l'utilisateur</h5>
                    <div>
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary me-2">
                            <i class="bx bx-edit me-1"></i> Modifier
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Retour à la liste
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="fw-semibold">ID</h6>
                                <p>{{ $user->id }}</p>
                            </div>
                            <div class="mb-3">
                                <h6 class="fw-semibold">Nom</h6>
                                <p>{{ $user->name }}</p>
                            </div>
                            <div class="mb-3">
                                <h6 class="fw-semibold">Email</h6>
                                <p>{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="fw-semibold">Rôle</h6>
                                <p>
                                    @if($user->isAdmin())
                                        <span class="badge bg-label-primary">Administrateur</span>
                                    @elseif($user->isRestaurateur())
                                        <span class="badge bg-label-success">Restaurateur</span>
                                    @else
                                        <span class="badge bg-label-info">Client</span>
                                    @endif
                                </p>
                            </div>
                            <div class="mb-3">
                                <h6 class="fw-semibold">Date d'inscription</h6>
                                <p>{{ $user->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                            <div class="mb-3">
                                <h6 class="fw-semibold">Dernière mise à jour</h6>
                                <p>{{ $user->updated_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    @if($user->isRestaurateur())
                    <div class="mt-4">
                        <h6 class="fw-semibold mb-3">Restaurants gérés</h6>
                        @php
                            $restaurants = \App\Models\Restaurant::where('user_id', $user->id)->get();
                        @endphp

                        @if($restaurants->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nom</th>
                                            <th>Adresse</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($restaurants as $restaurant)
                                        <tr>
                                            <td>{{ $restaurant->id }}</td>
                                            <td>{{ $restaurant->name }}</td>
                                            <td>{{ $restaurant->address }}</td>
                                            <td>
                                                <a href="{{ route('restaurants.show', $restaurant->id) }}" class="btn btn-sm btn-info">
                                                    <i class="bx bx-show me-1"></i> Voir
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-1"></i> Cet utilisateur ne gère aucun restaurant.
                            </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
