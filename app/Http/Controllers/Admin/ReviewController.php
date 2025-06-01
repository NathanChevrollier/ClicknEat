<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    /**
     * Affiche la liste des avis
     */
    public function index()
    {
        $reviews = Review::with(['restaurant', 'user'])->paginate(10);
        return view('admin.reviews.index', compact('reviews'));
    }

    /**
     * Affiche le formulaire de cru00e9ation d'un avis
     */
    public function create()
    {
        $restaurants = Restaurant::all();
        $users = User::where('role', 'client')->get();
        return view('admin.reviews.create', compact('restaurants', 'users'));
    }

    /**
     * Enregistre un nouvel avis
     */
    public function store(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'user_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:3',
        ]);

        // Vérifier si un avis existe déjà pour cet utilisateur et ce restaurant
        $existingReview = Review::where('user_id', $request->user_id)
                               ->where('restaurant_id', $request->restaurant_id)
                               ->first();

        if ($existingReview) {
            return redirect()->route('admin.reviews.edit', $existingReview->id)
                ->with('error', 'Cet utilisateur a déjà laissé un avis pour ce restaurant. Vous pouvez modifier l\'avis existant.');
        }

        $review = new Review();
        $review->restaurant_id = $request->restaurant_id;
        $review->user_id = $request->user_id;
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->is_approved = $request->has('is_approved') ? 1 : 0;
        $review->save();

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Avis créé avec succès');
    }

    /**
     * Affiche les du00e9tails d'un avis
     */
    public function show(Review $review)
    {
        return view('admin.reviews.show', compact('review'));
    }

    /**
     * Affiche le formulaire de modification d'un avis
     */
    public function edit(Review $review)
    {
        $restaurants = Restaurant::all();
        $users = User::where('role', 'client')->get();
        return view('admin.reviews.edit', compact('review', 'restaurants', 'users'));
    }

    /**
     * Met u00e0 jour un avis
     */
    public function update(Request $request, Review $review)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'user_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:3',
        ]);

        $review->restaurant_id = $request->restaurant_id;
        $review->user_id = $request->user_id;
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->is_approved = $request->has('is_approved');
        $review->save();

        return redirect()->route('admin.reviews.show', $review->id)
            ->with('success', 'Avis mis à jour avec succès');
    }

    /**
     * Supprime un avis
     */
    public function destroy(Review $review)
    {
        $review->delete();
        return redirect()->route('admin.reviews.index')
            ->with('success', 'Avis supprimé avec succès');
    }
}
