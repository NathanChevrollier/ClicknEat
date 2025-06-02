<div class="d-flex flex-wrap gap-2 justify-content-center">
    @if(auth()->check() && auth()->user()->isRestaurateur() && $restaurant->user_id === auth()->id())
        <div class="dropdown me-2">
            <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="actionDropdown{{ $restaurant->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bx bx-menu me-1"></i> Actions
            </button>
            <ul class="dropdown-menu" aria-labelledby="actionDropdown{{ $restaurant->id }}">
                <li>
                    <a class="dropdown-item" href="{{ route('restaurants.show', $restaurant->id) }}">
                        <i class="bx bx-show me-1"></i> Voir le restaurant
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('restaurants.edit', $restaurant->id) }}">
                        <i class="bx bx-edit me-1"></i> Modifier le restaurant
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('restaurants.categories_items', $restaurant->id) }}">
                        <i class="bx bx-food-menu me-1"></i> Gérer les plats
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="{{ route('restaurants.categories.create', $restaurant->id) }}">
                        <i class="bx bx-category me-1"></i> Ajouter une catégorie
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('restaurants.menus.create', $restaurant->id) }}">
                        <i class="bx bx-list-plus me-1"></i> Ajouter un menu
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); if(confirm('Êtes-vous sûr de vouloir supprimer ce restaurant ? Cette action est irréversible.')) document.getElementById('delete-restaurant-{{ $restaurant->id }}').submit();">
                        <i class="bx bx-trash me-1"></i> Supprimer le restaurant
                    </a>
                    <form id="delete-restaurant-{{ $restaurant->id }}" action="{{ route('restaurants.destroy', $restaurant->id) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </li>
            </ul>
        </div>
        
        <div class="btn-group">
            <form action="{{ route('restaurants.update', $restaurant->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="name" value="{{ $restaurant->name }}">
                <input type="hidden" name="address" value="{{ $restaurant->address }}">
                <input type="hidden" name="phone" value="{{ $restaurant->phone }}">
                <input type="hidden" name="description" value="{{ $restaurant->description }}">
                <input type="hidden" name="is_open" value="{{ isset($restaurant->is_open) && $restaurant->is_open ? '0' : '1' }}">
                <input type="hidden" name="accepts_reservations" value="{{ isset($restaurant->accepts_reservations) ? $restaurant->accepts_reservations : '1' }}">
                <button type="submit" class="btn btn-sm {{ isset($restaurant->is_open) && $restaurant->is_open ? 'btn-success' : 'btn-danger' }}">
                    <i class="bx {{ isset($restaurant->is_open) && $restaurant->is_open ? 'bx-door-open' : 'bx-door-closed' }} me-1"></i> 
                    {{ isset($restaurant->is_open) && $restaurant->is_open ? 'Ouvert' : 'Fermé' }}
                </button>
            </form>
            
            <form action="{{ route('restaurants.update', $restaurant->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="name" value="{{ $restaurant->name }}">
                <input type="hidden" name="address" value="{{ $restaurant->address }}">
                <input type="hidden" name="phone" value="{{ $restaurant->phone }}">
                <input type="hidden" name="description" value="{{ $restaurant->description }}">
                <input type="hidden" name="is_open" value="{{ isset($restaurant->is_open) ? $restaurant->is_open : '1' }}">
                <input type="hidden" name="accepts_reservations" value="{{ isset($restaurant->accepts_reservations) && $restaurant->accepts_reservations ? '0' : '1' }}">
                <button type="submit" class="btn btn-sm {{ isset($restaurant->accepts_reservations) && $restaurant->accepts_reservations ? 'btn-info' : 'btn-warning' }}">
                    <i class="bx {{ isset($restaurant->accepts_reservations) && $restaurant->accepts_reservations ? 'bx-calendar-check' : 'bx-calendar-x' }} me-1"></i>
                    {{ isset($restaurant->accepts_reservations) && $restaurant->accepts_reservations ? 'Réservations actives' : 'Réservations inactives' }}
                </button>
            </form>
        </div>
    @elseif(auth()->check() && auth()->user()->isClient())
        <a href="{{ route('restaurants.show', $restaurant->id) }}" class="btn btn-sm btn-info me-2">
            <i class="bx bx-show me-1"></i> Voir le restaurant
        </a>
        <a href="{{ route('orders.create', $restaurant->id) }}" class="btn btn-sm btn-primary {{ !isset($restaurant->is_open) || !$restaurant->is_open ? 'disabled' : '' }} me-2">
            <i class="bx bx-cart-add me-1"></i> Commander
        </a>
        <a href="{{ route('reservations.create', $restaurant->id) }}" class="btn btn-sm btn-success {{ !isset($restaurant->accepts_reservations) || !$restaurant->accepts_reservations ? 'disabled' : '' }}">
            <i class="bx bx-calendar-check me-1"></i> Réserver
        </a>
    @else
        <a href="{{ route('restaurants.show', $restaurant->id) }}" class="btn btn-sm btn-info">
            <i class="bx bx-show me-1"></i> Voir le restaurant
        </a>
    @endif
</div>
