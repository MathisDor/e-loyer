<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'status',
        'payment_method',
        'reference',
        'description',
        'metadata',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        $sign = in_array($this->type, ['withdrawal', 'refund']) ? '-' : '+';
        return $sign . ' ' . number_format($this->amount, 0, ',', ' ') . ' FCFA';
    }

    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'subscription' => 'Abonnement',
            'sponsorship' => 'Sponsorisation',
            'deposit' => 'Dépôt',
            'withdrawal' => 'Retrait',
            'commission' => 'Commission',
            'refund' => 'Remboursement',
            default => $this->type,
        };
    }

    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'completed' => 'Terminé',
            'failed' => 'Échoué',
            'cancelled' => 'Annulé',
            default => $this->status,
        };
    }

    public static function generateReference(): string
    {
        return 'TXN-' . strtoupper(uniqid()) . '-' . now()->format('Ymd');
    }
}


