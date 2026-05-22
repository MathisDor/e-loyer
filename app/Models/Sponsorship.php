<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sponsorship extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
        'type',
        'amount',
        'duration_days',
        'starts_at',
        'ends_at',
        'status',
        'views_count',
        'clicks_count',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->ends_at > now();
    }

    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'boost' => 'Boost',
            'featured' => 'À la une',
            'premium' => 'Premium',
            default => $this->type,
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 0, ',', ' ') . ' FCFA';
    }

    public static function getPricing(): array
    {
        return [
            'boost' => ['price' => 5000, 'duration' => 7, 'label' => 'Boost (7 jours)'],
            'featured' => ['price' => 15000, 'duration' => 14, 'label' => 'À la une (14 jours)'],
            'premium' => ['price' => 30000, 'duration' => 30, 'label' => 'Premium (30 jours)'],
        ];
    }
}


