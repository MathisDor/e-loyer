<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'name',
        'phone',
        'account_number',
        'bank_name',
        'is_default',
        'is_verified',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_verified' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'airtel_money' => 'Airtel Money',
            'moov_money' => 'Moov Money',
            'bank_transfer' => 'Virement Bancaire',
            'card' => 'Carte Bancaire',
            default => $this->type,
        };
    }

    public function getMaskedNumberAttribute(): string
    {
        if ($this->phone) {
            return substr($this->phone, 0, 3) . '****' . substr($this->phone, -3);
        }
        if ($this->account_number) {
            return '****' . substr($this->account_number, -4);
        }
        return '****';
    }
}


