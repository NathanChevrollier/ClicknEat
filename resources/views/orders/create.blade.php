@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Commander chez {{ $restaurant->name }}</h5>
            <a href="{{ route('restaurants.show', $restaurant->id) }}" class="btn btn-secondary btn-sm">
                <i class="bx bx-arrow-back me-1"></i> Retour au restaurant
            </a>
        </div>
        
        <div class="card-body">
            <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
                @csrf
                <input type="hidden" name="restaurant_id" value="{{ $restaurant->id }}">
                
                @php
                    // Vérifier si la commande est liée à une réservation
                    $hasReservation = request()->has('reservation_id');
                @endphp
                
                @if($hasReservation)
                    <input type="hidden" name="reservation_id" value="{{ request()->get('reservation_id') }}">
                @endif
                
                @if(session('error'))
                <div class="alert alert-danger mb-3">
                    {{ session('error') }}
                </div>
                @endif
                
                <!-- Préparation des données -->
                @php
                    // Récupérer les menus actifs du restaurant
                    $restaurantMenus = \App\Models\Menu::where('restaurant_id', $restaurant->id)
                        ->where('is_active', 1)
                        ->with(['items' => function($query) {
                            $query->where('is_available', 1);
                        }])
                        ->get();
                        
                    // Filtrer les menus pour ne garder que ceux dont tous les plats sont disponibles
                    $menus = [];
                    foreach ($restaurantMenus as $menu) {
                        // Vérifier si tous les plats du menu sont disponibles
                        $allItemsAvailable = true;
                        if ($menu->items->count() > 0) {
                            foreach ($menu->items as $item) {
                                if (!$item->is_available) {
                                    $allItemsAvailable = false;
                                    break;
                                }
                            }
                            
                            // Si tous les plats sont disponibles, ajouter le menu à la liste
                            if ($allItemsAvailable) {
                                $menus[] = $menu;
                            }
                        }
                    }
                    $menus = collect($menus);
                    
                    // Récupérer les catégories avec items disponibles pour le restaurant
                    $categories = \App\Models\Category::whereHas('items', function($query) use ($restaurant) {
                        $query->where('restaurant_id', $restaurant->id)
                              ->where('is_available', 1);
                              // Afficher tous les plats, même ceux qui font partie d'un menu
                    })->with(['items' => function($query) use ($restaurant) {
                        $query->where('restaurant_id', $restaurant->id)
                              ->where('is_available', 1);
                              // Afficher tous les plats, même ceux qui font partie d'un menu
                    }])->get();
                @endphp
                
                <!-- Section des menus -->
                @if(count($menus) > 0)
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-food-menu me-2"></i>
                            <h5 class="mb-0">Menus complets</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($menus as $menu)
                            <div class="col-lg-6 mb-3">
                                <div class="card h-100 menu-card" id="menu-card-{{ $menu->id }}">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">{{ $menu->name }}</h6>
                                        <span class="badge bg-label-primary">{{ number_format($menu->price / 100, 2, ',', ' ') }} €</span>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">{{ \Illuminate\Support\Str::limit($menu->description, 100) }}</p>
                                        
                                        <div class="mt-2 mb-3">
                                            <h6 class="text-muted mb-2">Contenu du menu:</h6>
                                            <ul class="list-unstyled">
                                                @foreach($menu->items->groupBy('category.name') as $categoryName => $items)
                                                <li class="mb-2">
                                                    <strong>{{ $categoryName ?: 'Sans catégorie' }}</strong>
                                                    <ul class="ps-3">
                                                        @foreach($items as $item)
                                                        <li>{{ $item->name }}</li>
                                                        @endforeach
                                                    </ul>
                                                </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="input-group" style="width: 130px;">
                                                <button type="button" class="btn btn-outline-primary btn-sm quantity-minus" data-type="menu" data-id="{{ $menu->id }}">
                                                    <i class="bx bx-minus"></i>
                                                </button>
                                                <input type="number" class="form-control form-control-sm text-center" name="menus[{{ $menu->id }}][quantity]" id="menu-{{ $menu->id }}" value="0" min="0" max="10">
                                                <button type="button" class="btn btn-outline-primary btn-sm quantity-plus" data-type="menu" data-id="{{ $menu->id }}">
                                                    <i class="bx bx-plus"></i>
                                                </button>
                                            </div>
                                            <button type="button" class="btn btn-primary btn-sm add-to-cart" data-type="menu" data-id="{{ $menu->id }}" data-name="{{ $menu->name }}" data-price="{{ $menu->price }}">
                                                <i class="bx bx-plus me-1"></i> Ajouter
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Section des plats individuels par catégorie -->
                @if(count($categories) > 0)
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-dish me-2"></i>
                            <h5 class="mb-0">Plats individuels</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="accordionCategories">
                            @foreach($categories as $index => $category)
                                @if($category->items->count() > 0)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading{{ $category->id }}">
                                        <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $category->id }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $category->id }}">
                                            <div class="d-flex justify-content-between align-items-center w-100">
                                                <span>{{ $category->name }}</span>
                                                <span class="badge bg-info rounded-pill ms-2">{{ $category->items->count() }} plat(s)</span>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $category->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="heading{{ $category->id }}" data-bs-parent="#accordionCategories">
                                        <div class="accordion-body">
                                            <div class="row">
                                                @foreach($category->items as $item)
                                                <div class="col-lg-6 mb-3">
                                                    <div class="card h-100 item-card" id="item-card-{{ $item->id }}">
                                                        <div class="card-header d-flex justify-content-between align-items-center">
                                                            <h6 class="mb-0">{{ $item->name }}</h6>
                                                            <span class="badge bg-label-info">{{ number_format($item->price / 100, 2, ',', ' ') }} €</span>
                                                        </div>
                                                        <div class="card-body">
                                                            <p class="card-text">{{ \Illuminate\Support\Str::limit($item->description, 100) }}</p>
                                                            
                                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                                <div class="input-group" style="width: 130px;">
                                                                    <button type="button" class="btn btn-outline-info btn-sm quantity-minus" data-type="item" data-id="{{ $item->id }}">
                                                                        <i class="bx bx-minus"></i>
                                                                    </button>
                                                                    <input type="number" class="form-control form-control-sm text-center" name="items[{{ $item->id }}][quantity]" id="item-{{ $item->id }}" value="0" min="0" max="10">
                                                                    <button type="button" class="btn btn-outline-info btn-sm quantity-plus" data-type="item" data-id="{{ $item->id }}">
                                                                        <i class="bx bx-plus"></i>
                                                                    </button>
                                                                </div>
                                                                <button type="button" class="btn btn-info btn-sm add-to-cart" data-type="item" data-id="{{ $item->id }}" data-name="{{ $item->name }}" data-price="{{ $item->price }}">
                                                                    <i class="bx bx-plus me-1"></i> Ajouter
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
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
                
                <!-- Récapitulatif de commande -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-cart me-2"></i>
                            <h5 class="mb-0">Récapitulatif de votre commande</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="empty-cart" class="text-center py-4">
                            <i class="bx bx-cart bx-lg mb-2 text-muted"></i>
                            <p class="mb-0">Votre panier est vide</p>
                            <p class="text-muted">Ajoutez des menus ou des plats pour passer commande</p>
                        </div>
                        
                        <div id="cart-items" class="d-none">
                            <!-- Les éléments du panier seront ajoutés ici par JavaScript -->
                        </div>
                        
                        <div id="cart-total" class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top d-none">
                            <h6 class="mb-0">Total</h6>
                            <h5 class="mb-0 text-success">
                                <span id="total-amount">0,00</span> €
                            </h5>
                        </div>
                        
                        <!-- Champs conditionnels pour les commandes sans réservation -->
                        @if(!$hasReservation)
                        <div class="card mb-3">
                            <div class="card-header bg-warning text-white">
                                <h5 class="mb-0">Informations de livraison</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="delivery_date" class="form-label">Date de livraison</label>
                                    <input type="datetime-local" class="form-control" id="delivery_date" name="delivery_date" min="{{ date('Y-m-d\TH:i') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="delivery_address" class="form-label">Adresse de livraison</label>
                                    <textarea class="form-control" id="delivery_address" name="delivery_address" rows="2" required></textarea>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Champ notes pour toutes les commandes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes ou instructions spéciales</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Instructions spéciales, allergies, préférences..."></textarea>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" id="submit-order" class="btn btn-success w-100" disabled>
                                <i class="bx bx-check-circle me-1"></i> Valider ma commande
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Éléments DOM
        const quantityInputs = document.querySelectorAll('input[type="number"]');
        const quantityMinusButtons = document.querySelectorAll('.quantity-minus');
        const quantityPlusButtons = document.querySelectorAll('.quantity-plus');
        const addToCartButtons = document.querySelectorAll('.add-to-cart');
        const cartItemsContainer = document.getElementById('cart-items');
        const emptyCart = document.getElementById('empty-cart');
        const cartTotal = document.getElementById('cart-total');
        const totalAmount = document.getElementById('total-amount');
        const submitButton = document.getElementById('submit-order');
        const orderForm = document.getElementById('orderForm');
        
        // Panier
        let cart = [];
        
        // Fonction pour mettre à jour l'affichage du panier
        function updateCartDisplay() {
            // Filtrer les éléments avec quantité > 0
            cart = cart.filter(item => item.quantity > 0);
            
            // Vider le conteneur
            cartItemsContainer.innerHTML = '';
            
            // Calculer le total
            let total = 0;
            
            if (cart.length === 0) {
                // Panier vide
                emptyCart.classList.remove('d-none');
                cartItemsContainer.classList.add('d-none');
                cartTotal.classList.add('d-none');
                submitButton.disabled = true;
            } else {
                // Panier avec des éléments
                emptyCart.classList.add('d-none');
                cartItemsContainer.classList.remove('d-none');
                cartTotal.classList.remove('d-none');
                submitButton.disabled = false;
                
                // Créer la liste des éléments
                const table = document.createElement('table');
                table.className = 'table table-sm';
                
                const thead = document.createElement('thead');
                thead.innerHTML = `
                    <tr>
                        <th>Produit</th>
                        <th class="text-center">Quantité</th>
                        <th class="text-end">Prix</th>
                        <th></th>
                    </tr>
                `;
                table.appendChild(thead);
                
                const tbody = document.createElement('tbody');
                
                cart.forEach(item => {
                    const tr = document.createElement('tr');
                    const itemTotal = item.price * item.quantity;
                    total += itemTotal;
                    
                    tr.innerHTML = `
                        <td>
                            <strong>${item.name}</strong>
                            <span class="badge bg-label-${item.type === 'menu' ? 'primary' : 'info'} ms-1">
                                ${item.type === 'menu' ? 'Menu' : 'Plat'}
                            </span>
                        </td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-end">${(itemTotal / 100).toFixed(2).replace('.', ',')} €</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-icon btn-sm btn-outline-danger remove-item" data-type="${item.type}" data-id="${item.id}">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    `;
                    
                    tbody.appendChild(tr);
                });
                
                table.appendChild(tbody);
                cartItemsContainer.appendChild(table);
                
                // Mettre à jour le total
                totalAmount.textContent = (total / 100).toFixed(2).replace('.', ',');
                
                // Ajouter des écouteurs pour les boutons de suppression
                document.querySelectorAll('.remove-item').forEach(button => {
                    button.addEventListener('click', function() {
                        const type = this.dataset.type;
                        const id = this.dataset.id;
                        
                        // Réinitialiser l'input de quantité
                        const input = document.getElementById(`${type}-${id}`);
                        if (input) {
                            input.value = 0;
                            
                            // Supprimer du panier
                            const itemIndex = cart.findIndex(item => item.type === type && item.id === id);
                            if (itemIndex !== -1) {
                                cart.splice(itemIndex, 1);
                                
                                // Mettre à jour l'affichage
                                updateCartDisplay();
                                
                                // Mise à jour visuelle de la carte
                                const card = document.getElementById(`${type}-card-${id}`);
                                if (card) {
                                    card.classList.remove('border-primary', 'border-info');
                                }
                            }
                        }
                    });
                });
            }
        }
        
        // Fonction pour ajouter un élément au panier
        function addToCart(type, id, name, price, quantity) {
            // Vérifier si l'élément existe déjà
            const existingItem = cart.find(item => item.type === type && item.id === id);
            
            if (existingItem) {
                // Mettre à jour la quantité
                existingItem.quantity = quantity;
            } else {
                // Ajouter un nouvel élément
                cart.push({
                    type: type,
                    id: id,
                    name: name,
                    price: price,
                    quantity: quantity
                });
            }
            
            // Mettre à jour l'affichage
            updateCartDisplay();
        }
        
        // Écouteurs pour les boutons de quantité
        quantityMinusButtons.forEach(button => {
            button.addEventListener('click', function() {
                const type = this.dataset.type;
                const id = this.dataset.id;
                const input = document.getElementById(`${type}-${id}`);
                let value = parseInt(input.value);
                
                if (value > 0) {
                    input.value = value - 1;
                }
            });
        });
        
        quantityPlusButtons.forEach(button => {
            button.addEventListener('click', function() {
                const type = this.dataset.type;
                const id = this.dataset.id;
                const input = document.getElementById(`${type}-${id}`);
                let value = parseInt(input.value);
                
                if (value < 10) {
                    input.value = value + 1;
                }
            });
        });
        
        // Écouteurs pour les boutons d'ajout au panier
        addToCartButtons.forEach(button => {
            button.addEventListener('click', function() {
                const type = this.dataset.type;
                const id = this.dataset.id;
                const name = this.dataset.name;
                const price = parseInt(this.dataset.price);
                const input = document.getElementById(`${type}-${id}`);
                const quantity = parseInt(input.value);
                
                if (quantity > 0) {
                    // Ajouter au panier
                    addToCart(type, id, name, price, quantity);
                    
                    // Mise à jour visuelle de la carte
                    const card = document.getElementById(`${type}-card-${id}`);
                    if (card) {
                        card.classList.remove('border-primary', 'border-info');
                        card.classList.add(`border-${type === 'menu' ? 'primary' : 'info'}`);
                        
                        // Animation
                        card.animate([
                            { transform: 'scale(1)' },
                            { transform: 'scale(1.05)' },
                            { transform: 'scale(1)' }
                        ], {
                            duration: 300,
                            iterations: 1
                        });
                    }
                }
            });
        });
        
        // Empêcher la soumission du formulaire par la touche Entrée
        orderForm.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.type !== 'textarea') {
                e.preventDefault();
                return false;
            }
        });
        
        // Validation avant soumission
        orderForm.addEventListener('submit', function(e) {
            if (cart.length === 0) {
                e.preventDefault();
                alert('Votre panier est vide. Veuillez ajouter au moins un article avant de valider votre commande.');
            }
        });
        
        // Initialiser l'affichage du panier
        updateCartDisplay();
    });
</script>
@endsection
