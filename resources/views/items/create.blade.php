@extends('layouts.main')

@section('content')
    <h1>Ajouter un item</h1>
    <form action="{{ route('items.store') }}" method="POST">
        @csrf
        <input type="text" name="name" placeholder="Nom de l'item" required>
        <input type="number" name="cost" placeholder="Coût (centimes)">
        <input type="number" name="price" placeholder="Prix (centimes)" required>
        <select name="category_id" required>
            @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
        <button type="submit">Ajouter</button>
    </form>
@endsection
