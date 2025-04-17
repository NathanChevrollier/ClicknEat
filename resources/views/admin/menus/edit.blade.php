@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Modifier le menu</h3>
            <a href="{{ route('admin.menus.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.menus.update', $menu->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="restaurant_id" class="form-label">Restaurant</label>
                    <select class="form-select @error('restaurant_id') is-invalid @enderror" id="restaurant_id" name="restaurant_id" required>
                        <option value="">Sélectionnez un restaurant</option>
                        @foreach($restaurants as $restaurant)
                            <option value="{{ $restaurant->id }}" {{ old('restaurant_id', $menu->restaurant_id) == $restaurant->id ? 'selected' : '' }}>
                                {{ $restaurant->name }} ({{ $restaurant->user->name }})
                            </option>
                        @endforeach
                    </select>
                    @error('restaurant_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="name" class="form-label">Nom du menu</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $menu->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $menu->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="price" class="form-label">Prix (€)</label>
                    <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $menu->price / 100) }}" step="0.01" min="0" required>
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3" id="items-container">
                    <label class="form-label">Plats inclus dans le menu</label>
                    <div class="alert alert-info" id="no-items-message">
                        <i class="bx bx-info-circle me-1"></i> Chargement des plats...
                    </div>
                    <div id="items-list" class="d-none">
                        <div class="row">
                            @foreach($items as $item)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="items[]" value="{{ $item->id }}" id="item-{{ $item->id }}" {{ in_array($item->id, old('items', $selectedItems)) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="item-{{ $item->id }}">
                                            {{ $item->name }} - {{ number_format($item->price / 100, 2, ',', ' ') }} €
                                            <small class="text-muted d-block">{{ $item->category->name }}</small>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @error('items')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bx bx-save me-1"></i> Enregistrer
                    </button>
                    <a href="{{ route('admin.menus.index') }}" class="btn btn-secondary">
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
        const noItemsMessage = document.getElementById('no-items-message');
        const itemsList = document.getElementById('items-list');
        const selectedItems = @json($selectedItems);
        
        // Fonction pour charger les plats d'un restaurant
        function loadItems(restaurantId) {
            // Réinitialiser le message
            noItemsMessage.textContent = 'Chargement des plats...';
            noItemsMessage.classList.remove('d-none');
            itemsList.classList.add('d-none');
            
            if (!restaurantId) {
                noItemsMessage.textContent = 'Sélectionnez d\'abord un restaurant pour voir les plats disponibles.';
                return;
            }
            
            // Requête AJAX pour récupérer les plats du restaurant
            fetch(`/api/restaurants/${restaurantId}/items`)
                .then(response => response.json())
                .then(data => {
                    // Mettre à jour la liste des plats
                    const itemsContainer = document.getElementById('items-list');
                    itemsContainer.innerHTML = '';
                    
                    if (data.length === 0) {
                        noItemsMessage.textContent = 'Aucun plat disponible pour ce restaurant.';
                        return;
                    }
                    
                    const row = document.createElement('div');
                    row.className = 'row';
                    
                    data.forEach(item => {
                        const col = document.createElement('div');
                        col.className = 'col-md-6 mb-2';
                        col.innerHTML = `
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="items[]" value="${item.id}" id="item-${item.id}" ${selectedItems.includes(item.id) ? 'checked' : ''}>
                                <label class="form-check-label" for="item-${item.id}">
                                    ${item.name} - ${(item.price / 100).toFixed(2).replace('.', ',')} €
                                    <small class="text-muted d-block">${item.category.name}</small>
                                </label>
                            </div>
                        `;
                        row.appendChild(col);
                    });
                    
                    itemsContainer.appendChild(row);
                    noItemsMessage.classList.add('d-none');
                    itemsList.classList.remove('d-none');
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des plats:', error);
                    noItemsMessage.textContent = 'Erreur lors du chargement des plats.';
                });
        }
        
        // Événement de changement du restaurant
        restaurantSelect.addEventListener('change', function() {
            loadItems(this.value);
        });
        
        // Charger les plats du restaurant sélectionné au chargement de la page
        if (restaurantSelect.value) {
            // Si la page est déjà chargée avec des plats, les afficher
            if (document.querySelectorAll('#items-list .form-check').length > 0) {
                noItemsMessage.classList.add('d-none');
                itemsList.classList.remove('d-none');
            } else {
                loadItems(restaurantSelect.value);
            }
        } else {
            noItemsMessage.textContent = 'Sélectionnez d\'abord un restaurant pour voir les plats disponibles.';
        }
    });
</script>
@endsection
