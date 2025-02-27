<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\Category;

class RestaurantController extends Controller
{
    public function index() {
        return view('restaurants.index', [
            'restaurants' => Restaurant::all()
        ]);
    }

    public function create() {
        return view('restaurants.create');
    }

    public function store(Request $request) {
        Restaurant::create( $request->all() );
        
        return redirect()->route('restaurants.index');
    }

    public function show($id) {
        return view('restaurants.show', [
            'restaurant' => Restaurant::findOrFail($id)
        ]);
    }

    public function edit($id) {
        return view('restaurants.edit', [
            'restaurant' => Restaurant::findOrFail($id)
        ]);
    }

    public function update(Request $request, $id) {
        $restaurant = Restaurant::findOrFail($id);

        $restaurant->name = $request->get('name');
        $restaurant->save();

        return redirect()->route('restaurants.index');
    }

    public function destroy(Request $request, $id) {
        if($request->get('id') == $id) {
            Restaurant::destroy($id);
        }
        return redirect()->route('restaurants.index');
    }
}
