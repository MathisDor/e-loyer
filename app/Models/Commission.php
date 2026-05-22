<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'prospector_id',
        'booking_id',
        'property_id',
        'amount',
        'percentage',
        'status',
        'paid_at',
        'payment_method',
        'transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'percentage' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    // ============ CONSTANTES ============

    public const STATUSES = [
        'en_attente' => 'En attente',
        'validee'    => 'Validée',
        'payee'      => 'Payée',
        'annulee'    => 'Annulée',
    ];

    public const PAYMENT_METHODS = [
        'airtel_money' => 'Airtel Money',
        'moov_money' => 'Moov Money',
        'gabon_telecom_cash' => 'Gabon Telecom Cash',
        'virement' => 'Virement Bancaire',
    ];

    // ============ RELATIONS ============

    public function prospector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prospector_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    // ============ ACCESSEURS ============

    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 0, ',', ' ') . ' FCFA';
    }

    public function getPaymentMethodNameAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    // ============ SCOPES ============

    public function scopePending($query)
    {
        return $query->where('status', 'en_attente');
    }

    public function scopeValidated($query)
    {
        return $query->where('status', 'validee');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'payee');
    }

    public function scopeForProspector($query, int $prospectorId)
    {
        return $query->where('prospector_id', $prospectorId);
    }

    // ============ HELPERS ============

    public function validate(): void
    {
        $this->update(['status' => 'validee']);
    }

    public function markAsPaid(string $method, string $transactionId = null): void
    {
        $this->update([
            'status'         => 'payee',
            'paid_at'        => now(),
            'payment_method' => $method,
            'transaction_id' => $transactionId,
        ]);

        // Mettre à jour les gains totaux + solde + badge du démarcheur
        $this->prospector->increment('total_earnings', $this->amount);
        $this->prospector->increment('balance', $this->amount);
        $this->prospector->increment('locations_concluded');
        $this->prospector->refreshBadge();
    }

    public function cancel(): void
    {
        $this->update(['status' => 'annulee']);
    }

    public function isPending(): bool    { return $this->status === 'en_attente'; }
    public function isPaid(): bool       { return $this->status === 'payee'; }
    public function isCancelled(): bool  { return $this->status === 'annulee'; }
}


