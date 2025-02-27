@extends('layout.main')

@section('main')
    <h1>Restaurants</h1>

    <a href="{{ route('restaurants.index') }}">Retour à la liste</a>
    <a href="{{ route('restaurants.create') }}">Créer un restaurant</a>

    <ul>
        <li>id : {{ $restaurant->id }}</li>
        <li>nom : {{ $restaurant->name }}</li>
        <li>created_at : {{ $restaurant->created_at }}</li>
        <li>updated_at : {{ $restaurant->updated_at }}</li>
    </ul>

    <h2>Catégories</h2>

    <ul>
        @foreach($restaurant->categories as $category)
            <li>
                <a href="{{ route('categories.show', $category->id) }}" title="Voir la catégorie">{{ $category->name }}</a>
            </li>
        @endforeach
    </ul>
    
@endsection
