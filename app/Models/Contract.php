<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'visit_id',
        'property_id',
        'tenant_id',
        'owner_id',
        'start_date',
        'end_date',
        'duration_months',
        'monthly_amount',
        'total_amount',
        'deposit_amount',
        'status',
        'tenant_signed_at',
        'owner_signed_at',
        'months_paid',
        'next_payment_date',
        'renewed_from_contract_id',
        'can_renew',
        'notes',
        'termination_requested_by',
        'termination_reason',
        'termination_details',
        'termination_requested_at',
        'termination_effective_date',
        'termination_status',
    ];

    protected function casts(): array
    {
        return [
            'start_date'                 => 'date',
            'end_date'                   => 'date',
            'next_payment_date'          => 'date',
            'tenant_signed_at'           => 'datetime',
            'owner_signed_at'            => 'datetime',
            'termination_requested_at'   => 'datetime',
            'termination_effective_date' => 'date',
            'monthly_amount'             => 'decimal:2',
            'total_amount'               => 'decimal:2',
            'deposit_amount'             => 'decimal:2',
            'months_paid'                => 'integer',
            'can_renew'                  => 'boolean',
        ];
    }

    // Préavis légaux (en mois) adaptés au contexte gabonais
    public const NOTICE_TENANT_MONTHS  = 1; // locataire : 1 mois
    public const NOTICE_OWNER_MONTHS   = 3; // propriétaire : 3 mois

    public const TERMINATION_REASONS = [
        'locataire' => [
            'depart_volontaire'       => 'Départ volontaire',
            'mutation_professionnelle' => 'Mutation / Raison professionnelle',
            'achat_logement'          => "Achat d'un logement",
            'autre'                   => 'Autre raison',
        ],
        'proprietaire' => [
            'fin_bail'      => 'Fin de bail non renouvelé',
            'non_paiement'  => 'Non-paiement des loyers',
            'nuisances'     => 'Nuisances / Troubles de voisinage',
            'autre'         => 'Autre raison',
        ],
    ];

    // ============ CONSTANTES ============

    public const STATUSES = [
        'en_attente' => 'En attente',
        'signe' => 'Signé',
        'actif' => 'Actif',
        'termine' => 'Terminé',
        'renouvele' => 'Renouvelé',
        'resilie' => 'Résilié',
    ];

    public const DURATION_MONTHS = 6;

    // ============ RELATIONS ============

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

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

    public function renewedFrom(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'renewed_from_contract_id');
    }

    public function monthlyPayments(): HasMany
    {
        return $this->payments()->where('payment_type', 'mensuel');
    }

    public function inventoryReports(): HasMany
    {
        return $this->hasMany(InventoryReport::class);
    }

    public function entryInventoryReport()
    {
        return $this->hasOne(InventoryReport::class)->where('type', 'entree');
    }

    public function exitInventoryReport()
    {
        return $this->hasOne(InventoryReport::class)->where('type', 'sortie');
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

    public function getRemainingMonthsAttribute(): int
    {
        return max(0, $this->duration_months - $this->months_paid);
    }

    public function isActive(): bool
    {
        return $this->status === 'actif' && 
               $this->start_date <= now() && 
               $this->end_date >= now();
    }

    public function isSigned(): bool
    {
        return $this->tenant_signed_at !== null && $this->owner_signed_at !== null;
    }

    public function needsPayment(): bool
    {
        return $this->isActive() && 
               $this->next_payment_date && 
               $this->next_payment_date <= now() &&
               $this->months_paid < $this->duration_months;
    }

    public function canRenew(): bool
    {
        return $this->can_renew &&
               $this->status === 'termine' &&
               $this->end_date <= now();
    }

    public function hasTerminationRequest(): bool
    {
        return !is_null($this->termination_requested_by) &&
               in_array($this->termination_status, ['en_attente']);
    }

    public function terminationPendingFor(string $role): bool
    {
        return $this->hasTerminationRequest() &&
               $this->termination_requested_by === $role;
    }

    public function canRequestTermination(string $role): bool
    {
        return $this->status === 'actif' && !$this->hasTerminationRequest();
    }

    public function getNoticePeriodMonths(): int
    {
        return $this->termination_requested_by === 'locataire'
            ? self::NOTICE_TENANT_MONTHS
            : self::NOTICE_OWNER_MONTHS;
    }

    public function getTerminationReasonLabelAttribute(): string
    {
        $all = array_merge(
            self::TERMINATION_REASONS['locataire'],
            self::TERMINATION_REASONS['proprietaire']
        );
        return $all[$this->termination_reason] ?? ($this->termination_reason ?? '—');
    }

    public function getDepositRefundableAttribute(): bool
    {
        // Caution restituable si le locataire a respecté le préavis
        if (!$this->termination_effective_date || !$this->termination_requested_at) return false;
        $noticeDays = $this->termination_requested_at->diffInDays($this->termination_effective_date);
        $requiredDays = $this->getNoticePeriodMonths() * 30;
        return $noticeDays >= $requiredDays;
    }
}

