@extends('layout.main')

@section('main')
    <h1>Détails de l'item</h1>

    <table style="border: 1px solid black; border-collapse: collapse;">
        <tr>
            <th style="border: 1px solid black;">ID</th>
            <td style="border: 1px solid black;">{{ $item->id }}</td>
        </tr>
        <tr>
            <th style="border: 1px solid black;">Nom</th>
            <td style="border: 1px solid black;">{{ $item->name }}</td>
        </tr>
        <tr>
            <th style="border: 1px solid black;">Coût</th>
            <td style="border: 1px solid black;">{{ $item->cost ? ($item->cost / 100) . '€' : 'Non défini' }}</td>
        </tr>
        <tr>
            <th style="border: 1px solid black;">Prix</th>
            <td style="border: 1px solid black;">{{ $item->price / 100 }}€</td>
        </tr>
        <tr>
            <th style="border: 1px solid black;">Actif</th>
            <td style="border: 1px solid black;">{{ $item->is_active ? 'Oui' : 'Non' }}</td>
        </tr>
        <tr>
            <th style="border: 1px solid black;">Catégorie</th>
            <td style="border: 1px solid black;">{{ $item->category->name }}</td>
        </tr>
    </table>

    <a href="{{ route('items.index') }}">Retour</a>
    <a href="{{ route('items.edit', $item->id) }}">Modifier</a>

    <form action="{{ route('items.destroy', $item->id) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit">Supprimer</button>
    </form>
@endsection
