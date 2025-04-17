@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Modifier le plat</h3>
            <a href="{{ route('admin.items.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.items.update', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="restaurant_id" class="form-label">Restaurant</label>
                    <select class="form-select @error('restaurant_id') is-invalid @enderror" id="restaurant_id" required>
                        <option value="">Sélectionnez un restaurant</option>
                        @foreach($restaurants as $restaurant)
                            <option value="{{ $restaurant->id }}" {{ $item->category->restaurant_id == $restaurant->id ? 'selected' : '' }}>
                                {{ $restaurant->name }} ({{ $restaurant->user->name }})
                            </option>
                        @endforeach
                    </select>
                    @error('restaurant_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="category_id" class="form-label">Catégorie</label>
                    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                        <option value="">Sélectionnez d'abord un restaurant</option>
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="name" class="form-label">Nom du plat</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $item->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $item->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="price" class="form-label">Prix (€)</label>
                    <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $item->price / 100) }}" step="0.01" min="0" required>
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input @error('is_active') is-invalid @enderror" type="checkbox" id="is_active" name="is_active" {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Actif (disponible à la commande)
                    </label>
                    @error('is_active')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bx bx-save me-1"></i> Enregistrer
                    </button>
                    <a href="{{ route('admin.items.index') }}" class="btn btn-secondary">
                        <i class="bx bx-x me-1"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const restaurantSelect = document.getElementById('restaurant_id');
        const categorySelect = document.getElementById('category_id');
        const currentCategoryId = '{{ $item->category_id }}';
        
        // Fonction pour charger les catégories d'un restaurant
        function loadCategories(restaurantId) {
            // Réinitialiser le select des catégories
            categorySelect.innerHTML = '<option value="">Chargement des catégories...</option>';
            
            if (!restaurantId) {
                categorySelect.innerHTML = '<option value="">Sélectionnez d\'abord un restaurant</option>';
                return;
            }
            
            // Requête AJAX pour récupérer les catégories du restaurant
            fetch(`/api/restaurants/${restaurantId}/categories`)
                .then(response => response.json())
                .then(data => {
                    categorySelect.innerHTML = '';
                    
                    if (data.length === 0) {
                        categorySelect.innerHTML = '<option value="">Aucune catégorie disponible</option>';
                        return;
                    }
                    
                    categorySelect.innerHTML = '<option value="">Sélectionnez une catégorie</option>';
                    
                    data.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.id;
                        option.textContent = category.name;
                        
                        // Sélectionner la catégorie actuelle
                        if (category.id == currentCategoryId) {
                            option.selected = true;
                        }
                        
                        categorySelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des catégories:', error);
                    categorySelect.innerHTML = '<option value="">Erreur lors du chargement des catégories</option>';
                });
        }
        
        // Événement de changement du restaurant
        restaurantSelect.addEventListener('change', function() {
            loadCategories(this.value);
        });
        
        // Charger les catégories au chargement de la page
        loadCategories(restaurantSelect.value);
    });
</script>
@endsection
