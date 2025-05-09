@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Créer un nouveau plat</h3>
            <a href="{{ route('admin.items.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.items.store') }}" method="POST" id="itemForm">
                @csrf
                <input type="hidden" name="restaurant_id" id="restaurant_id" value="{{ old('restaurant_id') }}">
                
                <div class="mb-3">
                    <label for="restaurant_selector" class="form-label">Restaurant</label>
                    <select class="form-select" id="restaurant_selector" onchange="updateRestaurantAndCategories(this.value)">
                        <option value="">Sélectionnez un restaurant</option>
                        @foreach($restaurants as $restaurant)
                            <option value="{{ $restaurant->id }}" {{ old('restaurant_id') == $restaurant->id ? 'selected' : '' }}>
                                {{ $restaurant->name }} ({{ $restaurant->user->name }})
                            </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback" id="restaurant_error">Veuillez sélectionner un restaurant</div>
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
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="price" class="form-label">Prix (€)</label>
                    <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" step="0.01" min="0" required>
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input @error('is_available') is-invalid @enderror" type="checkbox" id="is_available" name="is_available" value="1" {{ old('is_available') ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_available">
                        Actif (disponible à la commande)
                    </label>
                    @error('is_available')
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
        const restaurantSelect = document.getElementById('restaurant_selector');
        const categorySelect = document.getElementById('category_id');
        const restaurantIdInput = document.getElementById('restaurant_id');
        
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
                        categorySelect.appendChild(option);
                    });
                    
                    // Si une catégorie était sélectionnée précédemment, la re-sélectionner
                    const selectedCategoryId = '{{ old('category_id') }}';
                    if (selectedCategoryId) {
                        categorySelect.value = selectedCategoryId;
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des catégories:', error);
                    categorySelect.innerHTML = '<option value="">Erreur lors du chargement des catégories</option>';
                });
        }
        
        // Fonction pour mettre à jour le champ caché restaurant_id et charger les catégories
        function updateRestaurantAndCategories(restaurantId) {
            restaurantIdInput.value = restaurantId;
            loadCategories(restaurantId);
        }
        
        // Exposer la fonction updateRestaurantAndCategories globalement pour qu'elle soit accessible depuis l'attribut onchange
        window.updateRestaurantAndCategories = updateRestaurantAndCategories;
        
        // Vérifier le formulaire avant soumission
        document.getElementById('itemForm').addEventListener('submit', function(e) {
            // Vérifier que la catégorie est sélectionnée
            if (!categorySelect.value) {
                e.preventDefault();
                categorySelect.classList.add('is-invalid');
                return false;
            }
            
            return true;
        });
        
        // Charger les catégories au chargement de la page si un restaurant est sélectionné
        if (restaurantSelect.value) {
            updateRestaurantAndCategories(restaurantSelect.value);
        }
        
        // Si une catégorie était pré-sélectionnée (depuis l'URL par exemple)
        const selectedCategoryId = '{{ $selectedCategoryId ?? "" }}';
        if (selectedCategoryId) {
            // Trouver le restaurant de cette catégorie et le sélectionner
            // Cette partie nécessiterait une logique supplémentaire
        }
    });
</script>
@endsection
