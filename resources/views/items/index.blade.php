@extends('layouts.main')

@section('content')
    <h1>Liste des items</h1>
    <a href="{{ route('items.create') }}">Ajouter un item</a>
    <ul>
        @foreach($items as $item)
            <li>{{ $item->name }} - Prix: {{ $item->price / 100 }}€
                <a href="{{ route('items.show', $item->id) }}">Voir</a>
                <a href="{{ route('items.edit', $item->id) }}">Modifier</a>
                <form action="{{ route('items.destroy', $item->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Supprimer</button>
                </form>
            </li>
        @endforeach
    </ul>
@endsection
