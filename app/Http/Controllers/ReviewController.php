<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Afficher la liste des avis d'un restaurant.
     */
    public function index(Request $request, $restaurantId)
    {
        $restaurant = Restaurant::with(['reviews' => function($query) {
            $query->where('is_approved', true)
                  ->with('user')
                  ->orderBy('created_at', 'desc');
        }])->findOrFail($restaurantId);
        
        return view('reviews.index', compact('restaurant'));
    }

    /**
     * Afficher le formulaire de création d'un avis.
     */
    public function create(Request $request, $restaurantId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        
        // Vérifier si l'utilisateur a déjà laissé un avis pour ce restaurant
        $existingReview = Review::where('user_id', Auth::id())
                               ->where('restaurant_id', $restaurantId)
                               ->first();
        
        if ($existingReview) {
            return redirect()->route('restaurants.reviews.edit', [$restaurantId, $existingReview->id])
                             ->with('info', 'Vous avez déjà laissé un avis pour ce restaurant. Vous pouvez le modifier ci-dessous.');
        }
        
        // Vérifier si l'utilisateur a déjà commandé ou réservé dans ce restaurant
        $hasOrdered = Auth::user()->orders()
                                  ->where('restaurant_id', $restaurantId)
                                  ->exists();
        
        $hasReserved = Auth::user()->reservations()
                                   ->where('restaurant_id', $restaurantId)
                                   ->exists();
        
        if (!$hasOrdered && !$hasReserved && !Auth::user()->isAdmin()) {
            return redirect()->route('restaurants.show', $restaurantId)
                             ->with('error', 'Vous devez avoir commandé ou réservé dans ce restaurant pour laisser un avis.');
        }
        
        return view('reviews.create', compact('restaurant'));
    }

    /**
     * Enregistrer un nouvel avis.
     */
    public function store(Request $request, $restaurantId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);
        
        // Vérifier si l'utilisateur a déjà laissé un avis pour ce restaurant
        $existingReview = Review::where('user_id', Auth::id())
                               ->where('restaurant_id', $restaurantId)
                               ->first();
        
        if ($existingReview) {
            return redirect()->route('restaurants.reviews.edit', [$restaurantId, $existingReview->id])
                             ->with('info', 'Vous avez déjà laissé un avis pour ce restaurant. Vous pouvez le modifier ci-dessous.');
        }
        
        $review = new Review([
            'user_id' => Auth::id(),
            'restaurant_id' => $restaurantId,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => true, // Par défaut, les avis sont approuvés automatiquement
        ]);
        
        $review->save();
        
        return redirect()->route('restaurants.show', $restaurantId)
                         ->with('success', 'Votre avis a été publié avec succès. Merci pour votre retour !');
    }

    /**
     * Afficher un avis spécifique.
     */
    public function show($restaurantId, $reviewId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        $review = Review::where('restaurant_id', $restaurantId)
                       ->where('id', $reviewId)
                       ->with('user')
                       ->firstOrFail();
        
        return view('reviews.show', compact('restaurant', 'review'));
    }

    /**
     * Afficher le formulaire de modification d'un avis.
     */
    public function edit($restaurantId, $reviewId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        $review = Review::where('restaurant_id', $restaurantId)
                       ->where('id', $reviewId)
                       ->firstOrFail();
        
        // Vérifier que l'utilisateur est le propriétaire de l'avis ou un administrateur
        if ($review->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Vous n\'avez pas le droit de modifier cet avis.');
        }
        
        return view('reviews.edit', compact('restaurant', 'review'));
    }

    /**
     * Mettre à jour un avis.
     */
    public function update(Request $request, $restaurantId, $reviewId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);
        $review = Review::where('restaurant_id', $restaurantId)
                       ->where('id', $reviewId)
                       ->firstOrFail();
        
        // Vérifier que l'utilisateur est le propriétaire de l'avis ou un administrateur
        if ($review->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Vous n\'avez pas le droit de modifier cet avis.');
        }
        
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);
        
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        
        // Si c'est un admin qui modifie, il peut changer le statut d'approbation
        if (Auth::user()->isAdmin() && $request->has('is_approved')) {
            $review->is_approved = $request->is_approved;
        }
        
        $review->save();
        
        return redirect()->route('restaurants.show', $restaurantId)
                         ->with('success', 'Votre avis a été mis à jour avec succès.');
    }

    /**
     * Supprimer un avis.
     */
    public function destroy($restaurantId, $reviewId)
    {
        $review = Review::where('restaurant_id', $restaurantId)
                       ->where('id', $reviewId)
                       ->firstOrFail();
        
        // Vérifier que l'utilisateur est le propriétaire de l'avis ou un administrateur
        if ($review->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Vous n\'avez pas le droit de supprimer cet avis.');
        }
        
        $review->delete();
        
        return redirect()->route('restaurants.show', $restaurantId)
                         ->with('success', 'L\'avis a été supprimé avec succès.');
    }
    
    /**
     * Approuver ou rejeter un avis (pour les administrateurs).
     */
    public function approve(Request $request, $reviewId)
    {
        // Vérifier que l'utilisateur est un administrateur
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Vous n\'avez pas le droit d\'approuver ou rejeter des avis.');
        }
        
        $review = Review::findOrFail($reviewId);
        $review->is_approved = $request->is_approved;
        $review->save();
        
        $status = $request->is_approved ? 'approuvé' : 'rejeté';
        
        return redirect()->back()->with('success', "L'avis a été {$status} avec succès.");
    }
}
