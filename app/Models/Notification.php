<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'action_url',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    // ============ CONSTANTES ============

    public const TYPES = [
        'booking_request' => 'Demande de réservation',
        'booking_accepted' => 'Réservation acceptée',
        'booking_rejected' => 'Réservation refusée',
        'payment_received' => 'Paiement reçu',
        'payment_due' => 'Paiement dû',
        'new_message' => 'Nouveau message',
        'property_approved' => 'Propriété approuvée',
        'property_rejected' => 'Propriété rejetée',
        'prospector_validation' => 'Validation démarcheur requise',
        'commission_earned' => 'Commission gagnée',
        'commission_paid' => 'Commission payée',
        'new_review' => 'Nouvel avis',
        'visit_payment_received' => 'Paiement de visite reçu',
        'visit_assigned' => 'Visite assignée',
        'visit_started' => 'Visite démarrée',
        'visit_accepted' => 'Propriété acceptée après visite',
        'visit_refused' => 'Propriété refusée après visite',
        'first_payment_received' => 'Premier versement reçu',
        'visit_completed' => 'Visite terminée',
        'visit_not_completed' => 'Visite non effectuée',
        'contract_completed' => 'Contrat terminé',
        'contract_renewed' => 'Contrat renouvelé',
    ];

    // ============ RELATIONS ============

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ============ ACCESSEURS ============

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    public function getIconAttribute(): string
    {
        return match($this->type) {
            'booking_request', 'booking_accepted', 'booking_rejected' => 'calendar',
            'payment_received', 'payment_due' => 'credit-card',
            'new_message' => 'envelope',
            'property_approved', 'property_rejected', 'prospector_validation' => 'home',
            'commission_earned', 'commission_paid' => 'money-bill',
            'new_review' => 'star',
            default => 'bell',
        };
    }

    public function getColorAttribute(): string
    {
        return match($this->type) {
            'booking_accepted', 'property_approved', 'payment_received', 'commission_paid' => 'green',
            'booking_rejected', 'property_rejected' => 'red',
            'payment_due' => 'yellow',
            default => 'blue',
        };
    }

    // ============ SCOPES ============

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // ============ HELPERS ============

    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public function markAsUnread(): void
    {
        $this->update(['read_at' => null]);
    }

    // ============ MÉTHODES STATIQUES ============

    public static function send(User $user, string $type, string $title, string $message, string $actionUrl = null, array $data = []): self
    {
        return self::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'data' => $data,
        ]);
    }

    public static function markAllAsRead(User $user): void
    {
        self::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}

