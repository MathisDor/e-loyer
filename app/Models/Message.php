<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message',
        'is_read',
        'attachment',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    // ============ RELATIONS ============

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // ============ ACCESSEURS ============

    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment ? asset('storage/' . $this->attachment) : null;
    }

    public function getFormattedTimeAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    // ============ SCOPES ============

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeFromUser($query, int $userId)
    {
        return $query->where('sender_id', $userId);
    }

    // ============ HELPERS ============

    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update(['is_read' => true]);
        }
    }

    public function isSentBy(User $user): bool
    {
        return $this->sender_id === $user->id;
    }
}


