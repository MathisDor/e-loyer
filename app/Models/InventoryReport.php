<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contract_id',
        'property_id',
        'type',
        'report_date',
        'observations',
        'items',
        'photos',
        'tenant_signed',
        'owner_signed',
        'tenant_signed_at',
        'owner_signed_at',
        'tenant_notes',
        'owner_notes',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'items' => 'array',
            'photos' => 'array',
            'tenant_signed' => 'boolean',
            'owner_signed' => 'boolean',
            'tenant_signed_at' => 'datetime',
            'owner_signed_at' => 'datetime',
        ];
    }

    // ============ CONSTANTES ============

    public const TYPES = [
        'entree' => 'État des lieux d\'entrée',
        'sortie' => 'État des lieux de sortie',
    ];

    // ============ RELATIONS ============

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    // ============ ACCESSEURS ============

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function isSigned(): bool
    {
        return $this->tenant_signed && $this->owner_signed;
    }

    public function canBeSignedBy(int $userId): bool
    {
        if ($this->isSigned()) {
            return false;
        }

        // Vérifier si l'utilisateur est le locataire ou le propriétaire
        return $this->contract->tenant_id === $userId || $this->contract->owner_id === $userId;
    }

    // ============ MÉTHODES ============

    public function signByTenant(?string $notes = null): void
    {
        $this->update([
            'tenant_signed' => true,
            'tenant_signed_at' => now(),
            'tenant_notes' => $notes,
        ]);
    }

    public function signByOwner(?string $notes = null): void
    {
        $this->update([
            'owner_signed' => true,
            'owner_signed_at' => now(),
            'owner_notes' => $notes,
        ]);
    }
}

