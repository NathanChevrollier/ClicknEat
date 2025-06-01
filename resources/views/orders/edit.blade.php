@extends('layouts.main')

@php
    use Illuminate\Support\Str;
@endphp

@section('main')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Modifier la commande #{{ $order->id }} - {{ $restaurant->name }}</h3>
        <div>
            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-secondary">Retour aux détails de la commande</a>
        </div>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('orders.update', $order->id) }}" method="POST" id="orderForm">
            @csrf
            @method('PUT')
            
            <!-- Section des menus -->
            @php
                $menus = \App\Models\Menu::where('restaurant_id', $restaurant->id)->with('items')->get();
                // Récupérer les menus associés à cette commande
                $orderMenus = [];
                foreach($order->items as $orderItem) {
                    if($orderItem->pivot->menu_id) {
                        if(!isset($orderMenus[$orderItem->pivot->menu_id])) {
                            $orderMenus[$orderItem->pivot->menu_id] = 0;
                        }
                        $orderMenus[$orderItem->pivot->menu_id]++;
                    }
                }
            @endphp
            
            @if(count($menus) > 0)
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bx bx-food-menu me-2"></i>Menus</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($menus as $menu)
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-primary">
                                        <div class="card-header bg-light">
                                            <h5 class="card-title mb-0">{{ $menu->name }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-text fw-bold text-primary">{{ number_format($menu->price, 2, ',', ' ') }} &euro;</p>
                                            <p class="card-text">Contient {{ $menu->items->count() }} plat(s) :</p>
                                            <ul class="ps-3">
                                                @foreach($menu->items as $item)
                                                    <li>{{ $item->name }}</li>
                                                @endforeach
                                            </ul>
                                            <div class="d-flex align-items-center mt-3">
                                                <label for="menu-{{ $menu->id }}" class="me-2">Quantité:</label>
                                                <input type="number" min="0" value="{{ isset($orderMenus[$menu->id]) ? $orderMenus[$menu->id] : 0 }}" class="form-control menu-quantity" 
                                                    id="menu-{{ $menu->id }}" 
                                                    name="menus[{{ $menu->id }}][quantity]" 
                                                    data-price="{{ $menu->price }}">
                                                <input type="hidden" name="menus[{{ $menu->id }}][id]" value="{{ $menu->id }}">
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
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="bx bx-dish me-2"></i>Plats individuels</h4>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="categoryAccordion">
                            @foreach($categories as $index => $category)
                                @if(count($category->items) > 0)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading{{ $category->id }}">
                                            <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $category->id }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">
                                                {{ $category->name }} ({{ $category->items->count() }} plat(s))
                                            </button>
                                        </h2>
                                        <div id="collapse{{ $category->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="heading{{ $category->id }}" data-bs-parent="#categoryAccordion">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    @foreach($category->items as $item)
                                                        @if($item->is_available)
                                                        <div class="col-md-4 mb-3">
                                                            <div class="card h-100">
                                                                <div class="card-body">
                                                                    <h5 class="card-title">{{ $item->name }}</h5>
                                                                    <p class="card-text">{{ Str::limit($item->description, 50) }}</p>
                                                                    <p class="card-text fw-bold">{{ number_format($item->price, 2, ',', ' ') }} €</p>
                                                                    <div class="d-flex align-items-center">
                                                                        <label for="item-{{ $item->id }}" class="me-2">Quantité:</label>
                                                                        <input type="number" min="0" value="{{ $orderItems[$item->id] ?? 0 }}" class="form-control item-quantity" 
                                                                            id="item-{{ $item->id }}" 
                                                                            name="items[{{ $item->id }}][quantity]" 
                                                                            data-price="{{ $item->price }}">
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

                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h4 class="mb-0"><i class="bx bx-note me-2"></i>Notes pour la commande</h4>
                    </div>
                    <div class="card-body">
                        <textarea name="notes" class="form-control" rows="3" placeholder="Informations spéciales pour votre commande (allergies, préférences, etc.)">{{ $order->notes }}</textarea>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="bx bx-cart me-2"></i>Récapitulatif de la commande</h4>
                    </div>
                    <div class="card-body">
                        <div id="orderSummary" class="alert alert-info">
                            Chargement du récapitulatif...
                        </div>
                        <button type="submit" class="btn btn-primary mt-3 w-100" id="submitOrder">
                            <i class="bx bx-check-circle me-1"></i> Mettre à jour la commande
                        </button>
                    </div>
                </div>
            @else
                <div class="alert alert-warning">
                    Ce restaurant n'a pas encore de menu disponible.
                </div>
            @endif
        </form>
    </div>
    <!-- /.card-body -->
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInputs = document.querySelectorAll('.item-quantity');
        const menuQuantityInputs = document.querySelectorAll('.menu-quantity');
        const orderSummary = document.getElementById('orderSummary');
        const submitButton = document.getElementById('submitOrder');
        
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
                        disabledMessage.remove();
                    }
                }
            });
            
            // Appliquer les contraintes pour les menus actifs
            activeMenus.forEach(menuId => {
                const menuItems = menuItemsMap[menuId] || [];
                
                // Désactiver les plats de ce menu
                menuItems.forEach(itemId => {
                    const itemInput = document.getElementById(`item-${itemId}`);
                    if (itemInput) {
                        itemInput.disabled = true;
                        itemInput.value = 0;
                        
                        // Ajouter un style visuel et un message
                        const itemCard = itemInput.closest('.card');
                        if (itemCard) {
                            itemCard.classList.add('bg-light', 'text-muted');
                            
                            // Ajouter un message explicatif s'il n'existe pas déjà
                            if (!itemCard.querySelector('.disabled-message')) {
                                const messageDiv = document.createElement('div');
                                messageDiv.className = 'alert alert-info mt-2 disabled-message';
                                messageDiv.innerHTML = '<small><i class="bx bx-info-circle"></i> Ce plat fait partie d\'un menu que vous avez sélectionné</small>';
                                itemCard.querySelector('.card-body').appendChild(messageDiv);
                            }
                        }
                    }
                });
            });
            
            // Appliquer les contraintes pour les plats actifs
            activeItems.forEach(itemId => {
                // Si ce plat appartient à un menu, désactiver ce menu
                if (itemMenuMap[itemId]) {
                    const menuId = itemMenuMap[itemId];
                    const menuInput = document.getElementById(`menu-${menuId}`);
                    
                    if (menuInput) {
                        menuInput.disabled = true;
                        menuInput.value = 0;
                        
                        // Ajouter un style visuel et un message
                        const menuCard = menuInput.closest('.card');
                        if (menuCard) {
                            menuCard.classList.add('bg-light', 'text-muted');
                            
                            // Ajouter un message explicatif s'il n'existe pas déjà
                            if (!menuCard.querySelector('.disabled-message')) {
                                const messageDiv = document.createElement('div');
                                messageDiv.className = 'alert alert-info mt-2 disabled-message';
                                messageDiv.innerHTML = '<small><i class="bx bx-info-circle"></i> Ce menu contient des plats que vous avez sélectionnés individuellement</small>';
                                menuCard.querySelector('.card-body').appendChild(messageDiv);
                            }
                        }
                    }
                }
            });
            
            // Ajouter les menus au récapitulatif
            menuQuantityInputs.forEach(input => {
                const quantity = parseInt(input.value);
                if (quantity > 0) {
                    const price = parseFloat(input.dataset.price);
                    const itemTotal = price * quantity;
                    const itemName = input.closest('.card').querySelector('.card-title').textContent;
                    
                    totalItems += quantity;
                    totalPrice += itemTotal;
                    
                    summaryHTML += `<div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div>
                            <i class="bx bx-food-menu text-primary me-1"></i>
                            <strong class="text-primary">${quantity} x Menu ${itemName}</strong>
                        </div>
                        <span class="badge bg-primary rounded-pill">${itemTotal.toFixed(2).replace('.', ',')} €</span>
                    </div>`;
                }
            });
            
            // Ajouter les plats individuels au récapitulatif
            quantityInputs.forEach(input => {
                const quantity = parseInt(input.value);
                if (quantity > 0) {
                    const price = parseFloat(input.dataset.price);
                    const itemTotal = price * quantity;
                    const itemName = input.closest('.card').querySelector('.card-title').textContent;
                    
                    totalItems += quantity;
                    totalPrice += itemTotal;
                    
                    summaryHTML += `<div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div>
                            <i class="bx bx-dish text-info me-1"></i>
                            <span>${quantity} x ${itemName}</span>
                        </div>
                        <span class="badge bg-info rounded-pill">${itemTotal.toFixed(2).replace('.', ',')} €</span>
                    </div>`;
                }
            });
            
            if (totalItems > 0) {
                summaryHTML += `<div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <div>
                        <strong class="fs-5">Total:</strong>
                        <div class="text-muted small">${totalItems} article(s)</div>
                    </div>
                    <span class="badge bg-success rounded-pill fs-5">${totalPrice.toFixed(2).replace('.', ',')} €</span>
                </div>`;
                orderSummary.classList.remove('alert-info');
                orderSummary.classList.add('alert-success');
                submitButton.disabled = false;
            } else {
                summaryHTML = '<div class="text-center text-muted">Votre panier est vide</div>';
                orderSummary.classList.remove('alert-success');
                orderSummary.classList.add('alert-info');
                submitButton.disabled = true;
            }
            
            orderSummary.innerHTML = summaryHTML;
        }
        
        // Mettre à jour le récapitulatif lorsque la quantité des plats change
        quantityInputs.forEach(input => {
            input.addEventListener('change', updateOrderSummary);
            input.addEventListener('input', updateOrderSummary);
        });
        
        // Mettre à jour le récapitulatif lorsque la quantité des menus change
        menuQuantityInputs.forEach(input => {
            input.addEventListener('change', updateOrderSummary);
            input.addEventListener('input', updateOrderSummary);
        });
        
        // Initialiser le récapitulatif et les contraintes au chargement de la page
        updateOrderSummary();
    });
</script>
@endsection
