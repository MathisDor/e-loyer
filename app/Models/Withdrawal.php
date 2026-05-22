<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_method_id',
        'amount',
        'fee',
        'net_amount',
        'status',
        'reference',
        'reject_reason',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 0, ',', ' ') . ' FCFA';
    }

    public function getFormattedNetAmountAttribute(): string
    {
        return number_format($this->net_amount, 0, ',', ' ') . ' FCFA';
    }

    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'processing' => 'En cours',
            'completed' => 'Terminé',
            'rejected' => 'Rejeté',
            default => $this->status,
        };
    }

    public static function generateReference(): string
    {
        return 'WD-' . strtoupper(uniqid()) . '-' . now()->format('Ymd');
    }

    public static function calculateFee(float $amount): float
    {
        // Frais de 2% minimum 500 FCFA
        $fee = $amount * 0.02;
        return max(500, $fee);
    }
}


