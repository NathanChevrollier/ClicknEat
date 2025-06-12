@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Restaurant / Tables /</span> Modifier une table
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <h5 class="card-header">Modifier la table {{ $table->name }}</h5>
                <div class="card-body">
                    <form action="{{ route('restaurants.tables.update', [$restaurant->id, $table->id]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom de la table</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $table->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="capacity" class="form-label">Capacité (nombre de personnes)</label>
                            <input type="number" class="form-control @error('capacity') is-invalid @enderror" id="capacity" name="capacity" value="{{ old('capacity', $table->capacity) }}" min="1" required>
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="location" class="form-label">Emplacement</label>
                            <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location" value="{{ old('location', $table->location) }}">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="categorie_id" class="form-label">Catégorie de table</label>
                            <select class="form-select @error('categorie_id') is-invalid @enderror" id="categorie_id" name="categorie_id">
                                <option value="" disabled>Sélectionner une catégorie</option>
                                @foreach($categories as $categorie)
                                    <option value="{{ $categorie->id }}" {{ (old('categorie_id', $table->categorie_id) == $categorie->id) ? 'selected' : '' }}>
                                        {{ $categorie->nom }}
                                    </option>
                                @endforeach
                            </select>
                            @error('categorie_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="pmr" class="form-label">Accessibilité</label>
                            <select class="form-select @error('pmr') is-invalid @enderror" id="pmr" name="pmr">
                                <option value="0" {{ old('pmr', $table->pmr ? '1' : '0') == '0' ? 'selected' : '' }}>Table standard</option>
                                <option value="1" {{ old('pmr', $table->pmr ? '1' : '0') == '1' ? 'selected' : '' }}>Table accessible PMR</option>
                            </select>
                            @error('pmr')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Sélectionnez si cette table est accessible aux personnes à mobilité réduite.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $table->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="categorie_id" class="form-label">Catégorie de table</label>
                            <select class="form-select @error('categorie_id') is-invalid @enderror" id="categorie_id" name="categorie_id">
                                <option value="" disabled>Sélectionner une catégorie</option>
                                @foreach($categories as $categorie)
                                    <option value="{{ $categorie->id }}" {{ (old('categorie_id', $table->categorie_id) == $categorie->id) ? 'selected' : '' }}>
                                        {{ $categorie->nom }}
                                    </option>
                                @endforeach
                            </select>
                            @error('categorie_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_available" name="is_available" value="1" {{ old('is_available', $table->is_available) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_available">Table disponible</label>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary me-2">Mettre à jour</button>
                            <a href="{{ route('restaurants.tables.index', $restaurant->id) }}" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
