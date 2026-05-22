<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'tenant_id',
        'owner_id',
        'start_date',
        'end_date',
        'duration_months',
        'monthly_amount',
        'total_amount',
        'deposit_amount',
        'platform_commission',
        'prospector_commission',
        'status',
        'tenant_message',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'monthly_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'platform_commission' => 'decimal:2',
            'prospector_commission' => 'decimal:2',
        ];
    }

    // ============ CONSTANTES ============

    public const STATUSES = [
        'en_attente' => 'En attente',
        'acceptee' => 'Acceptée',
        'refusee' => 'Refusée',
        'payee' => 'Payée',
        'active' => 'Active',
        'terminee' => 'Terminée',
        'annulee' => 'Annulée',
    ];

    public const PLATFORM_COMMISSION_RATE = 0.12; // 12%
    public const PROSPECTOR_COMMISSION_RATE = 0.05; // 5%

    // ============ RELATIONS ============

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function commission(): HasOne
    {
        return $this->hasOne(Commission::class);
    }

    // ============ ACCESSEURS ============

    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getFormattedMonthlyAmountAttribute(): string
    {
        return number_format($this->monthly_amount, 0, ',', ' ') . ' FCFA';
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return number_format($this->total_amount, 0, ',', ' ') . ' FCFA';
    }

    public function getFormattedDepositAttribute(): string
    {
        return number_format($this->deposit_amount, 0, ',', ' ') . ' FCFA';
    }

    public function getInitialPaymentAttribute(): float
    {
        return $this->monthly_amount + $this->deposit_amount;
    }

    public function getFormattedInitialPaymentAttribute(): string
    {
        return number_format($this->initial_payment, 0, ',', ' ') . ' FCFA';
    }

    public function getOwnerAmountAttribute(): float
    {
        return $this->monthly_amount - $this->platform_commission - $this->prospector_commission;
    }

    public function getDaysUntilStartAttribute(): int
    {
        return now()->diffInDays($this->start_date, false);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' && 
               $this->start_date <= now() && 
               $this->end_date >= now();
    }

    public function getNextPaymentDateAttribute(): ?Carbon
    {
        if (!in_array($this->status, ['active', 'payee'])) {
            return null;
        }

        $lastPayment = $this->payments()
            ->where('payment_type', 'mensuel')
            ->where('status', 'confirme')
            ->latest('paid_at')
            ->first();

        if ($lastPayment) {
            return Carbon::parse($lastPayment->paid_at)->addMonth();
        }

        // Si pas de paiement mensuel, calculer depuis le début
        $initialPayment = $this->payments()
            ->where('payment_type', 'initial')
            ->where('status', 'confirme')
            ->first();

        if ($initialPayment) {
            return Carbon::parse($initialPayment->paid_at)->addMonth();
        }

        return $this->start_date;
    }

    // ============ SCOPES ============

    public function scopePending($query)
    {
        return $query->where('status', 'en_attente');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'acceptee');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForTenant($query, int $userId)
    {
        return $query->where('tenant_id', $userId);
    }

    public function scopeForOwner($query, int $userId)
    {
        return $query->where('owner_id', $userId);
    }

    // ============ MÉTHODES STATIQUES ============

    public static function calculateCommissions(float $monthlyAmount, bool $hasProspector = false): array
    {
        $platformCommission = $monthlyAmount * self::PLATFORM_COMMISSION_RATE;
        $prospectorCommission = $hasProspector ? $monthlyAmount * self::PROSPECTOR_COMMISSION_RATE : 0;

        return [
            'platform' => round($platformCommission, 2),
            'prospector' => round($prospectorCommission, 2),
            'owner' => round($monthlyAmount - $platformCommission - $prospectorCommission, 2),
        ];
    }

    // ============ HELPERS ============

    public function isPending(): bool
    {
        return $this->status === 'en_attente';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'acceptee';
    }

    public function isPaid(): bool
    {
        return in_array($this->status, ['payee', 'active', 'terminee']);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['en_attente', 'acceptee']);
    }

    public function canBeReviewed(User $user): bool
    {
        if ($this->status !== 'terminee') {
            return false;
        }

        // Vérifier si l'utilisateur n'a pas déjà laissé d'avis
        $existingReview = $this->reviews()
            ->where('reviewer_id', $user->id)
            ->exists();

        return !$existingReview;
    }

    public function belongsToUser(User $user): bool
    {
        return $this->tenant_id === $user->id || $this->owner_id === $user->id;
    }
}


