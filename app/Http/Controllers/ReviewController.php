<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Booking;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Formulaire pour laisser un avis
     */
    public function create(Booking $booking)
    {
        $this->authorize('review', $booking);

        if (!$booking->canBeReviewed(Auth::user())) {
            return back()->with('error', 'Vous ne pouvez pas laisser d\'avis pour cette réservation.');
        }

        return view('reviews.create', compact('booking'));
    }

    /**
     * Enregistrer un avis
     */
    public function store(Request $request, Booking $booking)
    {
        $this->authorize('review', $booking);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:20', 'max:1000'],
            'type' => ['required', 'in:property,owner,tenant'],
        ]);

        $user = Auth::user();

        // Déterminer qui/quoi est évalué
        $reviewedId = null;
        $propertyId = null;

        switch ($validated['type']) {
            case 'property':
                $propertyId = $booking->property_id;
                break;
            case 'owner':
                if ($user->id !== $booking->tenant_id) {
                    return back()->with('error', 'Seul le locataire peut évaluer le propriétaire.');
                }
                $reviewedId = $booking->owner_id;
                break;
            case 'tenant':
                if ($user->id !== $booking->owner_id) {
                    return back()->with('error', 'Seul le propriétaire peut évaluer le locataire.');
                }
                $reviewedId = $booking->tenant_id;
                break;
        }

        // Vérifier qu'un avis n'existe pas déjà
        $existingReview = Review::where('booking_id', $booking->id)
            ->where('reviewer_id', $user->id)
            ->where('type', $validated['type'])
            ->exists();

        if ($existingReview) {
            return back()->with('error', 'Vous avez déjà laissé un avis de ce type.');
        }

        $review = Review::create([
            'booking_id' => $booking->id,
            'reviewer_id' => $user->id,
            'reviewed_id' => $reviewedId,
            'property_id' => $propertyId,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'type' => $validated['type'],
            'is_approved' => false, // Nécessite modération
        ]);

        // Notifier l'utilisateur évalué (si applicable)
        if ($reviewedId) {
            $reviewedUser = $reviewedId === $booking->owner_id 
                ? $booking->owner 
                : $booking->tenant;

            Notification::send(
                $reviewedUser,
                'new_review',
                'Nouvel avis reçu',
                "{$user->name} vous a laissé un avis",
                route('dashboard')
            );
        }

        return redirect()->route('dashboard')
            ->with('success', 'Votre avis a été soumis et sera visible après modération.');
    }

    /**
     * Voir les avis d'une propriété
     */
    public function propertyReviews(int $propertyId)
    {
        $reviews = Review::with('reviewer')
            ->forProperty($propertyId)
            ->approved()
            ->latest()
            ->paginate(10);

        return view('reviews.property', compact('reviews', 'propertyId'));
    }
}


