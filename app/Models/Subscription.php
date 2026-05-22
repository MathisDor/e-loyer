<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'starts_at',
        'ends_at',
        'cancelled_at',
        'auto_renew',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->ends_at > now();
    }

    public function daysRemaining(): int
    {
        if (!$this->ends_at) return 0;
        return max(0, now()->diffInDays($this->ends_at, false));
    }
}


