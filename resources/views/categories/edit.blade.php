@extends('layout.main')

@section('main')
    <h1>Modification category</h1>

    <a href="{{ route('categories.index') }}">Retour à la liste</a>

    <form action="{{ route('categories.update', $category->id ) }}" method="POST">
        @csrf 
        @method('put')
        <div>
            <label for="name">Nom : </label>
            <input type="text" id="name" name="name" placeholder="Nom" value="{{ $category->name }}">
        </div>
        <div>
            <label for="restaurant_id">Restaurant</label>
            <select name="restaurant_id" id="restaurant_id">
                @foreach($restaurants as $restaurant)
                    @if($restaurant->id == $category->restaurant->id)
                        <option value="{{ $restaurant->id }}" selected="selected">{{ $restaurant->name }}</option>
                    @else
                        <option value="{{ $restaurant->id }}">{{ $restaurant->name }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <button type="submit">Envoyer</button>
    </form>
@endsection