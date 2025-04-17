@extends('layouts.main')

@php
    use Illuminate\Support\Str;
@endphp

@section('main')

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Restaurants</h3>
        @if(auth()->user()->isRestaurateur())
        <div>
            <a href="{{ route('restaurants.create') }}" class="btn btn-primary">Ajouter un restaurant</a>
        </div>
        @endif
    </div>
    <!-- /.card-header -->
    <div class="card-body">
      <!-- Filtres de recherche -->
      <div class="row mb-4">
        <div class="col-md-12">
          <div class="card">
            <div class="card-body">
              <form action="{{ route('restaurants.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                  <label for="search" class="form-label">Rechercher</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Nom du restaurant..." value="{{ request('search') }}">
                  </div>
                </div>
                <div class="col-md-3">
                  <label for="category" class="form-label">Catégorie</label>
                  <select class="form-select" id="category" name="category">
                    <option value="">Toutes les catégories</option>
                    <option value="italien" {{ request('category') == 'italien' ? 'selected' : '' }}>Italien</option>
                    <option value="asiatique" {{ request('category') == 'asiatique' ? 'selected' : '' }}>Asiatique</option>
                    <option value="fast-food" {{ request('category') == 'fast-food' ? 'selected' : '' }}>Fast Food</option>
                    <option value="francais" {{ request('category') == 'francais' ? 'selected' : '' }}>Français</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label for="sort" class="form-label">Trier par</label>
                  <select class="form-select" id="sort" name="sort">
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nom (A-Z)</option>
                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nom (Z-A)</option>
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Plus récent</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Plus ancien</option>
                  </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      
      @if(count($restaurants) > 0)
      <div class="row">
        @foreach($restaurants as $restaurant)
            <div class="col-md-4 mb-4">
                @include('restaurants.partials.restaurant-card', ['restaurant' => $restaurant])
            </div>
        @endforeach
      </div>
      <div class="d-flex justify-content-center mt-4">
        {{ $restaurants->links() }}
      </div>
      @else
      <div class="alert alert-info">
        <i class="bx bx-info-circle me-1"></i> Aucun restaurant trouvé.
      </div>
      @endif
    </div>
    <!-- /.card-body -->
</div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Code JavaScript si nécessaire
        });
    </script>
@endsection