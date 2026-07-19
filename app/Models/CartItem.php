<?php

namespace App\Models;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CartItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the user who owns this cart item.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the product associated with this cart item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Administrator who created the record.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Administrator who last updated the record.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
