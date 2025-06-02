@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Réservation chez {{ $restaurant->name }}</h3>
            <div>
                <a href="{{ route('restaurants.index') }}" class="btn btn-secondary">Retour aux restaurants</a>
            </div>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('reservations.store') }}" method="POST" id="reservationForm">
                @csrf
                <input type="hidden" name="restaurant_id" value="{{ $restaurant->id }}">
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="reservation_date" class="form-label">Date et heure de réservation</label>
                            <input type="datetime-local" class="form-control @error('reservation_date') is-invalid @enderror" 
                                id="reservation_date" name="reservation_date" value="{{ old('reservation_date') }}" required>
                            @error('reservation_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Choisissez une date et une heure pour votre réservation</small>
                        </div>

                        <div class="mb-3">
                            <label for="guests_number" class="form-label">Nombre de personnes</label>
                            <input type="number" class="form-control @error('guests_number') is-invalid @enderror" 
                                id="guests_number" name="guests_number" min="1" max="20" value="{{ old('guests_number', 2) }}" required>
                            @error('guests_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="special_requests" class="form-label">Demandes spéciales (optionnel)</label>
                            <textarea class="form-control @error('special_requests') is-invalid @enderror" 
                                id="special_requests" name="special_requests" rows="3">{{ old('special_requests') }}</textarea>
                            @error('special_requests')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Indiquez toute demande spéciale (allergie, occasion spéciale, etc.)</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h5><i class="bx bx-table me-1"></i> Tables disponibles</h5>
                        <div class="mb-3">
                            <div class="alert alert-info mb-3">
                                <i class="bx bx-info-circle me-1"></i> Sélectionnez une date, une heure et le nombre de personnes pour voir les tables disponibles
                            </div>
                            <div id="tables-container" class="tables-container">
                                <!-- Les tables disponibles seront chargées ici dynamiquement -->
                            </div>
                            <input type="hidden" name="table_id" id="table_id" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="confirm_policy" required>
                            <label class="form-check-label" for="confirm_policy">
                                J'accepte les <a href="#" data-bs-toggle="modal" data-bs-target="#policyModal">conditions de réservation</a>
                            </label>
                        </div>
                        
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="create_order" name="create_order" value="1">
                            <label class="form-check-label" for="create_order">
                                <strong>Créer une commande pour cette réservation</strong>
                                <small class="d-block text-muted">Vous serez redirigé vers le formulaire de commande après l'enregistrement de la réservation.</small>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" id="submitButton" disabled>
                        <i class="bx bx-calendar-check me-1"></i> Confirmer la réservation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reservationDateInput = document.getElementById('reservation_date');
        const guestsNumberInput = document.getElementById('guests_number');
        const tableIdInput = document.getElementById('table_id');
        const tablesContainer = document.getElementById('tables-container');
        const submitButton = document.getElementById('submitButton');
        
        // Ajouter des styles pour les cartes de table
        const style = document.createElement('style');
        style.textContent = `
            .table-option {
                transition: all 0.3s ease;
                cursor: pointer;
                border: 1px solid #e0e0e0;
            }
            .table-option:hover {
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                transform: translateY(-2px);
            }
            .table-option.selected {
                border: 2px solid #696cff;
                box-shadow: 0 4px 12px rgba(105, 108, 255, 0.16);
            }
            .tables-container {
                max-height: 400px;
                overflow-y: auto;
                padding-right: 5px;
            }
        `;
        document.head.appendChild(style);
        
        // Initialiser avec la date et l'heure actuelles
        const now = new Date();
        // Arrondir à l'heure suivante
        now.setHours(now.getHours() + 1);
        now.setMinutes(0);
        now.setSeconds(0);
        
        const formattedDate = now.toISOString().slice(0, 16);
        reservationDateInput.value = formattedDate;
        
        // Fonction pour charger les tables disponibles
        function loadAvailableTables() {
            const date = reservationDateInput.value;
            const guests = guestsNumberInput.value;
            
            if (!date || !guests) {
                tablesContainer.innerHTML = `
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-1"></i> Veuillez sélectionner une date et une heure pour voir les tables disponibles.
                    </div>
                `;
                submitButton.disabled = true;
                return;
            }
            
            tablesContainer.innerHTML = `
                <div class="text-center p-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2">Recherche des tables disponibles...</p>
                </div>
            `;
            
            // Faire une requête AJAX pour obtenir les tables disponibles
            fetch('{{ route("tables.available") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    restaurant_id: {{ $restaurant->id }},
                    reservation_date: date,
                    guests_number: guests
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.tables && data.tables.length > 0) {
                    let tablesHtml = '';
                    data.tables.forEach(table => {
                        tablesHtml += `
                            <div class="card mb-3 table-option" data-table-id="${table.id}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1"><i class="bx bx-chair me-1"></i> ${table.name}</h5>
                                            <p class="mb-0"><i class="bx bx-user me-1"></i> <span class="fw-bold">${table.capacity}</span> personnes</p>
                                            <p class="mb-0 text-muted"><i class="bx bx-map me-1"></i> ${table.location || 'Salle principale'}</p>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-primary select-table-btn" data-table-id="${table.id}">Choisir</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    tablesContainer.innerHTML = tablesHtml;
                    
                    // Ajouter des écouteurs d'événements pour la sélection de table
                    document.querySelectorAll('.select-table-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const tableId = this.dataset.tableId;
                            document.getElementById('table_id').value = tableId;
                            
                            // Mettre à jour la classe selected
                            document.querySelectorAll('.table-option').forEach(el => {
                                el.classList.remove('selected');
                            });
                            this.closest('.table-option').classList.add('selected');
                            
                            // Activer le bouton de soumission
                            submitButton.disabled = false;
                            
                            // Feedback visuel
                            document.querySelectorAll('.select-table-btn').forEach(b => {
                                b.classList.remove('btn-primary');
                                b.classList.add('btn-outline-primary');
                                b.textContent = 'Choisir';
                            });
                            this.classList.remove('btn-outline-primary');
                            this.classList.add('btn-primary');
                            this.textContent = 'Sélectionnée';
                        });
                    });
                } else {
                    tablesContainer.innerHTML = `
                        <div class="alert alert-warning">
                            <i class="bx bx-error-circle me-1"></i> Aucune table disponible pour ${guests} personnes à cette date et heure.
                        </div>
                    `;
                    submitButton.disabled = true;
                }
            })
            .catch(error => {
                console.error('Erreur lors de la récupération des tables disponibles:', error);
                tablesContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bx bx-error-circle me-1"></i> Erreur lors de la récupération des tables disponibles. Veuillez réessayer.
                    </div>
                `;
                submitButton.disabled = true;
            });
        }
        
        // Charger les tables au chargement de la page
        loadAvailableTables();
        
        // Recharger les tables lorsque la date ou le nombre de personnes change
        reservationDateInput.addEventListener('change', loadAvailableTables);
        guestsNumberInput.addEventListener('change', loadAvailableTables);
    });
</script>
@endsection
@endsection
