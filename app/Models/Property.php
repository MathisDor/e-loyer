<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'prospector_id',
        'title',
        'description',
        'type',
        'bedrooms',
        'bathrooms',
        'beds',
        'surface',
        'monthly_price',
        'deposit',
        'visit_price',
        'requires_deposit',
        'visit_assigned_to',
        'visit_assigned_user_id',
        'address',
        'city',
        'neighborhood',
        'latitude',
        'longitude',
        'amenities',
        'images',
        'status',
        'is_available',
        'prospector_validated',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'amenities' => 'array',
            'images' => 'array',
            'is_available' => 'boolean',
            'prospector_validated' => 'boolean',
            'requires_deposit' => 'boolean',
            'monthly_price' => 'decimal:2',
            'deposit' => 'decimal:2',
            'visit_price' => 'decimal:2',
            'surface' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    // ============ CONSTANTES ============

    public const TYPES = [
        'appartement' => 'Appartement',
        'maison' => 'Maison',
        'studio' => 'Studio',
        'villa' => 'Villa',
        'chambre' => 'Chambre',
    ];

    public const STATUSES = [
        'en_attente' => 'En attente',
        'approuve' => 'Approuvé',
        'rejete' => 'Rejeté',
        'loue' => 'Loué',
    ];

    public const CITIES = [
        'Libreville',
        'Port-Gentil',
        'Franceville',
        'Oyem',
        'Moanda',
        'Mouila',
        'Lambaréné',
        'Tchibanga',
        'Koulamoutou',
        'Makokou',
    ];

    public const AMENITIES = [
        // Eau & Énergie
        'groupe_electrogene' => 'Groupe électrogène',
        'citerne_eau'        => "Citerne d'eau",
        'forage'             => 'Forage / Puits',
        'eau_chaude'         => 'Eau chaude',
        'fibre_optique'      => 'Fibre optique',
        'wifi'               => 'WiFi',
        // Confort
        'climatisation'      => 'Climatisation',
        'meuble'             => 'Meublé',
        'cuisine_equipee'    => 'Cuisine équipée',
        'cuisine_exterieure' => 'Cuisine extérieure',
        'buanderie'          => 'Buanderie',
        // Sécurité
        'gardien'            => 'Gardien',
        'securite_24h'       => 'Sécurité 24h/24',
        'cloture'            => 'Enceinte clôturée',
        'porte_blindee'      => 'Porte blindée',
        // Extérieur & Parking
        'parking'            => 'Parking',
        'jardin'             => 'Jardin',
        'balcon'             => 'Balcon',
        'terrasse'           => 'Terrasse',
        // Divers
        'piscine'            => 'Piscine',
        'douche_externe'     => 'Douche externe',
    ];

    // ============ RELATIONS ============

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function prospector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prospector_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('type', 'property');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function visitAssignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'visit_assigned_user_id');
    }

    // ============ ACCESSEURS ============

    public function getMainImageAttribute(): ?string
    {
        $images = $this->images;
        if (empty($images) || !is_array($images)) return null;
        $path = is_array($images[0]) ? ($images[0]['path'] ?? null) : $images[0];
        if (!$path) return null;
        return asset('storage/' . $path);
    }

    public function getImagesUrlsAttribute(): array
    {
        $images = $this->images ?? [];
        return array_map(function ($image) {
            return asset('storage/' . $image);
        }, $images);
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->monthly_price, 0, ',', ' ') . ' FCFA';
    }

    public function getPriceWithFeesAttribute(): float
    {
        $price = (float) $this->monthly_price;
        return $price + round($price * 0.08) + 400;
    }

    public function getFormattedPriceWithFeesAttribute(): string
    {
        return number_format($this->price_with_fees, 0, ',', ' ') . ' FCFA';
    }

    public static function generateTitle(string $type, string $city, ?string $neighborhood = null): string
    {
        $typeName = self::TYPES[$type] ?? ucfirst($type);
        return $neighborhood
            ? "{$typeName} à {$neighborhood}, {$city}"
            : "{$typeName} à {$city}";
    }

    public function getFormattedDepositAttribute(): string
    {
        return number_format($this->deposit ?? 0, 0, ',', ' ') . ' FCFA';
    }

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getAmenitiesListAttribute(): array
    {
        $amenities = $this->amenities ?? [];
        return array_map(function ($key) {
            return self::AMENITIES[$key] ?? $key;
        }, $amenities);
    }

    public function getAverageRatingAttribute(): float
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->count();
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([$this->address, $this->neighborhood, $this->city]);
        return implode(', ', $parts);
    }

    // ============ SCOPES ============

    public function scopeApproved($query)
    {
        return $query->where('status', 'approuve');
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)->approved();
    }

    public function scopeInCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopePriceBetween($query, $min, $max)
    {
        if ($min) {
            $query->where('monthly_price', '>=', $min);
        }
        if ($max) {
            $query->where('monthly_price', '<=', $max);
        }
        return $query;
    }

    public function scopeWithBedrooms($query, int $bedrooms)
    {
        return $query->where('bedrooms', '>=', $bedrooms);
    }

    public function scopeWithAmenities($query, array $amenities)
    {
        foreach ($amenities as $amenity) {
            $query->whereJsonContains('amenities', $amenity);
        }
        return $query;
    }

    public function scopePending($query)
    {
        return $query->where('status', 'en_attente');
    }

    public function scopeNeedsProspectorValidation($query)
    {
        return $query->whereNotNull('prospector_id')
            ->where('prospector_validated', false);
    }

    // ============ HELPERS ============

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function isProspectedBy(User $user): bool
    {
        return $this->prospector_id === $user->id;
    }

    public function hasProspector(): bool
    {
        return $this->prospector_id !== null;
    }

    public function isFavoritedBy(User $user): bool
    {
        return $this->favorites()->where('user_id', $user->id)->exists();
    }

    public function needsValidation(): bool
    {
        return $this->status === 'en_attente';
    }

    public function needsProspectorValidation(): bool
    {
        return $this->hasProspector() && !$this->prospector_validated;
    }

    public function getFormattedVisitPriceAttribute(): string
    {
        if (!$this->visit_price) {
            return 'Non défini';
        }
        return number_format($this->visit_price, 0, ',', ' ') . ' FCFA';
    }

    public function getVisitTotalAmountAttribute(): float
    {
        if (!$this->visit_price) {
            return 0;
        }
        $amounts = Visit::calculateTotalAmount($this->visit_price);
        return $amounts['total_amount'];
    }

    public function getFormattedVisitTotalAmountAttribute(): string
    {
        if (!$this->visit_price) {
            return 'Non défini';
        }
        return number_format($this->visit_total_amount, 0, ',', ' ') . ' FCFA';
    }
}

