@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Restaurant / Tables /</span> Disponibilité
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <h5 class="card-header">Disponibilité des tables pour {{ $restaurant->name }}</h5>
                <div class="card-body">
                    <form action="{{ route('restaurants.tables.availability', $restaurant->id) }}" method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date" name="date" value="{{ $date }}" min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="time" class="form-label">Heure</label>
                                <input type="time" class="form-control" id="time" name="time" value="{{ $time }}">
                            </div>
                            <div class="col-md-3">
                                <label for="guests" class="form-label">Nombre de personnes</label>
                                <input type="number" class="form-control" id="guests" name="guests" value="{{ $guests }}" min="1">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                            </div>
                        </div>
                    </form>

                    <h6 class="fw-bold mb-3">Tables disponibles pour le {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }} à {{ $time }}</h6>

                    @if($availableTables->isEmpty())
                        <div class="alert alert-warning">
                            <i class="bx bx-error-circle me-1"></i>
                            Aucune table disponible pour cette date, cette heure et ce nombre de personnes.
                        </div>
                    @else
                        <div class="row">
                            @foreach($availableTables as $table)
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $table->name }}</h5>
                                            <p class="card-text mb-1">
                                                <i class="bx bx-user me-1"></i> Capacité: {{ $table->capacity }} personnes
                                            </p>
                                            @if($table->location)
                                                <p class="card-text mb-1">
                                                    <i class="bx bx-map me-1"></i> Emplacement: {{ $table->location }}
                                                </p>
                                            @endif
                                            @if($table->description)
                                                <p class="card-text text-muted small">
                                                    {{ $table->description }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <a href="{{ route('restaurants.reservations.create', ['restaurant' => $restaurant->id, 'date' => $date, 'time' => $time, 'guests' => $guests, 'table_id' => $table->id]) }}" class="btn btn-outline-primary w-100">
                                                <i class="bx bx-calendar-check me-1"></i> Réserver
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('restaurants.tables.index', $restaurant->id) }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Retour aux tables
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser les champs de date et d'heure si nécessaire
        const dateInput = document.getElementById('date');
        const timeInput = document.getElementById('time');
        
        if (!dateInput.value) {
            dateInput.value = new Date().toISOString().split('T')[0];
        }
        
        if (!timeInput.value) {
            const now = new Date();
            timeInput.value = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
        }
    });
</script>
@endsection
