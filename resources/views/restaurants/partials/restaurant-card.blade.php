@php
    use Illuminate\Support\Str;
@endphp

<div class="card h-100 shadow-sm">
    <div class="card-body">
        <h5 class="card-title fw-semibold">{{ $restaurant->name }}</h5>
        <p class="card-text">{{ Str::limit($restaurant->description, 100) }}</p>
        
        <div class="mb-3">
            <div class="d-flex align-items-center mb-2">
                <i class="bx bx-map-pin me-2 text-muted"></i>
                <span class="text-muted">{{ $restaurant->address ?: 'Adresse non spécifiée' }}</span>
            </div>
            <div class="d-flex align-items-center">
                <i class="bx bx-phone me-2 text-muted"></i>
                <span class="text-muted">{{ $restaurant->phone ?: 'Téléphone non spécifié' }}</span>
            </div>
        </div>
        
        <div class="d-flex flex-wrap gap-2 mb-3">
            @if(isset($restaurant->is_open))
                @if($restaurant->is_open)
                    <span class="badge bg-success"><i class="bx bx-door-open me-1"></i> Ouvert</span>
                @else
                    <span class="badge bg-danger"><i class="bx bx-door-closed me-1"></i> Fermé</span>
                @endif
            @endif
            
            @if(isset($restaurant->accepts_reservations))
                @if($restaurant->accepts_reservations)
                    <span class="badge bg-info"><i class="bx bx-calendar-check me-1"></i> Réservations actives</span>
                @else
                    <span class="badge bg-warning"><i class="bx bx-calendar-x me-1"></i> Réservations inactives</span>
                @endif
            @endif
        </div>
        
        <div class="d-flex justify-content-between mb-3">
            <div class="d-flex align-items-center">
                <i class="bx bx-category me-2"></i>
                <span>{{ $restaurant->categories->count() }} catégorie(s)</span>
            </div>
            @php
                $menuCount = \App\Models\Menu::where('restaurant_id', $restaurant->id)->count();
            @endphp
            <div class="d-flex align-items-center">
                <i class="bx bx-food-menu me-2"></i>
                <span>{{ $menuCount }} menu(s)</span>
            </div>
        </div>
    </div>
    
    <div class="card-footer bg-transparent border-top pt-3">
        @include('restaurants.partials.restaurant-actions', ['restaurant' => $restaurant])
    </div>
</div>
