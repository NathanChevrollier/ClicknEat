@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Restaurants / {{ $restaurant->name }} /</span> Détail de l'avis
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Avis de {{ $review->user->name }}</h5>
                    <div>
                        <a href="{{ route('restaurants.reviews.index', $restaurant->id) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bx bx-arrow-back me-1"></i> Retour aux avis
                        </a>
                        @if(auth()->check() && (auth()->id() === $review->user_id || auth()->user()->isAdmin()))
                            <a href="{{ route('restaurants.reviews.edit', [$restaurant->id, $review->id]) }}" class="btn btn-primary btn-sm">
                                <i class="bx bx-edit me-1"></i> Modifier
                            </a>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <div class="mb-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $review->rating)
                                            <i class="bx bxs-star text-warning"></i>
                                        @else
                                            <i class="bx bx-star text-warning"></i>
                                        @endif
                                    @endfor
                                </div>
                                <div class="text-muted small">
                                    Publié le {{ $review->created_at->format('d/m/Y à H:i') }}
                                    @if($review->created_at != $review->updated_at)
                                        <span>(Modifié le {{ $review->updated_at->format('d/m/Y à H:i') }})</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="review-content p-3 bg-light rounded">
                            <p class="mb-0">{{ $review->comment }}</p>
                        </div>
                    </div>
                    
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <div class="mt-4 p-3 border rounded">
                            <h6>Administration</h6>
                            <div class="d-flex align-items-center">
                                <span class="me-3">Statut :</span>
                                @if($review->is_approved)
                                    <span class="badge bg-success me-2">Approuvé</span>
                                @else
                                    <span class="badge bg-danger me-2">En attente d'approbation</span>
                                @endif
                                
                                <form action="{{ route('reviews.approve', $review->id) }}" method="POST" class="ms-auto">
                                    @csrf
                                    @if($review->is_approved)
                                        <input type="hidden" name="is_approved" value="0">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="bx bx-x-circle me-1"></i> Rejeter
                                        </button>
                                    @else
                                        <input type="hidden" name="is_approved" value="1">
                                        <button type="submit" class="btn btn-outline-success btn-sm">
                                            <i class="bx bx-check-circle me-1"></i> Approuver
                                        </button>
                                    @endif
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
