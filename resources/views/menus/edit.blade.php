@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Menus /</span> Modifier un menu</h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <h5 class="card-header">Modification du menu</h5>
                <div class="card-body">
                    <form action="{{ route('restaurants.menus.update', [$restaurant->id, $menu->id]) }}" method="POST" class="mb-3">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom du menu</label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Entrez le nom du menu" value="{{ old('name', $menu->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" placeholder="Entrez une description du menu" rows="3">{{ old('description', $menu->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="price" class="form-label">Prix (u20ac)</label>
                            <input type="number" id="price" name="price" class="form-control @error('price') is-invalid @enderror" placeholder="0.00" value="{{ old('price', $menu->price) }}" step="0.01" min="0" required>
                            <div class="form-text">Exemple : 19.99 pour 19,99 u20ac</div>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Restaurant</label>
                            <input type="text" class="form-control" value="{{ $restaurant->name }}" disabled>
                        </div>
                        
                        <div class="mb-3" id="items-container">
                            <label class="form-label">Plats inclus dans le menu</label>
                            <div id="items-list">
                                @if(isset($items) && count($items) > 0)
                                    <div class="row">
                                        @foreach($items as $item)
                                            <div class="col-md-6 col-lg-4 mb-2">
                                                <div class="form-check custom-option custom-option-basic">
                                                    <input class="form-check-input" type="checkbox" name="items[]" id="item_{{ $item->id }}" value="{{ $item->id }}" {{ (is_array(old('items')) && in_array($item->id, old('items'))) || (old('items') === null && in_array($item->id, $selectedItems)) ? 'checked' : '' }}>
                                                    <label class="form-check-label custom-option-content" for="item_{{ $item->id }}">
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <span>{{ $item->name }}</span>
                                                            <span class="text-primary">{{ number_format($item->price, 2, ',', ' ') }} u20ac</span>
                                                        </div>
                                                        <small>{{ $item->category->name }}</small>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="bx bx-error-circle me-1"></i> Aucun plat disponible pour ce restaurant.
                                    </div>
                                @endif
                            </div>
                            @error('items')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bx bx-save me-1"></i> Enregistrer
                            </button>
                            <a href="{{ route('restaurants.menus.index', $restaurant->id) }}" class="btn btn-outline-secondary">
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

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const itemsContainer = document.getElementById('items-container');
        const itemsList = document.getElementById('items-list');
        const selectedItems = @json($selectedItems);
        
        // Fonction pour charger les plats en fonction du restaurant sélectionné
        function loadItems(restaurantId) {
            if (!restaurantId) {
                itemsList.innerHTML = '<div class="alert alert-info"><i class="bx bx-info-circle me-1"></i> Veuillez sélectionner un restaurant.</div>';
                return;
            }
            
            // Affichage d'un message de chargement
            itemsList.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div></div>';
            
            // Reqête AJAX pour récupérer les plats du restaurant
            fetch(`/api/restaurants/${restaurantId}/items`)
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        itemsList.innerHTML = '<div class="alert alert-warning"><i class="bx bx-error-circle me-1"></i> Aucun plat disponible pour ce restaurant. Veuillez d\'abord <a href="/items/create?restaurant_id=' + restaurantId + '">créer des plats</a>.</div>';
                    } else {
                        // Création des cases à cocher pour chaque plat
                        let html = '<div class="row">';
                        data.forEach(item => {
                            const isChecked = selectedItems.includes(item.id) ? 'checked' : '';
                            html += `
                                <div class="col-md-6 col-lg-4 mb-2">
                                    <div class="form-check custom-option custom-option-basic">
                                        <input class="form-check-input" type="checkbox" name="items[]" id="item_${item.id}" value="${item.id}" ${isChecked}>
                                        <label class="form-check-label custom-option-content" for="item_${item.id}">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>${item.name}</span>
                                                <span class="text-primary">${(item.price).toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}).replace('.', ',')} u20ac</span>
                                            </div>
                                            <small>${item.category.name}</small>
                                        </label>
                                    </div>
                                </div>
                            `;
                        });
                        html += '</div>';
                        itemsList.innerHTML = html;
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des plats:', error);
                    itemsList.innerHTML = '<div class="alert alert-danger"><i class="bx bx-error-circle me-1"></i> Erreur lors du chargement des plats.</div>';
                });
        }
        
        // Chargement des plats pour le restaurant actuel
        loadItems({{ $restaurant->id }});
    });
</script>
@endsection
