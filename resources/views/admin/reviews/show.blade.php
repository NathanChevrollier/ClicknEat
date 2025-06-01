@extends('layouts.main')
@php use Illuminate\Support\Str; @endphp

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Administration / <a href="{{ route('admin.reviews.index') }}">Avis</a> /</span> Détails de l'avis
    </h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Informations sur l'avis</h5>
            <div>
                <a href="{{ route('admin.reviews.edit', $review->id) }}" class="btn btn-primary me-2">
                    <i class="bx bx-edit me-1"></i> Modifier
                </a>
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Retour à la liste
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="fw-semibold">Restaurant :</h6>
                    <p><a href="{{ route('admin.restaurants.show', $review->restaurant_id) }}">{{ $review->restaurant->name }}</a></p>
                    
                    <h6 class="fw-semibold mt-3">Client :</h6>
                    <p><a href="{{ route('admin.users.show', $review->user_id) }}">{{ $review->user->name }} ({{ $review->user->email }})</a></p>
                    
                    <h6 class="fw-semibold mt-3">Note :</h6>
                    <div class="text-warning">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= $review->rating)
                                <i class="bx bxs-star"></i>
                            @else
                                <i class="bx bx-star"></i>
                            @endif
                        @endfor
                        <span class="text-dark ms-2">({{ $review->rating }}/5)</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-semibold">Créé le :</h6>
                    <p>{{ $review->created_at->format('d/m/Y à H:i') }}</p>
                    
                    <h6 class="fw-semibold mt-3">Dernière modification :</h6>
                    <p>{{ $review->updated_at->format('d/m/Y à H:i') }}</p>
                    
                    <h6 class="fw-semibold mt-3">Statut :</h6>
                    <p>
                        @if($review->is_approved)
                            <span class="badge bg-success">Approuvé</span>
                        @else
                            <span class="badge bg-warning">En attente d'approbation</span>
                        @endif
                    </p>
                </div>
            </div>
            
            <div class="mt-3">
                <h6 class="fw-semibold">Commentaire :</h6>
                <div class="p-3 bg-light rounded">
                    {{ $review->comment }}
                </div>
            </div>
            
            <div class="mt-4">
                <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet avis ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-trash me-1"></i> Supprimer cet avis
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
