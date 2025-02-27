@extends('layout.main')

@section('main')
    <h1>Modification restaurant</h1>

    <a href="{{ route('restaurants.index') }}">Retour à la liste</a>

    <form action="{{ route('restaurants.update', $restaurant->id) }}" method="POST">
        @csrf
        @method('put')
        <label for="name">Nom : </label>
        <input type="text" id="name" name="name" placeholder="Nom" value="{{ $restaurant->name }}">
        <button type="submit">Envoyer</button>
    </form>
@endsection