<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    /**
     * Voir une conversation
     */
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->hasUser($user);
    }
}


