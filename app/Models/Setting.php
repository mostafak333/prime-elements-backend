<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'delivery_fee',
        'vat_percentage',
        'vat_enabled',
        'terms_conditions',
        'privacy_policy',
        'return_exchange_policy',
        'updated_by',
    ];

    protected $casts = [
        'delivery_fee' => 'decimal:2',
        'vat_percentage' => 'decimal:2',
        'vat_enabled' => 'boolean',
    ];

    // Relationships

    public function updatedBy()
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    // Helper methods
    public static function getVatPercentage(): float
    {
        return static::first()->vat_percentage ?? 14.00;
    }

    public static function getDeliveryFee(): float
    {
        return static::first()->delivery_fee ?? 0.00;
    }

    public static function isVatEnabled(): bool
    {
        return static::first()->vat_enabled ?? true;
    }

    public static function getTermsConditions(): ?string
    {
        return static::first()->terms_conditions ?? null;
    }

    public static function getPrivacyPolicy(): ?string
    {
        return static::first()->privacy_policy ?? null;
    }

    public static function getReturnExchangePolicy(): ?string
    {
        return static::first()->return_exchange_policy ?? null;
    }
}
