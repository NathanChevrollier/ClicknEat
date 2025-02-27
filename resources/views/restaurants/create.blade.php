@extends('layout.main')

@section('main')
    <h1>Creation restaurant</h1>

    <a href="{{ route('restaurants.index') }}">Retour à la liste</a>

    <form action="{{ route('restaurants.store') }}" method="POST">
        @csrf
        <label for="name">Nom : </label>
        <input type="text" id="name" name="name" placeholder="Nom">
        <button type="submit">Envoyer</button>
    </form>
@endsection