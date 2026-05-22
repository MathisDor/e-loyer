<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'visit_id',
        'contract_id',
        'user_id',
        'amount',
        'payment_method',
        'transaction_id',
        'phone_number',
        'payment_type',
        'status',
        'description',
        'paid_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    // ============ CONSTANTES ============

    public const METHODS = [
        'airtel_money' => 'Airtel Money',
        'moov_money' => 'Moov Money',
        'gabon_telecom_cash' => 'Gabon Telecom Cash',
        'carte_bancaire' => 'Carte Bancaire',
    ];

    public const TYPES = [
        'initial' => 'Paiement Initial',
        'mensuel' => 'Loyer Mensuel',
        'caution' => 'Caution',
        'remboursement' => 'Remboursement',
        'visite' => 'Paiement de Visite',
        'premier_versement' => 'Premier Versement (1/6)',
    ];

    public const STATUSES = [
        'en_attente' => 'En attente',
        'traitement' => 'En traitement',
        'confirme' => 'Confirmé',
        'echoue' => 'Échoué',
        'rembourse' => 'Remboursé',
    ];

    // ============ RELATIONS ============

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    // ============ ACCESSEURS ============

    public function getMethodNameAttribute(): string
    {
        return self::METHODS[$this->payment_method] ?? $this->payment_method;
    }

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->payment_type] ?? $this->payment_type;
    }

    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 0, ',', ' ') . ' FCFA';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'confirme' => 'green',
            'en_attente', 'traitement' => 'yellow',
            'echoue' => 'red',
            'rembourse' => 'blue',
            default => 'gray',
        };
    }

    // ============ SCOPES ============

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirme');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'en_attente');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('payment_type', $type);
    }

    // ============ HELPERS ============

    public function isConfirmed(): bool
    {
        return $this->status === 'confirme';
    }

    public function isPending(): bool
    {
        return $this->status === 'en_attente';
    }

    public function isFailed(): bool
    {
        return $this->status === 'echoue';
    }

    public function markAsConfirmed(string $transactionId = null): void
    {
        $this->update([
            'status' => 'confirme',
            'paid_at' => now(),
            'transaction_id' => $transactionId ?? $this->transaction_id,
        ]);
    }

    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => 'echoue',
            'description' => $reason ?? $this->description,
        ]);
    }
}

