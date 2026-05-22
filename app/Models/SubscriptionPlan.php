<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'max_properties',
        'max_images_per_property',
        'can_sponsor',
        'sponsor_discount',
        'priority_support',
        'analytics_advanced',
        'features',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'can_sponsor' => 'boolean',
        'priority_support' => 'boolean',
        'analytics_advanced' => 'boolean',
        'is_active' => 'boolean',
        'features' => 'array',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 0, ',', ' ') . ' FCFA';
    }
}


