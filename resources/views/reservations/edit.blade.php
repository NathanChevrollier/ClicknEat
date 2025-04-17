@extends('layouts.main')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .table-option {
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .table-option:hover {
        border-color: #696cff;
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
    }
    .table-option.selected {
        border-color: #696cff;
        background-color: rgba(105, 108, 255, 0.1);
    }
    .item-card {
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px;
        margin-bottom: 10px;
    }
    .item-quantity {
        width: 60px;
    }
</style>
@endsection

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Réservations /</span> Modifier la réservation
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <h5 class="card-header">Modifier la réservation chez {{ $restaurant->name }}</h5>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form id="reservationForm" action="{{ route('reservations.update', $reservation->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Étape 1: Date, heure et nombre de personnes -->
                        <div id="step1" class="reservation-step">
                            <h6 class="mb-3">1. Modifier la date et l'heure</h6>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="date" class="form-label">Date</label>
                                    <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date', $reservation->reservation_date->format('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                                    @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="time" class="form-label">Heure</label>
                                    <input type="time" class="form-control @error('time') is-invalid @enderror" id="time" name="time" value="{{ old('time', $reservation->reservation_date->format('H:i')) }}" required>
                                    @error('time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="guests" class="form-label">Nombre de personnes</label>
                                    <input type="number" class="form-control @error('guests') is-invalid @enderror" id="guests" name="guests" value="{{ old('guests', $reservation->guests_number) }}" min="1" required>
                                    @error('guests')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="mt-3 text-end">
                                <button type="button" id="checkAvailability" class="btn btn-primary">Vérifier la disponibilité</button>
                            </div>
                        </div>
                        
                        <!-- Étape 2: Choix de la table -->
                        <div id="step2" class="reservation-step" style="display: none;">
                            <h6 class="mb-3">2. Choisissez une table</h6>
                            
                            <div id="tablesContainer" class="row">
                                <!-- Les tables disponibles seront affichées ici -->
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle me-1"></i>
                                        Veuillez d'abord vérifier la disponibilité.
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" id="table_id" name="table_id" value="{{ old('table_id', $reservation->table_id) }}">
                            @error('table_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            
                            <div class="mt-3">
                                <button type="button" id="backToStep1" class="btn btn-outline-secondary">Retour</button>
                                <button type="button" id="goToStep3" class="btn btn-primary float-end">Continuer</button>
                            </div>
                        </div>
                        
                        <!-- Étape 3: Précommande et demandes spéciales -->
                        <div id="step3" class="reservation-step" style="display: none;">
                            <h6 class="mb-3">3. Modifier vos plats précommandés (optionnel)</h6>
                            
                            <div class="accordion mb-4" id="menuAccordion">
                                @foreach($categories as $category)
                                    @if($category->items->count() > 0)
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="heading{{ $category->id }}">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $category->id }}" aria-expanded="false" aria-controls="collapse{{ $category->id }}">
                                                    {{ $category->name }}
                                                </button>
                                            </h2>
                                            <div id="collapse{{ $category->id }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $category->id }}" data-bs-parent="#menuAccordion">
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        @foreach($category->items as $item)
                                                            <div class="col-md-6 col-lg-4 mb-3">
                                                                <div class="item-card">
                                                                    <h6>{{ $item->name }}</h6>
                                                                    <p class="small text-muted">{{ $item->description }}</p>
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <span class="fw-bold">{{ number_format($item->price, 2) }} u20ac</span>
                                                                        <div class="d-flex align-items-center">
                                                                            <button type="button" class="btn btn-sm btn-outline-primary decrease-quantity" data-item-id="{{ $item->id }}">-</button>
                                                                            <input type="number" class="form-control form-control-sm mx-2 item-quantity" value="{{ $reservation->order && $reservation->order->items->contains($item->id) ? $reservation->order->items->find($item->id)->pivot->quantity : 0 }}" min="0" data-item-id="{{ $item->id }}">
                                                                            <button type="button" class="btn btn-sm btn-outline-primary increase-quantity" data-item-id="{{ $item->id }}">+</button>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mt-2 special-instructions-container" style="{{ $reservation->order && $reservation->order->items->contains($item->id) ? 'display: block;' : 'display: none;' }}">
                                                                        <input type="text" class="form-control form-control-sm" placeholder="Instructions spéciales" data-item-id="{{ $item->id }}" value="{{ $reservation->order && $reservation->order->items->contains($item->id) ? $reservation->order->items->find($item->id)->pivot->special_instructions : '' }}">
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
                            
                            <div id="selectedItemsContainer" style="{{ $reservation->order && $reservation->order->items->count() > 0 ? 'display: block;' : 'display: none;' }}">
                                <h6>Plats sélectionnés</h6>
                                <ul id="selectedItemsList" class="list-group mb-3">
                                    <!-- Les plats sélectionnés seront affichés ici -->
                                </ul>
                            </div>
                            
                            <div id="itemsInputContainer">
                                <!-- Les inputs pour les plats sélectionnés seront générés ici -->
                            </div>
                            
                            <div class="mb-3">
                                <label for="special_requests" class="form-label">Demandes spéciales</label>
                                <textarea class="form-control" id="special_requests" name="special_requests" rows="3">{{ old('special_requests', $reservation->special_requests) }}</textarea>
                                <div class="form-text">Indiquez ici toute demande particulière concernant votre réservation.</div>
                            </div>
                            
                            <div class="mt-3">
                                <button type="button" id="backToStep2" class="btn btn-outline-secondary">Retour</button>
                                <button type="submit" class="btn btn-success float-end">Mettre à jour la réservation</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation de Flatpickr pour la date
        flatpickr("#date", {
            minDate: "today",
            dateFormat: "Y-m-d"
        });
        
        // Variables pour les étapes
        const step1 = document.getElementById('step1');
        const step2 = document.getElementById('step2');
        const step3 = document.getElementById('step3');
        
        // Boutons de navigation
        const checkAvailabilityBtn = document.getElementById('checkAvailability');
        const backToStep1Btn = document.getElementById('backToStep1');
        const goToStep3Btn = document.getElementById('goToStep3');
        const backToStep2Btn = document.getElementById('backToStep2');
        
        // Conteneur pour les tables disponibles
        const tablesContainer = document.getElementById('tablesContainer');
        
        // Input pour stocker l'ID de la table sélectionnée
        const tableIdInput = document.getElementById('table_id');
        
        // Vérifier la disponibilité des tables
        checkAvailabilityBtn.addEventListener('click', function() {
            const date = document.getElementById('date').value;
            const time = document.getElementById('time').value;
            const guests = document.getElementById('guests').value;
            
            if (!date || !time || !guests) {
                alert('Veuillez remplir tous les champs.');
                return;
            }
            
            // Afficher un indicateur de chargement
            tablesContainer.innerHTML = '<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div></div>';
            
            // Requête AJAX pour vérifier la disponibilité
            fetch('{{ route("restaurants.check-availability", $restaurant->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ date, time, guests })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Ajouter la table actuelle à la liste si elle n'est pas déjà incluse
                    let currentTableIncluded = false;
                    const currentTableId = {{ $reservation->table_id }};
                    
                    for (let i = 0; i < data.tables.length; i++) {
                        if (data.tables[i].id === currentTableId) {
                            currentTableIncluded = true;
                            break;
                        }
                    }
                    
                    if (!currentTableIncluded) {
                        data.tables.push({
                            id: {{ $reservation->table_id }},
                            name: '{{ $reservation->table->name }}',
                            capacity: {{ $reservation->table->capacity }},
                            location: '{{ $reservation->table->location }}'
                        });
                    }
                    
                    // Afficher les tables disponibles
                    let tablesHtml = '';
                    
                    data.tables.forEach(table => {
                        const isSelected = table.id === currentTableId;
                        tablesHtml += `
                            <div class="col-md-4 mb-3">
                                <div class="table-option ${isSelected ? 'selected' : ''}" data-table-id="${table.id}">
                                    <h6>${table.name}</h6>
                                    <p class="mb-1"><i class="bx bx-user me-1"></i> ${table.capacity} personnes</p>
                                    ${table.location ? `<p class="mb-0 text-muted"><i class="bx bx-map me-1"></i> ${table.location}</p>` : ''}
                                </div>
                            </div>
                        `;
                    });
                    
                    tablesContainer.innerHTML = tablesHtml;
                    
                    // Ajouter des écouteurs d'événements pour la sélection de table
                    document.querySelectorAll('.table-option').forEach(tableOption => {
                        tableOption.addEventListener('click', function() {
                            // Supprimer la classe 'selected' de toutes les tables
                            document.querySelectorAll('.table-option').forEach(t => t.classList.remove('selected'));
                            
                            // Ajouter la classe 'selected' à la table cliquée
                            this.classList.add('selected');
                            
                            // Mettre à jour l'input caché avec l'ID de la table
                            tableIdInput.value = this.getAttribute('data-table-id');
                        });
                    });
                    
                    // Passer à l'étape 2
                    step1.style.display = 'none';
                    step2.style.display = 'block';
                } else {
                    // Afficher un message d'erreur
                    tablesContainer.innerHTML = `
                        <div class="col-12">
                            <div class="alert alert-danger">
                                <i class="bx bx-error-circle me-1"></i>
                                ${data.message}
                            </div>
                        </div>
                    `;
                    
                    // Rester à l'étape 1
                    step1.style.display = 'block';
                    step2.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                tablesContainer.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-danger">
                            <i class="bx bx-error-circle me-1"></i>
                            Une erreur est survenue. Veuillez réessayer.
                        </div>
                    </div>
                `;
            });
        });
        
        // Retour à l'étape 1
        backToStep1Btn.addEventListener('click', function() {
            step1.style.display = 'block';
            step2.style.display = 'none';
        });
        
        // Passer à l'étape 3
        goToStep3Btn.addEventListener('click', function() {
            if (tableIdInput.value) {
                step2.style.display = 'none';
                step3.style.display = 'block';
                updateSelectedItemsList(); // Mettre à jour la liste des plats sélectionnés
            } else {
                alert('Veuillez sélectionner une table.');
            }
        });
        
        // Retour à l'étape 2
        backToStep2Btn.addEventListener('click', function() {
            step2.style.display = 'block';
            step3.style.display = 'none';
        });
        
        // Gestion des quantités de plats
        document.querySelectorAll('.increase-quantity').forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.getAttribute('data-item-id');
                const quantityInput = document.querySelector(`input.item-quantity[data-item-id="${itemId}"]`);
                quantityInput.value = parseInt(quantityInput.value) + 1;
                updateSpecialInstructionsVisibility(itemId);
                updateSelectedItemsList();
            });
        });
        
        document.querySelectorAll('.decrease-quantity').forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.getAttribute('data-item-id');
                const quantityInput = document.querySelector(`input.item-quantity[data-item-id="${itemId}"]`);
                if (parseInt(quantityInput.value) > 0) {
                    quantityInput.value = parseInt(quantityInput.value) - 1;
                    updateSpecialInstructionsVisibility(itemId);
                    updateSelectedItemsList();
                }
            });
        });
        
        document.querySelectorAll('input.item-quantity').forEach(input => {
            input.addEventListener('change', function() {
                const itemId = this.getAttribute('data-item-id');
                updateSpecialInstructionsVisibility(itemId);
                updateSelectedItemsList();
            });
        });
        
        // Initialiser la visibilité des instructions spéciales pour les plats déjà sélectionnés
        document.querySelectorAll('input.item-quantity').forEach(input => {
            const itemId = input.getAttribute('data-item-id');
            updateSpecialInstructionsVisibility(itemId);
        });
        
        // Fonction pour mettre à jour la visibilité des instructions spéciales
        function updateSpecialInstructionsVisibility(itemId) {
            const quantityInput = document.querySelector(`input.item-quantity[data-item-id="${itemId}"]`);
            const specialInstructionsContainer = quantityInput.closest('.item-card').querySelector('.special-instructions-container');
            
            if (parseInt(quantityInput.value) > 0) {
                specialInstructionsContainer.style.display = 'block';
            } else {
                specialInstructionsContainer.style.display = 'none';
            }
        }
        
        // Fonction pour mettre à jour la liste des plats sélectionnés
        function updateSelectedItemsList() {
            const selectedItemsList = document.getElementById('selectedItemsList');
            const selectedItemsContainer = document.getElementById('selectedItemsContainer');
            const itemsInputContainer = document.getElementById('itemsInputContainer');
            
            // Vider les conteneurs
            selectedItemsList.innerHTML = '';
            itemsInputContainer.innerHTML = '';
            
            let hasSelectedItems = false;
            
            // Parcourir tous les inputs de quantité
            document.querySelectorAll('input.item-quantity').forEach(input => {
                const itemId = input.getAttribute('data-item-id');
                const quantity = parseInt(input.value);
                
                if (quantity > 0) {
                    hasSelectedItems = true;
                    
                    // Récupérer les informations du plat
                    const itemCard = input.closest('.item-card');
                    const itemName = itemCard.querySelector('h6').textContent;
                    const itemPrice = itemCard.querySelector('.fw-bold').textContent;
                    const specialInstructions = itemCard.querySelector('.special-instructions-container input').value;
                    
                    // Ajouter à la liste des plats sélectionnés
                    selectedItemsList.innerHTML += `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold">${quantity}x</span> ${itemName}
                                ${specialInstructions ? `<small class="d-block text-muted">Note: ${specialInstructions}</small>` : ''}
                            </div>
                            <span>${itemPrice}</span>
                        </li>
                    `;
                    
                    // Créer les inputs cachés pour le formulaire
                    itemsInputContainer.innerHTML += `
                        <input type="hidden" name="items[${itemId}][id]" value="${itemId}">
                        <input type="hidden" name="items[${itemId}][quantity]" value="${quantity}">
                        <input type="hidden" name="items[${itemId}][special_instructions]" value="${specialInstructions}">
                    `;
                }
            });
            
            // Afficher ou masquer le conteneur des plats sélectionnés
            if (hasSelectedItems) {
                selectedItemsContainer.style.display = 'block';
            } else {
                selectedItemsContainer.style.display = 'none';
            }
        }
        
        // Initialiser la liste des plats sélectionnés
        updateSelectedItemsList();
        
        // Afficher directement l'étape 3 si on est en mode édition
        @if(old('table_id', $reservation->table_id))
            step1.style.display = 'none';
            step3.style.display = 'block';
        @endif
    });
</script>
@endsection
