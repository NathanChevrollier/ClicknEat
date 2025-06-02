@php
use Illuminate\Support\Str;
@endphp

@extends('layouts.main')

@section('title', 'Mes avis')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Mes avis</h5>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(count($reviews) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Restaurant</th>
                                <th>Note</th>
                                <th>Commentaire</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reviews as $review)
                                <tr>
                                    <td>
                                        <a href="{{ route('restaurants.show', $review->restaurant->id) }}">
                                            {{ $review->restaurant->name }}
                                        </a>
                                    </td>
                                    <td>
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <i class="bx bxs-star text-warning"></i>
                                            @else
                                                <i class="bx bx-star text-muted"></i>
                                            @endif
                                        @endfor
                                    </td>
                                    <td>
                                        {{ Str::limit($review->comment, 50) }}
                                    </td>
                                    <td>{{ $review->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('restaurants.reviews.edit', [$review->restaurant->id, $review->id]) }}" class="btn btn-sm btn-primary rounded-pill">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            <form action="{{ route('restaurants.reviews.destroy', [$review->restaurant->id, $review->id]) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger rounded-pill" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet avis ?')">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-4">
                    {{ $reviews->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-2"></i>
                    Vous n'avez pas encore laissé d'avis sur des restaurants.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
