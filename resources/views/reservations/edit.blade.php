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
                        <input type="hidden" name="keep_current_items" id="keepCurrentItemsInput" value="0">
                        
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
                            
                            <div class="mt-3 d-flex justify-content-between">
                                <button type="button" id="skipAvailabilityCheck" class="btn btn-outline-secondary">Continuer sans vérifier</button>
                                <button type="button" id="checkAvailability" class="btn btn-primary">Vérifier la disponibilité</button>
                            </div>
                        </div>
                        
                        <!-- Étape 2: Choix de la table -->
                        <div id="step2" class="reservation-step" style="display: none;">
                            <h6 class="mb-3">2. Choisissez une table</h6>
                            
                            <div id="tablesContainer" class="row">
                                <!-- Les tables disponibles seront affichées ici -->
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="table_id" class="form-label">TABLE</label>
                                        <select name="table_id" id="table_id" class="form-select" required>
                                            <option value="">Sélectionner une table</option>
                                            @php
                                                // Récupérer directement les tables de la base de données
                                                $dbTables = DB::table('tables')
                                                    ->where('restaurant_id', $restaurant->id)
                                                    ->where(function($query) use ($reservation) {
                                                        $query->where('is_available', true)
                                                              ->orWhere('id', $reservation->table_id); // Inclure la table actuelle
                                                    })
                                                    ->get();
                                            @endphp
                                            @foreach($dbTables as $table)
                                                <option value="{{ $table->id }}" {{ $reservation->table_id == $table->id ? 'selected' : '' }}>
                                                    Table {{ $table->name }} ({{ $table->capacity }} personnes) - {{ $table->location }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            @error('table_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            
                            <div class="mt-4 d-flex justify-content-between">
                                <button type="button" id="backToStep1" class="btn btn-outline-secondary">Retour</button>
                                <div>
                                    <button type="button" id="skipTableSelection" class="btn btn-outline-secondary me-2">Continuer sans changer la table</button>
                                    <button type="button" id="goToStep3" class="btn btn-primary">Continuer</button>
                                </div>
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
                                                                            <input type="number" class="form-control form-control-sm mx-2 item-quantity" name="items[{{ $item->id }}][quantity]" value="{{ $reservation->order && $reservation->order->items->contains($item->id) ? $reservation->order->items->find($item->id)->pivot->quantity : 0 }}" min="0" data-item-id="{{ $item->id }}">
                                                                            <button type="button" class="btn btn-sm btn-outline-primary increase-quantity" data-item-id="{{ $item->id }}">+</button>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mt-2 special-instructions-container" style="{{ $reservation->order && $reservation->order->items->contains($item->id) ? 'display: block;' : 'display: none;' }}">
                                                                        <input type="text" class="form-control form-control-sm" name="items[{{ $item->id }}][special_instructions]" placeholder="Instructions spéciales" data-item-id="{{ $item->id }}" value="{{ $reservation->order && $reservation->order->items->contains($item->id) ? $reservation->order->items->find($item->id)->pivot->special_instructions : '' }}">
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
                            
                            <div class="mt-3 d-flex justify-content-between">
                                <button type="button" id="backToStep2" class="btn btn-outline-secondary">Retour</button>
                                <div>
                                    <button type="submit" id="keepCurrentItems" class="btn btn-outline-secondary me-2">Conserver les plats actuels</button>
                                    <button type="submit" class="btn btn-success">Mettre à jour la réservation</button>
                                </div>
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
        // Gérer le bouton pour conserver les plats actuels
        document.getElementById('keepCurrentItems').addEventListener('click', function(e) {
            // Définir la valeur du champ caché sur 1
            document.getElementById('keepCurrentItemsInput').value = '1';
            // Laisser le formulaire se soumettre normalement
        });
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
        
        // Référence au select de tables
        const tableSelect = document.getElementById('table_select');
        
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
            
            // Formater la date et l'heure pour les envoyer au serveur
            const dateTime = new Date(`${date}T${time}`);
            const formattedDateTime = dateTime.toISOString();
            
            // Requête AJAX pour vérifier la disponibilité des tables
            fetch('{{ route("tables.available") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    restaurant_id: {{ $restaurant->id }},
                    reservation_date: formattedDateTime,
                    guests_number: guests,
                    exclude_reservation_id: {{ $reservation->id }}
                })
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
                    
                    // Créer des cartes pour les tables disponibles comme dans create.blade.php
                    let tablesHtml = '';
                    
                    // Ajouter les tables disponibles
                    data.tables.forEach(table => {
                        const isSelected = table.id === currentTableId;
                        const selectedClass = isSelected ? 'border-primary bg-light' : '';
                        const checkedAttr = isSelected ? 'checked' : '';
                        const locationText = table.location || 'Non spécifié';
                        
                        tablesHtml += `
                            <div class="card mb-3 table-option ${selectedClass}" data-table-id="${table.id}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1">Table ${table.name}</h5>
                                            <p class="mb-0"><i class="bx bx-user me-1"></i> ${table.capacity} personnes</p>
                                            <p class="mb-0 text-muted"><i class="bx bx-map me-1"></i> ${locationText}</p>
                                            ${table.description ? `<p class="mb-0 small">${table.description}</p>` : ''}
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input table-radio" type="radio" name="table_id" value="${table.id}" id="table-${table.id}" ${checkedAttr} required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    tablesContainer.innerHTML = tablesHtml;
                    
                    // Ajouter des écouteurs d'événements pour la sélection de table
                    document.querySelectorAll('.table-option').forEach(tableOption => {
                        tableOption.addEventListener('click', function() {
                            const tableId = this.dataset.tableId;
                            document.getElementById(`table-${tableId}`).checked = true;
                            
                            // Mettre à jour la classe selected
                            document.querySelectorAll('.table-option').forEach(el => {
                                el.classList.remove('border-primary');
                                el.classList.remove('bg-light');
                            });
                            this.classList.add('border-primary');
                            this.classList.add('bg-light');
                            
                            // Activer le bouton de soumission si nécessaire
                            const submitButton = document.querySelector('button[type="submit"]');
                            if (submitButton) {
                                submitButton.disabled = false;
                            }
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
        
        // Configurer les boutons de navigation entre les étapes
        document.getElementById('skipAvailabilityCheck').addEventListener('click', function() {
            // Afficher directement l'étape 2 sans vérifier la disponibilité
            if (tableIdInput.value) {
                // Passer directement à l'étape 2
                step1.style.display = 'none';
                step3.style.display = 'none';
                step2.style.display = 'block';
            } else {
                // Si pas de table sélectionnée auparavant, créer une option par défaut
                const tablesHtml = `
                    <div class="col-md-12">
                        <div class="alert alert-warning">
                            <i class="bx bx-info-circle me-1"></i>
                            Vous avez choisi de continuer sans vérifier la disponibilité. Votre réservation conservera la table actuellement attribuée.
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="table-option selected" data-table-id="{{ $reservation->table_id }}">
                                <h6>{{ $reservation->table->name }}</h6>
                                <p class="mb-1"><i class="bx bx-user me-1"></i> {{ $reservation->table->capacity }} personnes</p>
                                @if($reservation->table->location)
                                    <p class="mb-0 text-muted"><i class="bx bx-map me-1"></i> {{ $reservation->table->location }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                `;
                
                tablesContainer.innerHTML = tablesHtml;
                
                // Ajouter des écouteurs pour la sélection de table
                document.querySelectorAll('.table-option').forEach(tableOption => {
                    tableOption.addEventListener('click', function() {
                        document.querySelectorAll('.table-option').forEach(t => t.classList.remove('selected'));
                        this.classList.add('selected');
                        tableIdInput.value = this.getAttribute('data-table-id');
                    });
                });
                
                // Passer à l'étape 2
                step1.style.display = 'none';
                step3.style.display = 'none';
                step2.style.display = 'block';
            }
        });
        
        document.getElementById('backToStep1').addEventListener('click', function() {
            step2.style.display = 'none';
            step3.style.display = 'none';
            step1.style.display = 'block';
        });
        
        document.getElementById('backToStep2').addEventListener('click', function() {
            step1.style.display = 'none';
            step3.style.display = 'none';
            step2.style.display = 'block';
        });
        
        // Passer à l'étape suivante lorsqu'on clique sur le bouton Continuer
        document.getElementById('goToStep3').addEventListener('click', function() {
            step1.style.display = 'none';
            step2.style.display = 'none';
            step3.style.display = 'block';
        });
        
        // Passer directement à l'étape 3 sans changer la table
        document.getElementById('skipTableSelection').addEventListener('click', function() {
            // S'assurer que la table actuelle reste sélectionnée
            tableIdInput.value = {{ $reservation->table_id }};
            
            // Passer à l'étape 3
            step1.style.display = 'none';
            step2.style.display = 'none';
            step3.style.display = 'block';
        });
        
        // En mode édition, préparer les données des tables
        if (tableIdInput.value) {
            // Si une table est déjà sélectionnée, préremplir les tables disponibles
            const availabilityData = {
                success: true,
                tables: [{
                    id: {{ $reservation->table_id }},
                    name: '{{ $reservation->table->name }}',
                    capacity: {{ $reservation->table->capacity }},
                    location: '{{ $reservation->table->location ?? "" }}'
                }]
            };
            
            let tablesHtml = '';
            availabilityData.tables.forEach(table => {
                tablesHtml += `
                    <div class="col-md-4 mb-3">
                        <div class="table-option selected" data-table-id="${table.id}">
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
                    document.querySelectorAll('.table-option').forEach(t => t.classList.remove('selected'));
                    this.classList.add('selected');
                    tableIdInput.value = this.getAttribute('data-table-id');
                });
            });
        }
        
        // Par défaut, afficher l'étape 1 en mode édition
        step1.style.display = 'block';
        step2.style.display = 'none';
        step3.style.display = 'none';
    });
</script>
@endsection
