@extends('layouts.main')

@php
    use Illuminate\Support\Str;
@endphp

@section('styles')
<style>
    .menu-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .menu-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .menu-card.selected {
        border-color: #696cff;
        box-shadow: 0 5px 15px rgba(105, 108, 255, 0.4);
    }
    .item-added {
        animation: pulse 0.5s;
    }
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    .add-menu-btn {
        transition: all 0.2s ease;
    }
    .add-menu-btn:hover {
        background-color: #696cff;
        color: white;
    }
    .menu-category {
        border-left: 3px solid #e9ecef;
        padding-left: 10px;
        margin-left: 5px;
    }
    .menu-items-list {
        max-height: 250px;
        overflow-y: auto;
        scrollbar-width: thin;
    }
    .menu-items-list::-webkit-scrollbar {
        width: 6px;
    }
    .menu-items-list::-webkit-scrollbar-thumb {
        background-color: #696cff50;
        border-radius: 6px;
    }
</style>
@endsection

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Commandes /</span> Nouvelle commande</h4>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Commander chez {{ $restaurant->name }}</h5>
            <a href="{{ route('restaurants.show', $restaurant->id) }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour au restaurant
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
                @csrf
                <input type="hidden" name="restaurant_id" value="{{ $restaurant->id }}">
                
                <!-- Section des menus -->
                @php
                    // Récupérer les menus actifs du restaurant
                    $restaurantMenus = \App\Models\Menu::where('restaurant_id', $restaurant->id)
                        ->where('is_active', 1)
                        ->with('items')
                        ->get();
                        
                    // Filtrer les menus pour ne garder que ceux dont tous les plats sont disponibles
                    $menus = [];
                    foreach ($restaurantMenus as $menu) {
                        // Vérifier si tous les plats du menu sont disponibles
                        $allItemsAvailable = true;
                        foreach ($menu->items as $item) {
                            if (!$item->is_available) {
                                $allItemsAvailable = false;
                                break;
                            }
                        }
                        
                        // Si tous les plats sont disponibles, ajouter le menu à la liste des menus valides
                        if ($allItemsAvailable && $menu->items->count() > 0) {
                            $menus[] = $menu;
                        }
                    }
                    $menus = collect($menus);
                @endphp
                
                @if(count($menus) > 0)
                    <div class="card mb-4 border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bx bx-food-menu me-2"></i>Menus complets</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($menus as $menu)
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100 shadow-sm menu-card">
                                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                                <h5 class="card-title mb-0">{{ $menu->name }}</h5>
                                                <span class="badge bg-light text-primary rounded-pill fs-6 fw-bold">{{ number_format($menu->price, 2, ',', ' ') }} €</span>
                                            </div>
                                            <div class="card-body">
                                                <div class="menu-description">
                                                    @if($menu->description)
                                                        <p class="text-muted">{{ $menu->description }}</p>
                                                        <hr>
                                                    @endif
                                                    <h6 class="fw-bold text-primary mb-2">
                                                        <i class="bx bx-food-menu me-1"></i> Contenu du menu ({{ $menu->items->count() }} plats)
                                                    </h6>
                                                </div>
                                                
                                                <div class="menu-items-list">
                                                    @php
                                                        $itemsByCategory = $menu->items->groupBy('category_id');
                                                    @endphp
                                                    
                                                    @foreach($itemsByCategory as $categoryId => $items)
                                                        @php
                                                            $category = \App\Models\Category::find($categoryId);
                                                            $categoryName = $category ? $category->name : 'Autres';
                                                        @endphp
                                                        <div class="menu-category mb-2">
                                                            <span class="text-muted small">{{ $categoryName }}</span>
                                                            <ul class="list-group list-group-flush">
                                                                @foreach($items as $item)
                                                                    <li class="list-group-item px-0 py-1 border-0 d-flex align-items-center">
                                                                        <i class="bx bx-check-circle text-success me-2"></i>
                                                                        <div>
                                                                            <span class="fw-semibold">{{ $item->name }}</span>
                                                                            @if($item->description)
                                                                                <p class="text-muted small mb-0">{{ Str::limit($item->description, 60) }}</p>
                                                                            @endif
                                                                        </div>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                
                                                <div class="mt-3 pt-3 border-top">
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-primary text-white">
                                                            <i class="bx bx-cart-add me-1"></i> Quantité
                                                        </span>
                                                        <input type="number" min="0" value="0" class="form-control menu-quantity" 
                                                            id="menu-{{ $menu->id }}" 
                                                            name="menus[{{ $menu->id }}][quantity]" 
                                                            data-price="{{ $menu->price * 100 }}">
                                                        <button type="button" class="btn btn-outline-primary add-menu-btn" data-menu-id="{{ $menu->id }}">
                                                            <i class="bx bx-plus"></i> Ajouter
                                                        </button>
                                                        <input type="hidden" name="menus[{{ $menu->id }}][id]" value="{{ $menu->id }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Section des plats individuels -->
                @if(count($categories) > 0)
                    <div class="card mb-4 border-info">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bx bx-dish me-2"></i>Plats individuels</h5>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="categoryAccordion">
                                @foreach($categories as $index => $category)
                                    @if(count($category->items) > 0)
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="heading{{ $category->id }}">
                                                <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $category->id }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $category->id }}">
                                                    <i class="bx bx-category-alt me-2"></i> {{ $category->name }} <span class="badge bg-info ms-2">{{ $category->items->count() }} plat(s)</span>
                                                </button>
                                            </h2>
                                            <div id="collapse{{ $category->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="heading{{ $category->id }}" data-bs-parent="#categoryAccordion">
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        @foreach($category->items as $item)
                                                            @if($item->menu_id === null && $item->is_available)
                                                                <div class="col-md-4 mb-3">
                                                                    <div class="card h-100 shadow-sm">
                                                                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                                            <h6 class="card-title mb-0">{{ $item->name }}</h6>
                                                                            <span class="badge bg-info rounded-pill">{{ number_format($item->price, 2, ',', ' ') }} €</span>
                                                                        </div>
                                                                        <div class="card-body">
                                                                            <p class="card-text">{{ Str::limit($item->description, 100) }}</p>
                                                                            <div class="input-group mt-3">
                                                                                <span class="input-group-text">Quantité</span>
                                                                                <input type="number" min="0" value="0" class="form-control item-quantity" 
                                                                                    id="item-{{ $item->id }}" 
                                                                                    name="items[{{ $item->id }}][quantity]" 
                                                                                    data-price="{{ $item->price * 100 }}">
                                                                                <input type="hidden" name="items[{{ $item->id }}][id]" value="{{ $item->id }}">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Récapitulatif de la commande -->
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bx bx-cart me-2"></i>Récapitulatif de votre commande</h5>
                    </div>
                    <div class="card-body">
                        <div id="orderSummary" class="card-text mb-4 p-3 border rounded bg-light">
                            <div class="text-center text-muted">Votre panier est vide</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes spéciales</label>
                            <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Instructions spéciales, allergies, préférences de livraison, etc."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-lg mt-3 w-100" id="submitOrder" disabled>
                            <i class="bx bx-check-circle me-1"></i> Confirmer et passer la commande
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Éléments du DOM
        const quantityInputs = document.querySelectorAll('.item-quantity');
        const menuQuantityInputs = document.querySelectorAll('.menu-quantity');
        const orderSummary = document.getElementById('orderSummary');
        const submitButton = document.getElementById('submitOrder');
        const addMenuButtons = document.querySelectorAll('.add-menu-btn');
        
        // Créer un mappage des plats appartenant à chaque menu
        const menuItemsMap = {};
        const itemMenuMap = {};
        
        // Initialiser les mappages
        @foreach($menus as $menu)
            menuItemsMap[{{ $menu->id }}] = [
                @foreach($menu->items as $item)
                    {{ $item->id }},
                @endforeach
            ];
            
            // Pour chaque plat, enregistrer à quel menu il appartient
            @foreach($menu->items as $item)
                itemMenuMap[{{ $item->id }}] = {{ $menu->id }};
            @endforeach
        @endforeach
        
        function updateOrderSummary() {
            let totalItems = 0;
            let totalPrice = 0;
            let summaryHTML = '';
            
            // Collecter les menus actifs et les plats actifs
            const activeMenus = [];
            const activeItems = [];
            
            // Collecter les menus actifs (quantité > 0)
            menuQuantityInputs.forEach(input => {
                const quantity = parseInt(input.value);
                const menuId = parseInt(input.id.replace('menu-', ''));
                if (quantity > 0) {
                    activeMenus.push(menuId);
                }
            });
            
            // Collecter les plats actifs (quantité > 0)
            quantityInputs.forEach(input => {
                const quantity = parseInt(input.value);
                const itemId = parseInt(input.id.replace('item-', ''));
                if (quantity > 0) {
                    activeItems.push(itemId);
                }
            });
            
            // Vérifier les contraintes et mettre à jour l'interface
            // 1. Si un menu est sélectionné, désactiver tous ses plats individuels
            // 2. Si un plat est sélectionné, désactiver tout menu contenant ce plat
            
            // Réinitialiser tous les inputs (enlever les disable)
            quantityInputs.forEach(input => {
                input.disabled = false;
                const itemCard = input.closest('.card');
                if (itemCard) {
                    itemCard.classList.remove('bg-light', 'text-muted');
                    const disabledMessage = itemCard.querySelector('.disabled-message');
                    if (disabledMessage) {
                        disabledMessage.remove();
                    }
                }
            });
            
            menuQuantityInputs.forEach(input => {
                input.disabled = false;
                const menuCard = input.closest('.card');
                if (menuCard) {
                    menuCard.classList.remove('bg-light', 'text-muted');
                    const disabledMessage = menuCard.querySelector('.disabled-message');
                    if (disabledMessage) {
                }
            });
            
            // Mettre à jour le récapitulatif
            // Ajouter les menus au récapitulatif
            menuQuantityInputs.forEach(input => {
                const quantity = parseInt(input.value);
                if (quantity > 0) {
                    const price = parseInt(input.dataset.price);
                    const itemTotal = price * quantity;
                    const itemName = input.closest('.card').querySelector('.card-title').textContent;
                    
                    totalItems += quantity;
                    totalPrice += itemTotal;
                    
                    summaryHTML += `<div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div>
                            <i class="bx bx-food-menu text-primary me-1"></i>
                            <strong class="text-primary">${quantity} x Menu ${itemName}</strong>
                        </div>
                        <span class="badge bg-primary rounded-pill fs-6">${(itemTotal / 100).toFixed(2).replace('.', ',')} €</span>
                    </div>`;
                }
            });
            
            // Ajouter les plats individuels au récapitulatif
            quantityInputs.forEach(input => {
                const quantity = parseInt(input.value);
                if (quantity > 0) {
                    const price = parseInt(input.dataset.price);
                    const itemTotal = price * quantity;
                    const itemName = input.closest('.card').querySelector('.card-title').textContent;
                    
                    totalItems += quantity;
                    totalPrice += itemTotal;
                    
                    summaryHTML += `<div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div>
                            <i class="bx bx-dish text-info me-1"></i>
                            <span>${quantity} x ${itemName}</span>
                        </div>
                        <span class="badge bg-info rounded-pill fs-6">${(itemTotal / 100).toFixed(2).replace('.', ',')} €</span>
                    </div>`;
                }
            });
            
            if (totalItems > 0) {
                summaryHTML += `
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <div>
                        <strong class="fs-5">Total de la commande:</strong>
                        <div class="text-muted small">${totalItems} article(s)</div>
                    </div>
                    <span class="badge bg-success rounded-pill fs-5">${(totalPrice / 100).toFixed(2).replace('.', ',')} €</span>
                </div>`;
                orderSummary.innerHTML = summaryHTML;
                submitButton.disabled = false;
            } else {
                orderSummary.innerHTML = '<div class="text-center text-muted">Votre panier est vide</div>';
                submitButton.disabled = true;
            }
        }
        
        // Fonction pour ajouter un menu au panier avec animation
        function addMenuToCart(menuId) {
            const menuCard = document.querySelector(`.menu-card:has([id="menu-${menuId}"])`);
            const quantityInput = document.getElementById(`menu-${menuId}`);
            
            // Incrémenter la quantité
            quantityInput.value = parseInt(quantityInput.value) + 1;
            
            // Ajouter une classe pour l'animation
            menuCard.classList.add('item-added');
            setTimeout(() => {
                menuCard.classList.remove('item-added');
            }, 500);
            
            // Mettre à jour le récapitulatif
            updateOrderSummary();
            
            // Faire défiler jusqu'au récapitulatif
            orderSummary.scrollIntoView({ behavior: 'smooth' });
        }
        
        // Gérer les boutons "Ajouter" pour les menus
        addMenuButtons.forEach(button => {
            button.addEventListener('click', function() {
                const menuId = this.getAttribute('data-menu-id');
                addMenuToCart(menuId);
            });
        });
        
        // Écouter les changements de quantité pour les plats individuels
        quantityInputs.forEach(input => {
            input.addEventListener('change', updateOrderSummary);
            input.addEventListener('input', updateOrderSummary);
        });
        
        // Écouter les changements de quantité pour les menus
        menuQuantityInputs.forEach(input => {
            input.addEventListener('change', updateOrderSummary);
            input.addEventListener('input', updateOrderSummary);
            
            // Ajouter un événement pour mettre à jour visuellement la carte quand la quantité change
            input.addEventListener('change', function() {
                const menuCard = this.closest('.menu-card');
                if (parseInt(this.value) > 0) {
                    menuCard.classList.add('selected');
                } else {
                    menuCard.classList.remove('selected');
                }
            });
        });
        
        // Ajouter des tooltips pour améliorer l'expérience utilisateur
        const menuCards = document.querySelectorAll('.menu-card');
        menuCards.forEach(card => {
            card.setAttribute('title', 'Cliquez sur Ajouter pour commander ce menu');
        });
        
        // Initialiser le récapitulatif
        updateOrderSummary();
    });
</script>
@endsection
