<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;

class BookingPolicy
{
    /**
     * Seuls les locataires peuvent créer des réservations
     */
    public function create(User $user, Property $property): bool
    {
        return $user->isLocataire() && 
               $user->id !== $property->owner_id &&
               $property->is_available &&
               $property->status === 'approuve';
    }

    /**
     * Voir une réservation
     */
    public function view(User $user, Booking $booking): bool
    {
        return $user->id === $booking->tenant_id || 
               $user->id === $booking->owner_id ||
               $user->isAdmin();
    }

    /**
     * Répondre à une demande (accepter/refuser)
     */
    public function respond(User $user, Booking $booking): bool
    {
        return $user->id === $booking->owner_id;
    }

    /**
     * Payer une réservation
     */
    public function pay(User $user, Booking $booking): bool
    {
        return $user->id === $booking->tenant_id;
    }

    /**
     * Annuler une réservation
     */
    public function cancel(User $user, Booking $booking): bool
    {
        return ($user->id === $booking->tenant_id || $user->id === $booking->owner_id) &&
               $booking->canBeCancelled();
    }

    /**
     * Terminer une location
     */
    public function complete(User $user, Booking $booking): bool
    {
        return $user->id === $booking->owner_id && $booking->status === 'active';
    }

    /**
     * Laisser un avis
     */
    public function review(User $user, Booking $booking): bool
    {
        return ($user->id === $booking->tenant_id || $user->id === $booking->owner_id) &&
               $booking->status === 'terminee';
    }
}


