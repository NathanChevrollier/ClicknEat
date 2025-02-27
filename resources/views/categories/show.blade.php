@extends('layout.main')

@section('main')
    <h1>Categories</h1>

    <a href="{{ route('categories.index') }}">Retour à la liste</a>
    <a href="{{ route('categories.create') }}">Créer un category</a>

    <ul>
        <li>id : {{ $category->id }}</li>
        <li>nom : {{ $category->name }}</li>
        <li>created_at : {{ $category->created_at }}</li>
        <li>updated_at : {{ $category->updated_at }}</li>
    </ul>

    <h2>Restaurant : {{ $category->restaurant->name }}</h2>

    <a href="{{ route('restaurants.show', $category->restaurant->id) }}" title="Voir le restaurant">Aller au restaurant {{ $category->restaurant->name }}</a>

@endsection