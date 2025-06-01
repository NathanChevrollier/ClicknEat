@extends('layouts.main')

@php
    use Illuminate\Support\Str;
@endphp

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">{{ $restaurant->name }}</h3>
                    <div>
                        <a href="{{ route('restaurants.index') }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Retour
                        </a>
                        @if(auth()->user()->isRestaurateur() && $restaurant->user_id === auth()->id())
                            <a href="{{ route('restaurants.edit', $restaurant->id) }}" class="btn btn-warning">
                                <i class="bx bx-edit me-1"></i> Modifier
                            </a>
                            <a href="{{ route('restaurants.categories_items', $restaurant->id) }}" class="btn btn-success">
                                <i class="bx bx-food-menu me-1"></i> Gérer les plats
                            </a>
                        @elseif(auth()->user()->isClient())
                            @if($restaurant->is_open)
                                <a href="{{ route('orders.create', $restaurant->id) }}" class="btn btn-primary me-2">
                                    <i class="bx bx-cart-add me-1"></i> Commander
                                </a>
                            @else
                                <button class="btn btn-secondary me-2" disabled>
                                    <i class="bx bx-cart-add me-1"></i> Restaurant fermé
                                </button>
                            @endif
                            
                            @if($restaurant->accepts_reservations && isset($availableTables) && $availableTables > 0)
                                <a href="{{ route('reservations.create', $restaurant->id) }}" class="btn btn-info">
                                    <i class="bx bx-calendar-check me-1"></i> Réserver une table
                                </a>
                            @elseif(!$restaurant->accepts_reservations)
                                <button class="btn btn-secondary" disabled>
                                    <i class="bx bx-calendar-x me-1"></i> Réservations désactivées
                                </button>
                            @else
                                <button class="btn btn-secondary" disabled>
                                    <i class="bx bx-calendar-x me-1"></i> Aucune table disponible
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4 mb-md-0">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h4 class="card-title">Informations</h4>
                                    <div class="mb-3">
                                        <strong><i class="bx bx-map-pin me-1"></i> Adresse:</strong>
                                        <p>{{ $restaurant->address ?: 'Non spécifiée' }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <strong><i class="bx bx-phone me-1"></i> Téléphone:</strong>
                                        <p>{{ $restaurant->phone ?: 'Non spécifié' }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <strong><i class="bx bx-user me-1"></i> Propriétaire:</strong>
                                        <p>{{ $restaurant->user->name }}</p>
                                    </div>
                                    <div>
                                        <strong><i class="bx bx-calendar me-1"></i> Créé le:</strong>
                                        <p>{{ $restaurant->created_at->format('d/m/Y') }}</p>
                                    </div>
                                    
                                    @if(auth()->user()->isRestaurateur() && $restaurant->user_id === auth()->id() || auth()->user()->isAdmin())
                                    <div class="mt-4">
                                        <h5 class="mb-3">Statut du restaurant</h5>
                                        <div class="d-flex flex-column gap-3">
                                            <div class="card bg-light">
                                                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">Statut d'ouverture</h6>
                                                        <p class="mb-0">
                                                            @if(isset($restaurant->is_open) && $restaurant->is_open)
                                                                <span class="badge bg-success"><i class="bx bx-door-open me-1"></i> Ouvert</span>
                                                            @else
                                                                <span class="badge bg-danger"><i class="bx bx-door-closed me-1"></i> Fermé</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <form action="{{ route('restaurants.update', $restaurant->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="name" value="{{ $restaurant->name }}">
                                                        <input type="hidden" name="address" value="{{ $restaurant->address }}">
                                                        <input type="hidden" name="phone" value="{{ $restaurant->phone }}">
                                                        <input type="hidden" name="description" value="{{ $restaurant->description }}">
                                                        <input type="hidden" name="is_open" value="{{ isset($restaurant->is_open) && $restaurant->is_open ? '0' : '1' }}">
                                                        <input type="hidden" name="accepts_reservations" value="{{ isset($restaurant->accepts_reservations) ? $restaurant->accepts_reservations : '1' }}">
                                                        <button type="submit" class="btn btn-sm {{ isset($restaurant->is_open) && $restaurant->is_open ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                                            {{ isset($restaurant->is_open) && $restaurant->is_open ? 'Fermer le restaurant' : 'Ouvrir le restaurant' }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                            
                                            <div class="card bg-light">
                                                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">Réservations</h6>
                                                        <p class="mb-0">
                                                            @if(isset($restaurant->accepts_reservations) && $restaurant->accepts_reservations)
                                                                <span class="badge bg-info"><i class="bx bx-calendar-check me-1"></i> Actives</span>
                                                            @else
                                                                <span class="badge bg-warning"><i class="bx bx-calendar-x me-1"></i> Inactives</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <form action="{{ route('restaurants.update', $restaurant->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="name" value="{{ $restaurant->name }}">
                                                        <input type="hidden" name="address" value="{{ $restaurant->address }}">
                                                        <input type="hidden" name="phone" value="{{ $restaurant->phone }}">
                                                        <input type="hidden" name="description" value="{{ $restaurant->description }}">
                                                        <input type="hidden" name="is_open" value="{{ isset($restaurant->is_open) ? $restaurant->is_open : '1' }}">
                                                        <input type="hidden" name="accepts_reservations" value="{{ isset($restaurant->accepts_reservations) && $restaurant->accepts_reservations ? '0' : '1' }}">
                                                        <button type="submit" class="btn btn-sm {{ isset($restaurant->accepts_reservations) && $restaurant->accepts_reservations ? 'btn-outline-warning' : 'btn-outline-info' }}">
                                                            {{ isset($restaurant->accepts_reservations) && $restaurant->accepts_reservations ? 'Désactiver les réservations' : 'Activer les réservations' }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                            
                                            <!-- Affichage du nombre de tables disponibles -->
                                            <div class="card bg-light">
                                                <div class="card-body p-3">
                                                    <div>
                                                        <h6 class="mb-1">Tables</h6>
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <p class="mb-0">
                                                                <span class="badge bg-primary"><i class="bx bx-chair me-1"></i> Total: {{ $totalTables ?? 0 }}</span>
                                                                <span class="badge bg-success ms-2"><i class="bx bx-check-circle me-1"></i> Disponibles: {{ $availableTables ?? 0 }}</span>
                                                            </p>
                                                            @if(auth()->user()->isRestaurateur() && $restaurant->user_id === auth()->id())
                                                                <a href="{{ route('restaurants.tables.index', $restaurant->id) }}" class="btn btn-sm btn-outline-primary">
                                                                    <i class="bx bx-table me-1"></i> Gérer les tables
                                                                </a>
                                                            @endif
                                                        </div>
                                                        @if($totalTables > 0)
                                                            <div class="progress" style="height: 10px;">
                                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($availableTables / $totalTables) * 100 }}%;" aria-valuenow="{{ ($availableTables / $totalTables) * 100 }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                            </div>
                                                        @else
                                                            <div class="alert alert-warning py-1 mb-0">
                                                                <small><i class="bx bx-info-circle me-1"></i> Aucune table n'a été configurée pour ce restaurant.</small>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100" id="menus">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="card-title mb-0">Menus</h4>
                                        @if(auth()->user()->isRestaurateur() && $restaurant->user_id === auth()->id())
                                            <a href="{{ route('restaurants.menus.create', $restaurant->id) }}" class="btn btn-sm btn-primary">
                                                <i class="bx bx-plus me-1"></i> Ajouter un menu
                                            </a>
                                        @endif
                                    </div>
                                    @php
                                        $menus = \App\Models\Menu::where('restaurant_id', $restaurant->id)->with('items')->get();
                                    @endphp
                                    @if(count($menus) > 0)
                                        <div class="list-group">
                                            @foreach($menus as $menu)
                                                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h5 class="mb-1">{{ $menu->name }}</h5>
                                                        <p class="mb-0 text-muted">{{ $menu->items->count() }} plat(s) - {{ number_format($menu->price, 2, ',', ' ') }} €</p>
                                                    </div>
                                                    <div>
                                                        <div class="btn-group">
                                                            <a href="{{ route('restaurants.menus.show', [$restaurant->id, $menu->id]) }}" class="btn btn-sm btn-info">
                                                                <i class="bx bx-show"></i>
                                                            </a>
                                                            @if(auth()->user()->isRestaurateur() && $restaurant->user_id === auth()->id())
                                                                <a href="{{ route('restaurants.menus.edit', [$restaurant->id, $menu->id]) }}" class="btn btn-sm btn-warning">
                                                                    <i class="bx bx-edit"></i>
                                                                </a>
                                                                <form action="{{ route('restaurants.menus.destroy', [$restaurant->id, $menu->id]) }}" method="POST" style="margin: 0;">
                                                                    @csrf
                                                                    @method('delete')
                                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer ce menu ?')">
                                                                        <i class="bx bx-trash"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="bx bx-info-circle me-1"></i> Aucun menu pour ce restaurant.
                                            @if(auth()->user()->isRestaurateur() && $restaurant->user_id === auth()->id())
                                                <a href="{{ route('restaurants.menus.create', $restaurant->id) }}" class="alert-link">Créer un menu</a>.
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section des avis -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card" id="reviews">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h4 class="card-title mb-0">Avis et évaluations</h4>
                                    @auth
                                        @if(auth()->user()->isClient())
                                            <a href="{{ route('restaurants.reviews.create', $restaurant->id) }}" class="btn btn-primary btn-sm">
                                                <i class="bx bx-star me-1"></i> Laisser un avis
                                            </a>
                                        @endif
                                    @endauth
                                </div>
                                <div class="card-body">
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="me-3">
                                                    <h5 class="mb-0">{{ number_format($restaurant->averageRating(), 1) }}</h5>
                                                    <div>
                                                        @for($i = 1; $i <= 5; $i++)
                                                            @if($i <= round($restaurant->averageRating()))
                                                                <i class="bx bxs-star text-warning"></i>
                                                            @else
                                                                <i class="bx bx-star text-warning"></i>
                                                            @endif
                                                        @endfor
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="text-muted">{{ $restaurant->reviews()->count() }} avis</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @php
                                        $reviews = \App\Models\Review::where('restaurant_id', $restaurant->id)
                                            ->where('is_approved', true)
                                            ->with('user')
                                            ->orderBy('created_at', 'desc')
                                            ->take(3)
                                            ->get();
                                    @endphp

                                    @if($reviews->isEmpty())
                                        <div class="alert alert-info">
                                            <i class="bx bx-info-circle me-1"></i>
                                            Aucun avis pour ce restaurant pour le moment.
                                            @auth
                                                @if(auth()->user()->isClient())
                                                    <a href="{{ route('restaurants.reviews.create', $restaurant->id) }}" class="alert-link">Soyez le premier à laisser un avis !</a>
                                                @endif
                                            @endauth
                                        </div>
                                    @else
                                        <div class="reviews-list">
                                            @foreach($reviews as $review)
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                                            <div>
                                                                <h5 class="mb-0">{{ $review->user->name }}</h5>
                                                                <div class="text-muted small">{{ $review->created_at->format('d/m/Y') }}</div>
                                                            </div>
                                                            <div>
                                                                <div class="mb-1">
                                                                    @for($i = 1; $i <= 5; $i++)
                                                                        @if($i <= $review->rating)
                                                                            <i class="bx bxs-star text-warning"></i>
                                                                        @else
                                                                            <i class="bx bx-star text-warning"></i>
                                                                        @endif
                                                                    @endfor
                                                                </div>
                                                                @if(auth()->check() && (auth()->id() === $review->user_id || auth()->user()->isAdmin()))
                                                                    <div class="btn-group btn-group-sm">
                                                                        <a href="{{ route('restaurants.reviews.edit', [$restaurant->id, $review->id]) }}" class="btn btn-outline-primary">
                                                                            <i class="bx bx-edit-alt"></i>
                                                                        </a>
                                                                        <form action="{{ route('restaurants.reviews.destroy', [$restaurant->id, $review->id]) }}" method="POST" class="d-inline">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet avis ?')">
                                                                                <i class="bx bx-trash"></i>
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <p>{{ $review->comment }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                            
                                            @if($restaurant->reviews()->count() > 3)
                                                <div class="text-center mt-3">
                                                    <a href="{{ route('restaurants.reviews.index', $restaurant->id) }}" class="btn btn-outline-primary">
                                                        <i class="bx bx-list-ul me-1"></i> Voir tous les avis ({{ $restaurant->reviews()->count() }})
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section des catégories et plats -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card" id="categories">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h4 class="card-title mb-0">Catégories et Plats</h4>
                                    @if(auth()->user()->isRestaurateur() && $restaurant->user_id === auth()->id())
                                        <div>
                                            <a href="{{ route('restaurants.categories.create', $restaurant) }}" class="btn btn-sm btn-primary me-2">
                                                <i class="bx bx-plus me-1"></i> Ajouter une catégorie
                                            </a>
                                            <a href="{{ route('restaurants.items.create', $restaurant) }}" class="btn btn-sm btn-primary">
                                                <i class="bx bx-plus me-1"></i> Ajouter un plat
                                            </a>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body" id="items">
                                    @if(count($restaurant->categories) > 0)
                                        <div class="accordion" id="accordionCategories">
                                            @foreach($restaurant->categories as $index => $category)
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="heading{{ $category->id }}">
                                                        <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $category->id }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $category->id }}">
                                                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                                                <span>{{ $category->name }} ({{ $category->items->count() }} plat(s))</span>
                                                            </div>
                                                        </button>
                                                    </h2>
                                                    <div id="collapse{{ $category->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="heading{{ $category->id }}" data-bs-parent="#accordionCategories">
                                                        <div class="accordion-body">
                                                            @if($category->items->count() > 0)
                                                                <div class="table-responsive">
                                                                    <table class="table table-hover">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Nom</th>
                                                                                <th>Description</th>
                                                                                <th>Prix</th>
                                                                                <th>Statut</th>
                                                                                @if(auth()->user()->isRestaurateur() && $restaurant->user_id === auth()->id())
                                                                                    <th>Actions</th>
                                                                                @endif
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach($category->items as $item)
                                                                                <tr>
                                                                                    <td>{{ $item->name }}</td>
                                                                                    <td>{{ Str::limit($item->description, 50) }}</td>
                                                                                    <td>{{ number_format($item->price, 2, ',', ' ') }} €</td>
                                                                                    <td>
                                                                                        @if($item->is_active)
                                                                                            <span class="badge bg-success">Actif</span>
                                                                                        @else
                                                                                            <span class="badge bg-danger">Inactif</span>
                                                                                        @endif
                                                                                    </td>
                                                                                    @if(auth()->user()->isRestaurateur() && $restaurant->user_id === auth()->id())
                                                                                        <td>
                                                                                            <div class="btn-group">
                                                                                                <a href="{{ route('items.show', $item->id) }}" class="btn btn-sm btn-info">
                                                                                                    <i class="bx bx-show"></i>
                                                                                                </a>
                                                                                                <a href="{{ route('items.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                                                                                    <i class="bx bx-edit"></i>
                                                                                                </a>
                                                                                                <form action="{{ route('items.destroy', $item->id) }}" method="POST" style="margin: 0;">
                                                                                                    @csrf
                                                                                                    @method('delete')
                                                                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer ce plat ?')">
                                                                                                        <i class="bx bx-trash"></i>
                                                                                                    </button>
                                                                                                </form>
                                                                                            </div>
                                                                                        </td>
                                                                                    @endif
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            @else
                                                                <div class="alert alert-info">
                                                                    <i class="bx bx-info-circle me-1"></i> Aucun plat dans cette catégorie.
                                                                    @if(auth()->user()->isRestaurateur() && $restaurant->user_id === auth()->id())
                                                                        <a href="{{ route('restaurants.items.create', [$restaurant->id, 'category_id' => $category->id]) }}" class="alert-link">Ajouter un plat</a>.
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="bx bx-info-circle me-1"></i> Aucune catégorie pour ce restaurant.
                                            @if(auth()->user()->isRestaurateur() && $restaurant->user_id === auth()->id())
                                                <a href="{{ route('restaurants.categories.create', $restaurant) }}" class="alert-link">Créer une catégorie</a>.
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section des avis -->
    <div class="row mt-4" id="reviews">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bx bx-star me-2"></i>Avis clients</h4>
                    @if(auth()->check() && auth()->user()->isClient())
                        <a href="{{ route('restaurants.reviews.create', $restaurant->id) }}" class="btn btn-primary btn-sm">
                            <i class="bx bx-plus me-1"></i> Laisser un avis
                        </a>
                    @endif
                </div>
                
                <div class="card-body">
                    <!-- Note moyenne -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light h-100">
                                <div class="card-body text-center">
                                    <h2 class="mb-2">{{ number_format($averageRating, 1) }}<small class="text-muted">/5</small></h2>
                                    <div class="text-warning mb-3">
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= round($averageRating))
                                                <i class="bx bxs-star fs-3"></i>
                                            @else
                                                <i class="bx bx-star fs-3"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <p class="text-muted mb-0">{{ $reviews->count() }} avis client(s)</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            @if($reviews->isNotEmpty())
                                <div class="reviews-list">
                                    @foreach($reviews->take(5) as $review)
                                        <div class="card mb-3" id="review-{{ $review->id }}">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div>
                                                        <h5 class="mb-0">{{ $review->user->name }}</h5>
                                                        <div class="text-warning">
                                                            @for ($i = 1; $i <= 5; $i++)
                                                                @if ($i <= $review->rating)
                                                                    <i class="bx bxs-star"></i>
                                                                @else
                                                                    <i class="bx bx-star"></i>
                                                                @endif
                                                            @endfor
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">{{ $review->created_at->format('d/m/Y') }}</small>
                                                </div>
                                                <p class="mb-0">{{ $review->comment }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                    @if($reviews->count() > 5)
                                        <a href="{{ route('restaurants.reviews.index', $restaurant->id) }}" class="btn btn-outline-primary btn-sm d-block mt-3">
                                            Voir tous les avis ({{ $reviews->count() }})
                                        </a>
                                    @endif
                                </div>
                            @else
                                <div class="alert alert-info mb-0">
                                    <i class="bx bx-info-circle me-1"></i> Aucun avis pour ce restaurant pour le moment.
                                    @if(auth()->check() && auth()->user()->isClient())
                                        <a href="{{ route('restaurants.reviews.create', $restaurant->id) }}" class="alert-link">Soyez le premier à donner votre avis !</a>
                                    @endif
                                </div>
                            @endif
                        </div>
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
        // Si un hash est présent dans l'URL, faire défiler jusqu'à l'élément correspondant
        if(window.location.hash) {
            const hash = window.location.hash;
            const element = document.querySelector(hash);
            if(element) {
                setTimeout(function() {
                    window.scrollTo({
                        top: element.offsetTop - 70,
                        behavior: 'smooth'
                    });
                    
                    // Si c'est un accordéon, l'ouvrir
                    if(hash === '#items' || hash === '#categories') {
                        const accordionItem = document.querySelector('.accordion-collapse');
                        if(accordionItem && !accordionItem.classList.contains('show')) {
                            document.querySelector('.accordion-button').click();
                        }
                    }
                }, 100);
            }
        }
    });
</script>
@endsection
