<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EbookAccessCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'code',
        'used_at',
        'order_id',
        'order_item_id',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function orderEbookCode()
    {
        return $this->hasOne(OrderEbookCode::class);
    }
}
