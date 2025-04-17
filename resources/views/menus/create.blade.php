@extends('layouts.main')

@php
    use Illuminate\Support\Str;
@endphp

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Menus /</span> Créer un menu</h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <h5 class="card-header">Création d'un menu</h5>
                <div class="card-body">
                    <form action="{{ route('restaurants.menus.store', $restaurant->id) }}" method="POST" class="mb-3">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom du menu</label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Entrez le nom du menu" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" placeholder="Entrez une description du menu" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="price" class="form-label">Prix (€)</label>
                            <input type="number" id="price" name="price" class="form-control @error('price') is-invalid @enderror" placeholder="0.00" value="{{ old('price') }}" step="0.01" min="0" required>
                            <div class="form-text">Exemple : 19.99 pour 19,99 €</div>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Restaurant</label>
                            <input type="text" class="form-control" value="{{ $restaurant->name }}" disabled>
                        </div>
                        
                        <div class="mb-4" id="items-container">
                            <label class="form-label fw-bold">Plats inclus dans le menu</label>
                            <div class="alert alert-info mb-3">
                                <i class="bx bx-info-circle me-1"></i> Sélectionnez les plats à inclure dans ce menu.
                            </div>
                            <div id="items-list">
                                @if(isset($items) && count($items) > 0)
                                    <div class="row">
                                        @php
                                            // Regrouper les plats par catégorie
                                            $itemsByCategory = $items->groupBy(function($item) {
                                                return $item->category->name;
                                            });
                                        @endphp
                                        
                                        @foreach($itemsByCategory as $categoryName => $categoryItems)
                                            <div class="col-12 mb-3">
                                                <h6 class="border-bottom pb-2 mb-3">{{ $categoryName }}</h6>
                                                <div class="row">
                                                    @foreach($categoryItems as $item)
                                                        <div class="col-md-6 col-lg-4 mb-2">
                                                            <div class="card h-100 border-primary border-opacity-25 hover-shadow">
                                                                <div class="card-body p-3">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" name="items[]" id="item_{{ $item->id }}" value="{{ $item->id }}" {{ is_array(old('items')) && in_array($item->id, old('items')) ? 'checked' : '' }}>
                                                                        <label class="form-check-label w-100" for="item_{{ $item->id }}">
                                                                            <div class="d-flex justify-content-between align-items-center">
                                                                                <span class="fw-semibold">{{ $item->name }}</span>
                                                                                <span class="badge bg-primary">{{ number_format($item->price, 2, ',', ' ') }} €</span>
                                                                            </div>
                                                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                                                @if($item->description)
                                                                                    <small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                                                                @endif
                                                                                @if(isset($item->is_active))
                                                                                    @if($item->is_active)
                                                                                        <span class="badge bg-success ms-1">Actif</span>
                                                                                    @else
                                                                                        <span class="badge bg-danger ms-1">Inactif</span>
                                                                                    @endif
                                                                                @endif
                                                                            </div>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="bx bx-error-circle me-1"></i> Aucun plat disponible pour ce restaurant. Veuillez d'abord <a href="{{ route('items.create') }}?restaurant_id={{ $restaurant->id }}">créer des plats</a>.
                                    </div>
                                @endif
                            </div>
                            @error('items')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bx bx-save me-1"></i> Enregistrer
                            </button>
                            <a href="{{ route('restaurants.menus.index', $restaurant->id) }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Retour
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Aucun besoin de charger les plats via AJAX puisqu'ils sont déjà chargés par le contrôleur
        console.log('Plats déjà chargés par le contrôleur, pas besoin de requête AJAX');
    });
</script>
@endsection
