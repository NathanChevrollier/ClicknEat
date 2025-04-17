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
        const orderSummary = document.getElementById('orderSummary');
        const submitButton = document.getElementById('submitOrder');
        
        function updateOrderSummary() {
            let totalItems = 0;
            let totalPrice = 0;
            let summaryHTML = '';
            
            // Ajouter les plats individuels au récapitulatif
            quantityInputs.forEach(input => {
                const quantity = parseInt(input.value);
                if (quantity > 0) {
                    const price = parseFloat(input.dataset.price);
                    const itemTotal = price * quantity;
                    const itemName = input.closest('.card').querySelector('.card-title').textContent;
                    
                    totalItems += quantity;
                    totalPrice += itemTotal;
                    
                    summaryHTML += `<div class="d-flex justify-content-between mb-1">
                        <span>${quantity} x ${itemName}</span>
                        <span>${itemTotal.toFixed(2).replace('.', ',')} €</span>
                    </div>`;
                }
            });
            
            if (totalItems > 0) {
                summaryHTML += `<hr>
                <div class="d-flex justify-content-between mt-2 fw-bold">
                    <span>Total:</span>
                    <span>${totalPrice.toFixed(2).replace('.', ',')} €</span>
                </div>`;
                orderSummary.classList.remove('alert-info');
                orderSummary.classList.add('alert-success');
                submitButton.disabled = false;
            } else {
                summaryHTML = 'Votre panier est vide';
                orderSummary.classList.remove('alert-success');
                orderSummary.classList.add('alert-info');
                submitButton.disabled = true;
            }
            
            orderSummary.innerHTML = summaryHTML;
        }
        
        // Mettre à jour le récapitulatif lorsque la quantité change
        quantityInputs.forEach(input => {
            input.addEventListener('change', updateOrderSummary);
            input.addEventListener('input', updateOrderSummary);
        });
        
        // Initialiser le récapitulatif au chargement de la page
        updateOrderSummary();
    });
</script>
@endsection
