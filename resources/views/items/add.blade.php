@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Items /</span> Ajouter un item</h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <h5 class="card-header">Création d'un item</h5>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    <form action="{{ url('/items/store-direct') }}" method="POST" class="mb-3">
                        @csrf
                        
                        @if(request()->has('restaurant_id'))
                            <input type="hidden" id="selected_restaurant_id" name="selected_restaurant_id" value="{{ request('restaurant_id') }}">
                        @endif

                        @if(auth()->user()->isRestaurateur() && auth()->user()->restaurants->count() > 1)
                            <div class="mb-3">
                                <label for="restaurant_selector" class="form-label">Restaurant</label>
                                <select id="restaurant_selector" class="form-select @error('restaurant_id') is-invalid @enderror">
                                    <option value="">Choisir un restaurant</option>
                                    @foreach(auth()->user()->restaurants as $restaurant)
                                        <option value="{{ $restaurant->id }}" {{ (request('restaurant_id') == $restaurant->id) ? 'selected' : '' }}>{{ $restaurant->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom de l'item</label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Entrez le nom de l'item" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" placeholder="Entrez une description de l'item" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="cost" class="form-label">Coût (centimes)</label>
                                <input type="number" id="cost" name="cost" class="form-control @error('cost') is-invalid @enderror" placeholder="Coût en centimes" value="{{ old('cost') }}">
                                <small class="text-muted">Exemple : 500 pour 5,00 €</small>
                                @error('cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="price" class="form-label">Prix (centimes)</label>
                                <input type="number" id="price" name="price" class="form-control @error('price') is-invalid @enderror" placeholder="Prix en centimes" value="{{ old('price') }}" required>
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
                                    <option value="{{ $category->id }}" data-restaurant="{{ $category->restaurant_id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }} ({{ $category->restaurant->name }})</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Actif</label>
                            </div>
                            <small class="text-muted">Si coché, l'item sera visible et commandable par les clients</small>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bx bx-save me-1"></i> Enregistrer
                            </button>
                            <a href="{{ route('items.index', request()->has('restaurant_id') ? ['restaurant_id' => request('restaurant_id')] : []) }}" class="btn btn-outline-secondary">
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
        const restaurantSelector = document.getElementById('restaurant_selector');
        const categorySelect = document.getElementById('category_id');
        const selectedRestaurantId = document.getElementById('selected_restaurant_id');
        
        function filterCategories() {
            let restaurantId = restaurantSelector ? restaurantSelector.value : (selectedRestaurantId ? selectedRestaurantId.value : null);
            
            if (restaurantId) {
                // Masquer toutes les options qui ne correspondent pas au restaurant sélectionné
                Array.from(categorySelect.options).forEach(option => {
                    if (option.value === '') return; // Ignorer l'option par défaut
                    
                    const optionRestaurantId = option.getAttribute('data-restaurant');
                    option.style.display = (optionRestaurantId === restaurantId) ? '' : 'none';
                });
                
                // Sélectionner la première option visible si aucune n'est sélectionnée
                const visibleOptions = Array.from(categorySelect.options).filter(opt => opt.style.display !== 'none' && opt.value !== '');
                if (visibleOptions.length > 0 && !Array.from(categorySelect.options).some(opt => opt.selected && opt.style.display !== 'none' && opt.value !== '')) {
                    visibleOptions[0].selected = true;
                }
            } else {
                // Afficher toutes les options si aucun restaurant n'est sélectionné
                Array.from(categorySelect.options).forEach(option => {
                    option.style.display = '';
                });
            }
        }
        
        // Filtrer les catégories au chargement de la page
        filterCategories();
        
        // Ajouter un écouteur d'événement pour le changement de restaurant
        if (restaurantSelector) {
            restaurantSelector.addEventListener('change', filterCategories);
        }
    });
</script>
@endsection
@endsection
