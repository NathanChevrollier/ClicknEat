@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Administration / <a href="{{ route('reservations.index') }}">Réservations</a> /</span> Modifier une réservation
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Formulaire de modification d'une réservation</h5>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('reservations.update', $reservation) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="user_id" class="form-label">Client</label>
                        <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                            <option value="">Sélectionner un client</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', $reservation->user_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="restaurant_id" class="form-label">Restaurant</label>
                        <select class="form-select @error('restaurant_id') is-invalid @enderror" id="restaurant_id" name="restaurant_id" required>
                            <option value="">Sélectionner un restaurant</option>
                            @foreach($restaurants as $restaurant)
                                <option value="{{ $restaurant->id }}" {{ old('restaurant_id', $reservation->restaurant_id) == $restaurant->id ? 'selected' : '' }}>
                                    {{ $restaurant->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('restaurant_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="reservation_date" class="form-label">Date et heure</label>
                        <input type="datetime-local" class="form-control @error('reservation_date') is-invalid @enderror" id="reservation_date" name="reservation_date" value="{{ old('reservation_date', date('Y-m-d\TH:i', strtotime($reservation->reservation_date))) }}" required>
                        @error('reservation_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4">
                        <label for="guests_number" class="form-label">Nombre de personnes</label>
                        <input type="number" class="form-control @error('guests_number') is-invalid @enderror" id="guests_number" name="guests_number" value="{{ old('guests_number', $reservation->guests_number) }}" min="1" required>
                        @error('guests_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="pending" {{ old('status', $reservation->status) == 'pending' ? 'selected' : '' }}>En attente</option>
                            <option value="confirmed" {{ old('status', $reservation->status) == 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                            <option value="cancelled" {{ old('status', $reservation->status) == 'cancelled' ? 'selected' : '' }}>Annulée</option>
                            <option value="completed" {{ old('status', $reservation->status) == 'completed' ? 'selected' : '' }}>Terminée</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="table_id" class="form-label">Table</label>
                    <select class="form-select @error('table_id') is-invalid @enderror" id="table_id" name="table_id" required>
                        @if($tables->count() > 0)
                            @foreach($tables as $table)
                                <option value="{{ $table->id }}" {{ old('table_id', $reservation->table_id) == $table->id ? 'selected' : '' }}>
                                    {{ $table->name }} ({{ $table->capacity }} personnes)
                                </option>
                            @endforeach
                        @else
                            <option value="">Aucune table disponible</option>
                        @endif
                    </select>
                    @error('table_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bx bx-save me-1"></i> Enregistrer les modifications
                    </button>
                    <a href="{{ route('admin.reservations.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-x me-1"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const restaurantSelect = document.getElementById('restaurant_id');
        const reservationDateInput = document.getElementById('reservation_date');
        const guestsNumberInput = document.getElementById('guests_number');
        const tableSelect = document.getElementById('table_id');
        
        // Fonction pour charger les tables disponibles
        function loadAvailableTables() {
            const restaurantId = restaurantSelect.value;
            const reservationDate = reservationDateInput.value;
            const guestsNumber = guestsNumberInput.value;
            
            if (!restaurantId || !reservationDate || !guestsNumber) {
                return;
            }
            
            tableSelect.innerHTML = '<option value="">Chargement des tables...</option>';
            
            // Ajouter l'ID de la réservation actuelle pour l'exclure du filtre des tables réservées
            const reservationId = {{ $reservation->id }};
            
            const url = `/admin/reservations/get-tables?restaurant_id=${restaurantId}&reservation_date=${reservationDate}&guests_number=${guestsNumber}&exclude_reservation_id=${reservationId}`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    tableSelect.innerHTML = '';
                    
                    if (data.tables.length === 0) {
                        tableSelect.innerHTML = '<option value="">Aucune table disponible</option>';
                        return;
                    }
                    
                    data.tables.forEach(table => {
                        const option = document.createElement('option');
                        option.value = table.id;
                        option.textContent = `${table.name} (${table.capacity} personnes)`;
                        // Sélectionner la table actuelle par défaut
                        if (table.id == {{ $reservation->table_id }}) {
                            option.selected = true;
                        }
                        tableSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des tables:', error);
                    tableSelect.innerHTML = '<option value="">Erreur lors du chargement des tables</option>';
                });
        }
        
        // Événements pour déclencher le chargement des tables
        restaurantSelect.addEventListener('change', loadAvailableTables);
        reservationDateInput.addEventListener('change', loadAvailableTables);
        guestsNumberInput.addEventListener('change', loadAvailableTables);
    });
</script>
@endsection
@endsection
