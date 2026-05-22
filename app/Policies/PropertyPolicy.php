<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    /**
     * Seuls les propriétaires, agences et démarcheurs peuvent créer des propriétés
     */
    public function create(User $user): bool
    {
        return in_array($user->user_type, ['proprietaire', 'demarcheur', 'agence']);
    }

    /**
     * Seul le propriétaire peut modifier
     */
    public function update(User $user, Property $property): bool
    {
        return $user->id === $property->owner_id || $user->isAdmin();
    }

    /**
     * Seul le propriétaire peut supprimer
     */
    public function delete(User $user, Property $property): bool
    {
        return $user->id === $property->owner_id || $user->isAdmin();
    }

    /**
     * Validation par le propriétaire (pour les biens ajoutés par démarcheurs)
     */
    public function validate(User $user, Property $property): bool
    {
        return $user->id === $property->owner_id && 
               $property->prospector_id !== null && 
               !$property->prospector_validated;
    }
}

