@extends('layouts.main')
@php use Illuminate\Support\Str; @endphp

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Administration / <a href="{{ route('admin.reviews.index') }}">Avis</a> /</span> Modifier un avis
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Formulaire de modification d'un avis</h5>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.reviews.update', $review->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="user_id" class="form-label">Client</label>
                        <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                            <option value="">Sélectionner un client</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', $review->user_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="restaurant_id" class="form-label">Restaurant</label>
                        <select class="form-select @error('restaurant_id') is-invalid @enderror" id="restaurant_id" name="restaurant_id" required>
                            <option value="">Sélectionner un restaurant</option>
                            @foreach($restaurants as $restaurant)
                                <option value="{{ $restaurant->id }}" {{ old('restaurant_id', $review->restaurant_id) == $restaurant->id ? 'selected' : '' }}>
                                    {{ $restaurant->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('restaurant_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Note</label>
                    <div class="rating-container">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="rating" id="rating1" value="1" {{ old('rating', $review->rating) == '1' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="rating1">1 <i class="bx bxs-star text-warning"></i></label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="rating" id="rating2" value="2" {{ old('rating', $review->rating) == '2' ? 'checked' : '' }}>
                            <label class="form-check-label" for="rating2">2 <i class="bx bxs-star text-warning"></i></label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="rating" id="rating3" value="3" {{ old('rating', $review->rating) == '3' ? 'checked' : '' }}>
                            <label class="form-check-label" for="rating3">3 <i class="bx bxs-star text-warning"></i></label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="rating" id="rating4" value="4" {{ old('rating', $review->rating) == '4' ? 'checked' : '' }}>
                            <label class="form-check-label" for="rating4">4 <i class="bx bxs-star text-warning"></i></label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="rating" id="rating5" value="5" {{ old('rating', $review->rating) == '5' ? 'checked' : '' }}>
                            <label class="form-check-label" for="rating5">5 <i class="bx bxs-star text-warning"></i></label>
                        </div>
                    </div>
                    @error('rating')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="comment" class="form-label">Commentaire</label>
                    <textarea class="form-control @error('comment') is-invalid @enderror" id="comment" name="comment" rows="4" required>{{ old('comment', $review->comment) }}</textarea>
                    @error('comment')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="is_approved" name="is_approved" {{ old('is_approved', $review->is_approved) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_approved">
                        Approuver cet avis (visible publiquement)
                    </label>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bx bx-save me-1"></i> Mettre à jour
                    </button>
                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-x me-1"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
