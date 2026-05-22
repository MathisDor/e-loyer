<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
    ];

    // ============ RELATIONS ============

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    // ============ MÉTHODES STATIQUES ============

    public static function toggle(User $user, Property $property): bool
    {
        $favorite = self::where('user_id', $user->id)
            ->where('property_id', $property->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return false; // Retiré des favoris
        }

        self::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        return true; // Ajouté aux favoris
    }
}


