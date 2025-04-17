@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Plats /</span> Modifier un plat</h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <h5 class="card-header">Modification du plat</h5>
                <div class="card-body">
                    <form action="{{ route('items.update', $item->id) }}" method="POST" class="mb-3">
                        @csrf
                        @method('PUT')
                        
                        <input type="hidden" id="selected_restaurant_id" value="{{ $item->category->restaurant_id }}">
                        
                        <div class="alert alert-info mb-4">
                            <i class="bx bx-info-circle me-1"></i> Vous modifiez un plat du restaurant <strong>{{ $item->category->restaurant->name }}</strong>
                        </div>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom du plat</label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Entrez le nom du plat" value="{{ old('name', $item->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" placeholder="Entrez une description du plat" rows="3">{{ old('description', $item->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="cost" class="form-label">Coût (centimes)</label>
                                <input type="number" id="cost" name="cost" class="form-control @error('cost') is-invalid @enderror" placeholder="Coût en centimes" value="{{ old('cost', $item->cost) }}">
                                <small class="text-muted">Exemple : 500 pour 5,00 €</small>
                                @error('cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="price" class="form-label">Prix (centimes)</label>
                                <input type="number" id="price" name="price" class="form-control @error('price') is-invalid @enderror" placeholder="Prix en centimes" value="{{ old('price', $item->price) }}" required>
                                <small class="text-muted">Exemple : 1000 pour 10,00 €</small>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Catégorie</label>
                            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">Choisir une catégorie</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" data-restaurant="{{ $category->restaurant_id }}" {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }} ({{ $category->restaurant->name }})</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Actif</label>
                            </div>
                            <small class="text-muted">Si coché, le plat sera visible et commandable par les clients</small>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bx bx-save me-1"></i> Enregistrer les modifications
                            </button>
                            <a href="{{ route('items.index', ['restaurant_id' => $item->category->restaurant_id]) }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Retour
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('category_id');
        const selectedRestaurantId = document.getElementById('selected_restaurant_id').value;
        
        function filterCategories() {            
            // Masquer toutes les options qui ne correspondent pas au restaurant sélectionné
            Array.from(categorySelect.options).forEach(option => {
                if (option.value === '') return; // Ignorer l'option par défaut
                
                const optionRestaurantId = option.getAttribute('data-restaurant');
                option.style.display = (optionRestaurantId === selectedRestaurantId) ? '' : 'none';
            });
        }
        
        // Filtrer les catégories au chargement de la page
        filterCategories();
    });
</script>
@endsection
@endsection
