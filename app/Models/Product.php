<?php

namespace App\Models;

use App\Models\Admin;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\OrderItem;
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
        'short_description',
        'price',
        'discount',
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
        'discount' => 'decimal:2',
        'status' => 'boolean',
        'is_new_arrival' => 'boolean',
        'is_best_seller' => 'boolean',
        'is_e_copy' => 'boolean',
    ];

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
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope to filter new arrivals only.
     */
    public function scopeNewArrivals($query)
    {
        return $query->where('is_new_arrival', true);
    }

    /**
     * Scope to filter best sellers only.
     */
    public function scopeBestSellers($query)
    {
        return $query->where('is_best_seller', true);
    }

    /**
     * Scope to filter e-copy products only.
     */
    public function scopeECopy($query)
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
