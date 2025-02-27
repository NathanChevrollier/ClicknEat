@extends('layout.main')

@section('main')
    <h1>Categories</h1>

    <a href="{{ route('categories.create') }}">Créer une categorie</a>

    <table style="border: 1px solid black; border-collapse: collapse;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Restaurant</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $category)
                <tr>
                    <td>{{ $category->id }}</td>
                    <td>{{ $category->name }}</td>
                    <td>
                        <a href="{{ route('restaurants.show', $category->restaurant->id) }}" title="Voir le restaurant">{{ $category->restaurant->name }}</a>
                    </td>
                    <td>
                        <div style="display: flex;">
                            <a style="margin-right: 8px;" href="{{ route('categories.show', $category->id) }}">Voir</a>
                            <a style="margin-right: 8px;" href="{{ route('categories.edit', $category->id) }}">Modifier</a>
                            <form action="{{ route('categories.destroy', $category->id) }}" method="POST">
                                @csrf
                                @method('delete')
                                <input type="hidden" name="id" value="{{ $category->id }}">
                                <button type="submit">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

@section('scripts')
    <script>
        console.log("scripts !");
    </script>
@endsection