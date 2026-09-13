<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name_en',
        'name_ar',
        'short_description_en',
        'short_description_ar',
        'price',
        'discount_percentage',
        'discount_start_at',
        'discount_end_at',
        'stock',
        'status',
        'is_new_arrival',
        'is_best_seller',
        'is_e_copy',
        'publisher',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_start_at' => 'datetime',
        'discount_end_at' => 'datetime',
        'status' => 'boolean',
        'is_new_arrival' => 'boolean',
        'is_best_seller' => 'boolean',
        'is_e_copy' => 'boolean',
    ];

    /**
     * Effective per-unit discount amount, derived from the stored percentage.
     * Returns 0 once the discount window is not active, even though the stored
     * percentage is preserved.
     */
    public function getDiscountAttribute(mixed $value): string
    {
        if (! $this->isDiscountActive()) {
            return number_format(0, 2, '.', '');
        }

        $price = (float) ($this->attributes['price'] ?? 0);
        $percentage = (float) ($this->attributes['discount_percentage'] ?? 0);
        $percentageDiscount = round($price * ($percentage / 100), 2);

        return number_format($percentageDiscount, 2, '.', '');
    }

    /**
     * True when the product currently has an active offer.
     */
    public function getHasOfferAttribute(): bool
    {
        return $this->isDiscountActive();
    }

    public function isDiscountActive(): bool
    {
        $percentage = (float) ($this->attributes['discount_percentage'] ?? 0);

        if ($percentage <= 0) {
            return false;
        }

        if ($this->discount_end_at !== null && now()->greaterThanOrEqualTo($this->discount_end_at)) {
            return false;
        }

        if ($this->discount_start_at !== null && now()->lessThan($this->discount_start_at)) {
            return false;
        }

        return true;
    }

    /**
     * Zero out discount percentages whose time window has ended.
     */
    public static function expireExpiredDiscounts(): int
    {
        return static::query()
            ->where('discount_percentage', '>', 0)
            ->whereNotNull('discount_end_at')
            ->where('discount_end_at', '<=', now())
            ->update(['discount_percentage' => 0]);
    }

    /**
     * Get the category that owns this product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get all cart items for this product.
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function detail()
    {
        return $this->hasOne(ProductDetail::class);
    }

    /**
     * Get all order items for this product.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope to filter active products only.
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope to filter new arrivals only.
     */
    public function scopeNewArrivals(Builder $query)
    {
        return $query->where('is_new_arrival', true);
    }

    /**
     * Scope to filter best sellers only.
     */
    public function scopeBestSellers(Builder $query)
    {
        return $query->where('is_best_seller', true);
    }

    /**
     * Scope to filter only products with an active offer (discount within window).
     */
    public function scopeHasOffer(Builder $query)
    {
        return $query
            ->where('discount_percentage', '>', 0)
            ->where(function ($q) {
                $q->whereNull('discount_end_at')
                    ->orWhere('discount_end_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('discount_start_at')
                    ->orWhere('discount_start_at', '<=', now());
            });
    }

    /**
     * Scope to filter e-copy products only.
     */
    public function scopeECopy(Builder $query)
    {
        return $query->where('is_e_copy', true);
    }

    /**
     * Administrator who created the record.
     */
    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Administrator who last updated the record.
     */
    public function updatedBy()
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }
}
