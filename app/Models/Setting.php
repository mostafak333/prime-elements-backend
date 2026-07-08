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
}