<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Category;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Item::with('category')->get();
        return view('items.index',compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('items.create',compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cost' => 'nullable|integer',
            'price' => 'required|integer',
            'is_active' => 'boolean',
            'category_id' => 'required|exists:categories,id',
        ]);

        Item::create($request->all());

        return redirect()->route('items.index')->with('success', 'Item ajouté avec succès');
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        return view('items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        $categories = Category::all();
        return view('items.edit', compact('item', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cost' => 'nullable|integer',
            'price' => 'required|integer',
            'is_active' => 'boolean',
            'category_id' => 'required|exists:categories,id',
        ]);

        $item->update($request->all());

        return redirect()->route('items.index')->with('success', 'Item mis à jour');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        $item->delete();
        return redirect()->route('items.index')->with('success', 'Item supprimé');
    }
}
