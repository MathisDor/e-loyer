<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_one_id',
        'user_two_id',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    // ============ RELATIONS ============

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    // ============ ACCESSEURS ============

    public function getOtherUser(User $currentUser): User
    {
        return $this->user_one_id === $currentUser->id 
            ? $this->userTwo 
            : $this->userOne;
    }

    public function getLastMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }

    public function getUnreadCountFor(User $user): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->count();
    }

    // ============ SCOPES ============

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId);
    }

    public function scopeWithProperty($query, int $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    // ============ MÉTHODES STATIQUES ============

    public static function findOrCreateBetween(User $userOne, User $userTwo, ?Property $property = null): self
    {
        $conversation = self::where(function ($query) use ($userOne, $userTwo) {
            $query->where('user_one_id', $userOne->id)
                ->where('user_two_id', $userTwo->id);
        })->orWhere(function ($query) use ($userOne, $userTwo) {
            $query->where('user_one_id', $userTwo->id)
                ->where('user_two_id', $userOne->id);
        });

        if ($property) {
            $conversation->where('property_id', $property->id);
        }

        $conversation = $conversation->first();

        if (!$conversation) {
            $conversation = self::create([
                'user_one_id' => $userOne->id,
                'user_two_id' => $userTwo->id,
                'property_id' => $property?->id,
            ]);
        }

        return $conversation;
    }

    // ============ HELPERS ============

    public function hasUser(User $user): bool
    {
        return $this->user_one_id === $user->id || $this->user_two_id === $user->id;
    }

    public function updateLastMessageTime(): void
    {
        $this->update(['last_message_at' => now()]);
    }
}


