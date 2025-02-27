@extends('layout.main')

@section('main')
    <h1>Modifier l'item</h1>

    <form action="{{ route('items.update', $item->id) }}" method="POST">
        @csrf
        @method('PUT')

        <label>Nom :</label>
        <input type="text" name="name" value="{{ $item->name }}" required>

        <label>Coût :</label>
        <input type="number" name="cost" value="{{ $item->cost }}">

        <label>Prix :</label>
        <input type="number" name="price" value="{{ $item->price }}" required>

        <label>Catégorie :</label>
        <select name="category_id" required>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ $category->id == $item->category_id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>

        <label>Actif :</label>
        <input type="checkbox" name="is_active" {{ $item->is_active ? 'checked' : '' }}>

        <button type="submit">Mettre à jour</button>
    </form>

    <a href="{{ route('items.index') }}">Annuler</a>
@endsection
