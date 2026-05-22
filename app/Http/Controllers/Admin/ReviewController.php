<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Avis en attente de modération
     */
    public function pending()
    {
        $reviews = Review::with(['reviewer', 'reviewed', 'property', 'booking'])
            ->pending()
            ->latest()
            ->paginate(20);

        return view('admin.reviews.pending', compact('reviews'));
    }

    /**
     * Tous les avis
     */
    public function index(Request $request)
    {
        $query = Review::with(['reviewer', 'reviewed', 'property']);

        if ($request->filled('approved')) {
            $query->where('is_approved', $request->boolean('approved'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $reviews = $query->latest()->paginate(20);

        return view('admin.reviews.index', compact('reviews'));
    }

    /**
     * Approuver un avis
     */
    public function approve(Review $review)
    {
        $review->approve();

        return back()->with('success', 'Avis approuvé.');
    }

    /**
     * Rejeter un avis
     */
    public function reject(Request $request, Review $review)
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $review->update([
            'is_approved' => false,
            'moderation_note' => $request->reason,
        ]);

        return back()->with('success', 'Avis rejeté.');
    }

    /**
     * Supprimer un avis
     */
    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with('success', 'Avis supprimé.');
    }
}


