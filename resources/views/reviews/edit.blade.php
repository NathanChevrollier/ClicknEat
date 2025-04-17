@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Restaurants / {{ $restaurant->name }} /</span> Modifier votre avis
    </h4>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <h5 class="card-header">Modifier votre avis sur {{ $restaurant->name }}</h5>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('restaurants.reviews.update', [$restaurant->id, $review->id]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label class="form-label">Note</label>
                            <div class="rating-stars mb-2">
                                <div class="d-flex">
                                    @for($i = 1; $i <= 5; $i++)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input visually-hidden" type="radio" name="rating" id="rating{{ $i }}" value="{{ $i }}" {{ old('rating', $review->rating) == $i ? 'checked' : '' }} required>
                                            <label class="form-check-label star-label" for="rating{{ $i }}">
                                                <i class="bx bx-star star-icon fs-2"></i>
                                            </label>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                            @error('rating')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="comment" class="form-label">Commentaire</label>
                            <textarea class="form-control @error('comment') is-invalid @enderror" id="comment" name="comment" rows="5" placeholder="Partagez votre expérience avec ce restaurant...">{{ old('comment', $review->comment) }}</textarea>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        @if(auth()->user()->isAdmin())
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_approved" name="is_approved" {{ old('is_approved', $review->is_approved) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_approved">Avis approuvé</label>
                                </div>
                                <small class="text-muted">Seuls les avis approuvés sont visibles publiquement.</small>
                            </div>
                        @endif
                        
                        <div class="mt-4">
                            <a href="{{ route('restaurants.reviews.index', $restaurant->id) }}" class="btn btn-outline-secondary me-2">
                                <i class="bx bx-arrow-back me-1"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-check me-1"></i> Mettre à jour mon avis
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <h5 class="card-header">Informations</h5>
                <div class="card-body">
                    <p>Votre avis aidera les autres utilisateurs à découvrir ce restaurant.</p>
                    <p class="mb-0"><strong>Conseils pour un bon avis :</strong></p>
                    <ul class="mt-2">
                        <li>Soyez objectif et honnête</li>
                        <li>Décrivez votre expérience personnelle</li>
                        <li>Mentionnez la qualité des plats, le service et l'ambiance</li>
                        <li>Respectez les règles de la communauté</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gérer l'affichage des étoiles
        const starLabels = document.querySelectorAll('.star-label');
        const starIcons = document.querySelectorAll('.star-icon');
        const ratingInputs = document.querySelectorAll('input[name="rating"]');
        
        // Initialiser les étoiles selon la valeur sélectionnée
        function updateStars() {
            const selectedRating = document.querySelector('input[name="rating"]:checked')?.value || 0;
            
            starIcons.forEach((icon, index) => {
                if (index < selectedRating) {
                    icon.classList.remove('bx-star');
                    icon.classList.add('bxs-star');
                    icon.classList.add('text-warning');
                } else {
                    icon.classList.remove('bxs-star');
                    icon.classList.remove('text-warning');
                    icon.classList.add('bx-star');
                }
            });
        }
        
        // Gérer le survol des étoiles
        starLabels.forEach((label, index) => {
            label.addEventListener('mouseenter', () => {
                starIcons.forEach((icon, i) => {
                    if (i <= index) {
                        icon.classList.remove('bx-star');
                        icon.classList.add('bxs-star');
                        icon.classList.add('text-warning');
                    } else {
                        icon.classList.remove('bxs-star');
                        icon.classList.remove('text-warning');
                        icon.classList.add('bx-star');
                    }
                });
            });
            
            label.addEventListener('mouseleave', updateStars);
        });
        
        // Mettre à jour les étoiles lorsqu'une note est sélectionnée
        ratingInputs.forEach(input => {
            input.addEventListener('change', updateStars);
        });
        
        // Initialiser l'affichage
        updateStars();
    });
</script>
@endsection
