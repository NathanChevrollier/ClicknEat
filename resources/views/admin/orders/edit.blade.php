@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Administration / Commandes /</span> Édition de la commande
    </h4>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Modifier la commande #{{ $order->id }}</h5>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Client -->
                <div class="mb-3">
                    <label for="user_id" class="form-label">Client</label>
                    <select id="user_id" name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                        @foreach(\App\Models\User::where('role', 'client')->get() as $user)
                            <option value="{{ $user->id }}" {{ $order->user_id == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Restaurant (non modifiable car lié à la réservation) -->
                <div class="mb-3">
                    <label for="restaurant_id" class="form-label">Restaurant</label>
                    <input type="hidden" name="restaurant_id" value="{{ $order->restaurant_id }}">
                    <input type="text" class="form-control" value="{{ $order->restaurant->name }}" readonly>
                    <small class="text-muted">Le restaurant n'est pas modifiable car cette commande est liée à une réservation.</small>
                    @error('restaurant_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Statut -->
                <div class="mb-3">
                    <label for="status" class="form-label">Statut</label>
                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                        <option value="preparing" {{ $order->status == 'preparing' ? 'selected' : '' }}>En préparation</option>
                        <option value="ready" {{ $order->status == 'ready' ? 'selected' : '' }}>Prête</option>
                        <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Terminée</option>
                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Annulée</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Adresse de livraison -->
                <div class="mb-3">
                    <label for="delivery_address" class="form-label">Adresse de livraison</label>
                    <textarea id="delivery_address" name="delivery_address" class="form-control @error('delivery_address') is-invalid @enderror" rows="3">{{ $order->delivery_address }}</textarea>
                    @error('delivery_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ $order->notes }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Menus -->
                <div class="mb-3">
                    <label class="form-label">Menus</label>
                    <div id="menus-container" class="mb-2">
                        @foreach($order->menus ?? [] as $menu)
                        <div class="row mb-2 menu-row">
                            <div class="col-md-6">
                                <select name="menus[{{ $menu->id }}][id]" class="form-select" disabled>
                                    <option value="{{ $menu->id }}" selected>{{ $menu->name }} ({{ number_format($menu->pivot->price / 100, 2) }} €)</option>
                                </select>
                                <input type="hidden" name="menus[{{ $menu->id }}][id]" value="{{ $menu->id }}">
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="menus[{{ $menu->id }}][quantity]" class="form-control" placeholder="Quantité" min="1" value="{{ $menu->pivot->quantity }}" required>
                            </div>
                            <div class="col-md-3">
                                <a href="javascript:void(0);" class="btn btn-danger" onclick="this.closest('.menu-row').remove();">
                                    <i class="bx bx-trash"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <a href="javascript:void(0);" onclick="addMenu()" class="btn btn-success btn-lg">
                        <i class="bx bx-plus me-1"></i> Ajouter un menu
                    </a>
                </div>

                <!-- Plats -->
                <div class="mb-3">
                    <label class="form-label">Plats</label>
                    <div id="items-container" class="mb-2">
                        @foreach($order->items as $item)
                        <div class="row mb-2 item-row">
                            <div class="col-md-6">
                                <select name="items[{{ $item->id }}][id]" class="form-select" disabled>
                                    <option value="{{ $item->id }}" selected>{{ $item->name }} ({{ number_format($item->pivot->price / 100, 2) }} €)</option>
                                </select>
                                <input type="hidden" name="items[{{ $item->id }}][id]" value="{{ $item->id }}">
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="items[{{ $item->id }}][quantity]" class="form-control" placeholder="Quantité" min="1" value="{{ $item->pivot->quantity }}" required>
                            </div>
                            <div class="col-md-3">
                                <a href="javascript:void(0);" class="btn btn-danger" onclick="this.closest('.item-row').remove();">
                                    <i class="bx bx-trash"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <a href="javascript:void(0);" onclick="addItem()" class="btn btn-info btn-lg">
                        <i class="bx bx-plus me-1"></i> Ajouter un plat
                    </a>
                </div>

                <button type="submit" class="btn btn-primary">Mettre à jour la commande</button>
            </form>
        </div>
    </div>
</div>
@endsection

<!-- Données pour le JavaScript -->
<script>
    // Pré-charger uniquement les plats disponibles et menus contenant des plats disponibles
    const restaurantsData = {
        {{ $order->restaurant_id }}: {
            menus: [
                @foreach($availableMenus as $menu)
                    {
                        id: {{ $menu->id }},
                        name: "{{ addslashes($menu->name) }}",
                        price: {{ $menu->price }}
                    },
                @endforeach
            ],
            items: [
                @foreach($availableItems as $item)
                    {
                        id: {{ $item->id }},
                        name: "{{ addslashes($item->name) }}",
                        price: {{ $item->price }}
                    },
                @endforeach
            ]
        }
    };
</script>

@section('scripts')
<script>
    // Variables globales
    let itemCount = 0;
    let menuCount = 0;
    let restaurantId = {{ $order->restaurant_id }};
    let itemsContainer;
    let menusContainer;

    // Initialisation au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        itemsContainer = document.getElementById('items-container');
        menusContainer = document.getElementById('menus-container');
    });

    // Fonction pour ajouter un plat (accessible globalement)
    function addItem() {
        console.log('Fonction addItem appelée');
        
        // Vérifier si des plats sont disponibles
        const items = restaurantsData[restaurantId]?.items || [];
        if (items.length === 0) {
            alert('Aucun plat disponible pour ce restaurant.');
            return;
        }
        
        // Créer une liste déroulante temporaire
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = `
            <div class="modal fade" id="tempItemModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Ajouter un plat</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="tempItemSelect" class="form-label">Sélectionner un plat</label>
                                <select id="tempItemSelect" class="form-select">
                                    <option value="">Choisir un plat</option>
                                    ${items.map(item => `<option value="${item.id}">${item.name} (${(item.price/100).toFixed(2)} €)</option>`).join('')}
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="tempItemQuantity" class="form-label">Quantité</label>
                                <input type="number" id="tempItemQuantity" class="form-control" min="1" value="1">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="button" id="confirmItemSelect" class="btn btn-primary">Ajouter</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(tempDiv);
        const modal = new bootstrap.Modal(document.getElementById('tempItemModal'));
        const tempItemSelect = document.getElementById('tempItemSelect');
        
        modal.show();
        
        // Gérer la confirmation
        document.getElementById('confirmItemSelect').addEventListener('click', function() {
            const selectedItemId = tempItemSelect.value;
            const selectedItemText = tempItemSelect.options[tempItemSelect.selectedIndex].text;
            const quantity = parseInt(document.getElementById('tempItemQuantity').value, 10);
            
            if (!selectedItemId) {
                alert('Veuillez sélectionner un plat');
                return;
            }
            
            if (isNaN(quantity) || quantity < 1) {
                alert('La quantité doit être un nombre positif');
                return;
            }
            
            // Vérifier si ce plat existe déjà dans la commande (existants ou nouveaux plats)
            // D'abord vérifier dans les plats existants
            let existingItems = document.querySelectorAll('input[name^="items["]');
            let itemExists = false;
            
            for (let i = 0; i < existingItems.length; i++) {
                const inputHidden = existingItems[i];
                if (inputHidden.value === selectedItemId) {
                    // Le plat existe déjà, incrémenter sa quantité
                    const quantityInput = inputHidden.closest('.item-row').querySelector('input[type="number"]');
                    const newQuantity = parseInt(quantityInput.value, 10) + quantity;
                    quantityInput.value = newQuantity;
                    
                    // S'assurer que l'attribut value est également mis à jour
                    quantityInput.setAttribute('value', newQuantity);
                    
                    // Déclencher un événement de changement
                    const event = new Event('change', { bubbles: true });
                    quantityInput.dispatchEvent(event);
                    
                    itemExists = true;
                    break;
                }
            }
            
            // Si pas trouvé dans les plats existants, vérifier dans les nouveaux plats
            if (!itemExists) {
                const newItemsSelects = document.querySelectorAll('.item-select');
                
                for (let i = 0; i < newItemsSelects.length; i++) {
                    if (newItemsSelects[i].value === selectedItemId) {
                        // Le plat existe déjà dans les nouveaux plats, incrémenter sa quantité
                        const quantityInput = newItemsSelects[i].closest('.item-row').querySelector('input[type="number"]');
                        const newQuantity = parseInt(quantityInput.value, 10) + quantity;
                        quantityInput.value = newQuantity;
                        
                        // S'assurer que l'attribut value est également mis à jour
                        quantityInput.setAttribute('value', newQuantity);
                        
                        // Déclencher un événement de changement
                        const event = new Event('change', { bubbles: true });
                        quantityInput.dispatchEvent(event);
                        
                        itemExists = true;
                        break;
                    }
                }
            }
            
            // Si le plat n'existe nulle part, l'ajouter comme nouveau
            if (!itemExists) {
                const row = document.createElement('div');
                row.className = 'row mb-2 item-row';
                row.innerHTML = `
                    <div class="col-md-6">
                        <select name="new_items[${selectedItemId}][id]" class="form-select item-select" required>
                            <option value="${selectedItemId}" selected>${selectedItemText}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="new_items[${selectedItemId}][quantity]" class="form-control" placeholder="Quantité" min="1" value="${quantity}" required>
                    </div>
                    <div class="col-md-3">
                        <a href="javascript:void(0);" class="btn btn-danger" onclick="this.closest('.item-row').remove();">
                            <i class="bx bx-trash"></i>
                        </a>
                    </div>
                `;
                
                itemsContainer.appendChild(row);
                itemCount++;
            }
            
            modal.hide();
            // Supprimer la modal après utilisation
            tempDiv.addEventListener('hidden.bs.modal', function () {
                tempDiv.remove();
            });
        });

    }

    // Fonction pour mettre à jour les champs de nom pour les menus sélectionnés
    function updateMenuName(selectElement) {
        const menuId = selectElement.value;
        if (!menuId) return;
        
        // Mettre à jour les noms des champs pour utiliser l'ID du menu comme clé
        const quantityInput = selectElement.closest('.menu-row').querySelector('input[type="number"]');
        
        // Mise à jour des attributs name
        selectElement.setAttribute('name', `new_menus[${menuId}][id]`);
        quantityInput.setAttribute('name', `new_menus[${menuId}][quantity]`);
    }
    
    // Fonction pour ajouter un menu (accessible globalement)
    function addMenu() {
        console.log('Fonction addMenu appelée');
        
        // Vérifier si des menus sont disponibles
        const menus = restaurantsData[restaurantId]?.menus || [];
        if (menus.length === 0) {
            alert('Aucun menu disponible pour ce restaurant. Veuillez d\'abord en ajouter dans la gestion des menus.');
            return;
        }

        const row = document.createElement('div');
        row.className = 'row mb-2 menu-row';
        
        // Créer une liste déroulante des menus disponibles
        let menusOptions = menus.map(menu => 
            `<option value="${menu.id}" data-price="${menu.price}">${menu.name} (${(menu.price/100).toFixed(2)} €)</option>`
        ).join('');
        
        row.innerHTML = `
            <div class="col-md-6">
                <select name="new_menus[][id]" class="form-select menu-select" required onchange="updateMenuName(this)">
                    <option value="">Sélectionner un menu</option>
                    ${menusOptions}
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" name="new_menus[][quantity]" class="form-control" placeholder="Quantité" min="1" value="1" required>
            </div>
            <div class="col-md-3">
                <a href="javascript:void(0);" class="btn btn-danger" onclick="this.closest('.menu-row').remove();">
                    <i class="bx bx-trash"></i>
                </a>
            </div>
        `;
        
        menusContainer.appendChild(row);
        menuCount++;
    }
</script>
@endsection
