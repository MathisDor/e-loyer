<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'phone',
        'whatsapp',
        'address',
        'city',
        'neighborhood',
        'id_card',
        'is_verified',
        'commission_rate',
        'total_earnings',
        'avatar',
        'bio',
        // Documents de vérification
        'pay_slip',
        'employment_contract',
        'proof_of_address',
        'bank_statement',
        'property_title',
        'business_registration',
        'phone_verified_at',
        'is_suspended',
        'suspension_reason',
        'agency_name',
        'agency_description',
        'agency_logo',
        'balance',
        // Système démarcheur
        'ref_code',
        'badge_level',
        'clients_brought',
        'locations_concluded',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'commission_rate' => 'decimal:2',
            'total_earnings' => 'decimal:2',
        ];
    }

    // ============ CONSTANTES BADGES ============

    public const BADGE_THRESHOLDS = [
        'bronze'   => 0,
        'silver'   => 100_000,
        'gold'     => 500_000,
        'platinum' => 1_000_000,
    ];

    public const BADGE_LABELS = [
        'bronze'   => 'Bronze',
        'silver'   => 'Argent',
        'gold'     => 'Or',
        'platinum' => 'Platine VIP',
    ];

    public const BADGE_COLORS = [
        'bronze'   => '#CD7F32',
        'silver'   => '#A8A9AD',
        'gold'     => '#D4AF37',
        'platinum' => '#E5E4E2',
    ];

    // ============ HELPERS ============

    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    public function isProprietaire(): bool
    {
        return $this->user_type === 'proprietaire';
    }

    public function isLocataire(): bool
    {
        return $this->user_type === 'locataire';
    }

    public function isDemarcheur(): bool
    {
        return $this->user_type === 'demarcheur';
    }

    public function isAgence(): bool
    {
        return $this->user_type === 'agence';
    }

    /**
     * Générer un code de parrainage unique (8 caractères alphanumériques).
     */
    public static function generateRefCode(): string
    {
        do {
            $code = 'DM' . strtoupper(\Illuminate\Support\Str::random(6));
        } while (self::where('ref_code', $code)->exists());

        return $code;
    }

    /**
     * Obtenir ou créer le code de parrainage du démarcheur.
     */
    public function getRefCodeAttribute($value): ?string
    {
        if ($this->isDemarcheur() && empty($value)) {
            $code = self::generateRefCode();
            $this->update(['ref_code' => $code]);
            return $code;
        }
        return $value;
    }

    /**
     * Lien de parrainage pour un bien donné.
     */
    public function getReferralLink(int $propertyId = null): string
    {
        $base = $propertyId
            ? route('properties.show', $propertyId)
            : route('properties.index');

        return $base . '?ref=' . $this->ref_code;
    }

    /**
     * Recalculer et mettre à jour le badge selon les gains.
     */
    public function refreshBadge(): void
    {
        $earnings = (float) $this->total_earnings;
        $level = 'bronze';

        foreach (array_reverse(self::BADGE_THRESHOLDS, true) as $badge => $threshold) {
            if ($earnings >= $threshold) {
                $level = $badge;
                break;
            }
        }

        if ($this->badge_level !== $level) {
            $this->update(['badge_level' => $level]);
        }
    }

    public function getBadgeLabelAttribute(): string
    {
        return self::BADGE_LABELS[$this->badge_level ?? 'bronze'] ?? 'Bronze';
    }

    public function getBadgeColorAttribute(): string
    {
        return self::BADGE_COLORS[$this->badge_level ?? 'bronze'] ?? '#CD7F32';
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=009639&color=fff';
    }

    // ============ RELATIONS ============

    /**
     * Propriétés possédées (pour propriétaires)
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'owner_id');
    }

    /**
     * Propriétés prospectées (pour démarcheurs)
     */
    public function prospectedProperties(): HasMany
    {
        return $this->hasMany(Property::class, 'prospector_id');
    }

    /**
     * Réservations en tant que locataire
     */
    public function bookingsAsTenant(): HasMany
    {
        return $this->hasMany(Booking::class, 'tenant_id');
    }

    /**
     * Réservations en tant que propriétaire
     */
    public function bookingsAsOwner(): HasMany
    {
        return $this->hasMany(Booking::class, 'owner_id');
    }

    /**
     * Paiements effectués
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Avis donnés
     */
    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    /**
     * Avis reçus
     */
    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewed_id');
    }

    /**
     * Commissions gagnées (pour démarcheurs)
     */
    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'prospector_id');
    }

    /**
     * Favoris
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Propriétés favorites
     */
    public function favoriteProperties()
    {
        return $this->belongsToMany(Property::class, 'favorites')
            ->withTimestamps();
    }

    /**
     * Conversations
     */
    public function conversations()
    {
        return Conversation::where('user_one_id', $this->id)
            ->orWhere('user_two_id', $this->id);
    }

    /**
     * Messages envoyés
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Notifications
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Abonnement actif (pour agences)
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class)->where('status', 'active')->latest();
    }

    /**
     * Tous les abonnements
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Méthodes de paiement
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Sponsorisations
     */
    public function sponsorships(): HasMany
    {
        return $this->hasMany(Sponsorship::class);
    }

    /**
     * Retraits (agence - ancien système)
     */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    /**
     * Demandes de retrait (démarcheur)
     */
    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    // ============ STATISTIQUES ============

    /**
     * Note moyenne reçue
     */
    public function getAverageRatingAttribute(): float
    {
        return $this->reviewsReceived()->avg('rating') ?? 0;
    }

    /**
     * Nombre d'avis reçus
     */
    public function getReviewsCountAttribute(): int
    {
        return $this->reviewsReceived()->count();
    }

    /**
     * Commissions en attente (pour démarcheurs)
     */
    public function getPendingCommissionsAttribute()
    {
        return $this->commissions()
            ->where('status', 'en_attente')
            ->sum('amount');
    }

    /**
     * Messages non lus
     */
    public function getUnreadMessagesCountAttribute(): int
    {
        $conversationIds = $this->conversations()->pluck('id');
        return Message::whereIn('conversation_id', $conversationIds)
            ->where('sender_id', '!=', $this->id)
            ->where('is_read', false)
            ->count();
    }
}
