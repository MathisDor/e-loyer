<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'payment_method', 'phone_number', 'account_name',
        'status', 'transaction_id', 'rejection_reason', 'processed_at', 'processed_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public const STATUSES = [
        'en_attente' => 'En attente',
        'approuve'   => 'Approuvé',
        'paye'       => 'Payé',
        'rejete'     => 'Rejeté',
    ];

    public const METHODS = [
        'airtel_money'        => 'Airtel Money',
        'moov_money'          => 'Moov Money',
        'gabon_telecom_cash'  => 'Gabon Telecom Cash',
        'virement'            => 'Virement bancaire',
    ];

    // ── Relations ──────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // ── Accesseurs ─────────────────────────────────────────────

    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getMethodNameAttribute(): string
    {
        return self::METHODS[$this->payment_method] ?? $this->payment_method;
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 0, ',', ' ') . ' FCFA';
    }

    // ── Scopes ─────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'en_attente');
    }

    // ── Helpers ────────────────────────────────────────────────

    public function isPending(): bool  { return $this->status === 'en_attente'; }
    public function isPaid(): bool     { return $this->status === 'paye'; }
    public function isRejected(): bool { return $this->status === 'rejete'; }

    public function approve(User $admin, string $transactionId): void
    {
        $this->update([
            'status'         => 'paye',
            'transaction_id' => $transactionId,
            'processed_at'   => now(),
            'processed_by'   => $admin->id,
        ]);

        // Déduire du solde du démarcheur
        $this->user->decrement('balance', $this->amount);
    }

    public function reject(User $admin, string $reason): void
    {
        $this->update([
            'status'           => 'rejete',
            'rejection_reason' => $reason,
            'processed_at'     => now(),
            'processed_by'     => $admin->id,
        ]);
    }
}
