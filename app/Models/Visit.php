<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Visit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'tenant_id',
        'owner_id',
        'assigned_user_id',
        'scheduled_at',
        'base_price',
        'commission',
        'service_fee',
        'total_amount',
        'status',
        'visit_status',
        'visit_status_notes',
        'property_accepted',
        'refusal_reason',
        'payment_id',
        'is_paid',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'paid_at' => 'datetime',
            'base_price' => 'decimal:2',
            'commission' => 'decimal:2',
            'service_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'is_paid' => 'boolean',
            'property_accepted' => 'boolean',
        ];
    }

    // ============ CONSTANTES ============

    public const STATUSES = [
        'reservee' => 'Réservée',
        'en_cours' => 'En cours',
        'terminee' => 'Terminée',
        'acceptee' => 'Acceptée',
        'refusee' => 'Refusée',
        'annulee' => 'Annulée',
        'non_effectuee' => 'Non effectuée',
    ];

    public const VISIT_STATUSES = [
        'en_attente' => 'En attente',
        'reussie' => 'Réussie',
        'non_effectuee' => 'Non effectuée',
    ];

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

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    // ============ ACCESSEURS ============

    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return number_format($this->total_amount, 0, ',', ' ') . ' FCFA';
    }

    // ============ MÉTHODES ============

    /**
     * Calculer le montant total de la visite
     */
    public static function calculateTotalAmount(float $basePrice): array
    {
        $commission = round($basePrice * 0.08); // 8%
        $serviceFee = 400; // Frais fixes
        $total = $basePrice + $commission + $serviceFee;

        return [
            'base_price' => $basePrice,
            'commission' => $commission,
            'service_fee' => $serviceFee,
            'total_amount' => $total,
        ];
    }

    /**
     * Vérifier si la visite peut être mise à jour
     */
    public function canBeUpdated(): bool
    {
        return in_array($this->status, ['reservee', 'en_cours']);
    }

    /**
     * Vérifier si la visite est en cours
     */
    public function isInProgress(): bool
    {
        return $this->status === 'en_cours';
    }

    /**
     * Vérifier si la visite est terminée
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, ['terminee', 'acceptee', 'refusee']);
    }

    /**
     * Vérifier si la visite peut être démarrée
     */
    public function canStart(): bool
    {
        return $this->status === 'reservee' && 
               $this->is_paid && 
               Carbon::parse($this->scheduled_at)->isToday();
    }

    /**
     * Vérifier si la visite peut être terminée
     */
    public function canComplete(): bool
    {
        // Peut être complétée si en cours OU terminée avec succès mais pas encore acceptée/refusée
        return $this->status === 'en_cours' || 
               ($this->status === 'terminee' && $this->visit_status === 'reussie' && is_null($this->property_accepted));
    }
}

