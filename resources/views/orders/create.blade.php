@extends('layouts.main')

@php
    use Illuminate\Support\Str;
@endphp

@section('main')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Commander chez {{ $restaurant->name }}</h3>
        <div>
            <a href="{{ route('restaurants.show', $restaurant->id) }}" class="btn btn-secondary">Retour au restaurant</a>
        </div>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
        <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
            @csrf
            <input type="hidden" name="restaurant_id" value="{{ $restaurant->id }}">
            
            <!-- Section des menus -->
            @php
                $menus = \App\Models\Menu::where('restaurant_id', $restaurant->id)->with('items')->get();
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
                                                <input type="number" min="0" value="0" class="form-control menu-quantity" 
                                                    id="menu-{{ $menu->id }}" 
                                                    name="menus[{{ $menu->id }}][quantity]" 
                                                    data-price="{{ $menu->price * 100 }}">
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
                                                        @if($item->is_active)
                                                        <div class="col-md-4 mb-3">
                                                            <div class="card h-100">
                                                                <div class="card-body">
                                                                    <h5 class="card-title">{{ $item->name }}</h5>
                                                                    <p class="card-text">{{ Str::limit($item->description, 50) }}</p>
                                                                    <p class="card-text fw-bold">{{ number_format($item->price / 100, 2, ',', ' ') }} &euro;</p>
                                                                    <div class="d-flex align-items-center">
                                                                        <label for="item-{{ $item->id }}" class="me-2">Quantité:</label>
                                                                        <input type="number" min="0" value="0" class="form-control item-quantity" 
                                                                            id="item-{{ $item->id }}" 
                                                                            name="items[{{ $item->id }}][quantity]" 
                                                                            data-price="{{ $item->price }}">
                                                                        <input type="hidden" name="items[{{ $item->id }}][price]" value="{{ $item->price }}">
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

                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h4 class="mb-0"><i class="bx bx-note me-2"></i>Notes pour la commande</h4>
                    </div>
                    <div class="card-body">
                        <textarea name="notes" class="form-control" rows="3" placeholder="Informations spéciales pour votre commande (allergies, préférences, etc.)"></textarea>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="bx bx-cart me-2"></i>Récapitulatif de la commande</h4>
                    </div>
                    <div class="card-body">
                        <div id="orderSummary" class="alert alert-info">
                            Votre panier est vide
                        </div>
                        <button type="submit" class="btn btn-primary mt-3 w-100" id="submitOrder" disabled>
                            <i class="bx bx-check-circle me-1"></i> Passer la commande
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
        
        function updateOrderSummary() {
            let totalItems = 0;
            let totalPrice = 0;
            let summaryHTML = '';
            
            // Ajouter les menus au récapitulatif
            menuQuantityInputs.forEach(input => {
                const quantity = parseInt(input.value);
                if (quantity > 0) {
                    const price = parseInt(input.dataset.price);
                    const itemTotal = price * quantity;
                    const itemName = input.closest('.card').querySelector('.card-title').textContent;
                    
                    totalItems += quantity;
                    totalPrice += itemTotal;
                    
                    summaryHTML += `<div class="d-flex justify-content-between mb-1">
                        <span><strong>${quantity} x Menu ${itemName}</strong></span>
                        <span>${(itemTotal / 100).toFixed(2).replace('.', ',')} &euro;</span>
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
                    
                    summaryHTML += `<div class="d-flex justify-content-between mb-1">
                        <span>${quantity} x ${itemName}</span>
                        <span>${(itemTotal / 100).toFixed(2).replace('.', ',')} &euro;</span>
                    </div>`;
                }
            });
            
            if (totalItems > 0) {
                summaryHTML += `<hr>
                <div class="d-flex justify-content-between mt-2 fw-bold">
                    <span>Total:</span>
                    <span>${(totalPrice / 100).toFixed(2).replace('.', ',')} &euro;</span>
                </div>`;
                orderSummary.innerHTML = summaryHTML;
                submitButton.disabled = false;
            } else {
                orderSummary.innerHTML = 'Votre panier est vide';
                submitButton.disabled = true;
            }
        }
        
        // Écouter les changements de quantité pour les plats individuels
        quantityInputs.forEach(input => {
            input.addEventListener('change', updateOrderSummary);
            input.addEventListener('input', updateOrderSummary);
        });
        
        // Écouter les changements de quantité pour les menus
        menuQuantityInputs.forEach(input => {
            input.addEventListener('change', updateOrderSummary);
            input.addEventListener('input', updateOrderSummary);
        });
        
        // Initialiser le récapitulatif
        updateOrderSummary();
    });
</script>
@endsection
