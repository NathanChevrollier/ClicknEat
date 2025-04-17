@extends('layouts.main')

@section('main')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Restaurants /</span> Créer un restaurant</h4>

        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <h5 class="card-header">Nouveau restaurant</h5>
                    <div class="card-body">
                        <form action="{{ route('restaurants.store') }}" method="POST" class="mb-3">
                            @csrf
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Nom du restaurant</label>
                                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Entrez le nom du restaurant" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Téléphone</label>
                                    <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="Entrez le numéro de téléphone" value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Adresse</label>
                                <input type="text" id="address" name="address" class="form-control @error('address') is-invalid @enderror" placeholder="Entrez l'adresse du restaurant" value="{{ old('address') }}" required>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" placeholder="Entrez une description du restaurant" rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="is_open" name="is_open" value="1" {{ old('is_open', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_open">Restaurant ouvert</label>
                                    </div>
                                    <small class="text-muted">Activez cette option si votre restaurant est actuellement ouvert et accepte les commandes.</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="accepts_reservations" name="accepts_reservations" value="1" {{ old('accepts_reservations', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="accepts_reservations">Accepte les réservations</label>
                                    </div>
                                    <small class="text-muted">Activez cette option si votre restaurant accepte actuellement les réservations de table.</small>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bx bx-save me-1"></i> Créer le restaurant
                                </button>
                                <a href="{{ route('restaurants.index') }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Retour
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection