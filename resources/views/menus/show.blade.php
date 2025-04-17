@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Menus /</span> Détails du menu
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $menu->name }}</h5>
                    <div>
                        <a href="{{ route('menus.index', ['restaurant_id' => $menu->restaurant_id]) }}" class="btn btn-secondary me-2">
                            <i class="bx bx-arrow-back me-1"></i> Retour aux menus
                        </a>
                        @if(auth()->user()->isRestaurateur())
                            <a href="{{ route('menus.edit', $menu->id) }}" class="btn btn-warning me-2">
                                <i class="bx bx-edit me-1"></i> Modifier
                            </a>
                            <form action="{{ route('menus.destroy', $menu->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer ce menu ?')">
                                    <i class="bx bx-trash me-1"></i> Supprimer
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="fw-semibold">Restaurant</h6>
                            <p>
                                <a href="{{ route('restaurants.show', $menu->restaurant->id) }}">
                                    {{ $menu->restaurant->name }}
                                </a>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-semibold">Prix</h6>
                            <p class="text-primary fw-semibold">{{ number_format($menu->price, 2, ',', ' ') }} u20ac</p>
                        </div>
                    </div>

                    @if($menu->description)
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h6 class="fw-semibold">Description</h6>
                                <p>{{ $menu->description }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="fw-semibold mb-3">Plats inclus dans ce menu</h6>
                            @if($menu->items->count() > 0)
                                <div class="row">
                                    @foreach($menu->items as $item)
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title">{{ $item->name }}</h5>
                                                    <p class="card-text text-truncate mb-2">{{ $item->description }}</p>
                                                    <p class="card-text text-primary fw-semibold">{{ number_format($item->price, 2, ',', ' ') }} u20ac</p>
                                                    <a href="{{ route('items.show', $item->id) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="bx bx-show me-1"></i> Voir le plat
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="bx bx-error-circle me-1"></i> Aucun plat n'est inclus dans ce menu.
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
