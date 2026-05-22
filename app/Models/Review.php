<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'reviewer_id',
        'reviewed_id',
        'property_id',
        'rating',
        'comment',
        'type',
        'is_approved',
        'moderation_note',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'rating' => 'integer',
        ];
    }

    // ============ CONSTANTES ============

    public const TYPES = [
        'property' => 'Propriété',
        'owner' => 'Propriétaire',
        'tenant' => 'Locataire',
    ];

    // ============ RELATIONS ============

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewed(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    // ============ ACCESSEURS ============

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getStarsHtmlAttribute(): string
    {
        $filled = str_repeat('<i class="fas fa-star text-yellow-400"></i>', $this->rating);
        $empty = str_repeat('<i class="far fa-star text-gray-300"></i>', 5 - $this->rating);
        return $filled . $empty;
    }

    // ============ SCOPES ============

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForProperty($query, int $propertyId)
    {
        return $query->where('property_id', $propertyId);
    }

    // ============ HELPERS ============

    public function approve(): void
    {
        $this->update(['is_approved' => true]);
    }

    public function reject(string $reason = null): void
    {
        $this->update([
            'is_approved' => false,
            'moderation_note' => $reason,
        ]);
    }
}


