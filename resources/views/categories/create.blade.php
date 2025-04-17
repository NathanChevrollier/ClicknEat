@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Catégories /</span> Créer une catégorie</h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <h5 class="card-header">Création d'une catégorie</h5>
                <div class="card-body">
                    <form action="{{ route('restaurants.categories.store', $restaurant->id) }}" method="POST" class="mb-3">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom de la catégorie</label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Entrez le nom de la catégorie" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Restaurant</label>
                            <input type="text" class="form-control" value="{{ $restaurant->name }}" disabled>
                            <input type="hidden" name="restaurant_id" value="{{ $restaurant->id }}">
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bx bx-save me-1"></i> Enregistrer
                            </button>
                            <a href="{{ route('restaurants.categories.index', $restaurant->id) }}" class="btn btn-outline-secondary">
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