@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Restaurant / Tables /</span> Détails de la table
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $table->name }}</h5>
                    <div>
                        <a href="{{ route('restaurants.tables.edit', [$restaurant->id, $table->id]) }}" class="btn btn-primary btn-sm">
                            <i class="bx bx-edit-alt me-1"></i> Modifier
                        </a>
                        <form action="{{ route('restaurants.tables.destroy', [$restaurant->id, $table->id]) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette table ?')">
                                <i class="bx bx-trash me-1"></i> Supprimer
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-semibold">Informations sur la table</h6>
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="badge bg-label-primary me-2">
                                        <i class="bx bx-user"></i>
                                    </div>
                                    <span>Capacité : <strong>{{ $table->capacity }} personnes</strong></span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="badge bg-label-primary me-2">
                                        <i class="bx bx-map"></i>
                                    </div>
                                    <span>Emplacement : <strong>{{ $table->location ?: 'Non spécifié' }}</strong></span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="badge bg-label-primary me-2">
                                        <i class="bx bx-check-circle"></i>
                                    </div>
                                    <span>Statut : 
                                        @if($table->is_available)
                                            <span class="badge bg-success">Disponible</span>
                                        @else
                                            <span class="badge bg-danger">Indisponible</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            
                            @if($table->description)
                                <div class="mt-3">
                                    <h6 class="fw-semibold">Description</h6>
                                    <p class="mb-0">{{ $table->description }}</p>
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="fw-semibold">Informations sur le restaurant</h6>
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="badge bg-label-primary me-2">
                                        <i class="bx bx-store"></i>
                                    </div>
                                    <span>Restaurant : <strong>{{ $restaurant->name }}</strong></span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="badge bg-label-primary me-2">
                                        <i class="bx bx-map"></i>
                                    </div>
                                    <span>Adresse : <strong>{{ $restaurant->address }}</strong></span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="badge bg-label-primary me-2">
                                        <i class="bx bx-phone"></i>
                                    </div>
                                    <span>Téléphone : <strong>{{ $restaurant->phone }}</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('restaurants.tables.index', $restaurant->id) }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Retour aux tables
                        </a>
                        <a href="{{ route('restaurants.tables.availability', $restaurant->id) }}" class="btn btn-outline-primary ms-2">
                            <i class="bx bx-calendar me-1"></i> Vérifier la disponibilité
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Réservations futures pour cette table -->
            <div class="card">
                <h5 class="card-header">Réservations futures</h5>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-1"></i>
                        Aucune réservation future pour cette table.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
