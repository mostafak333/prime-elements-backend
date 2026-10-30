<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderEbookCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'order_item_id',
        'ebook_access_code_id',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function ebookAccessCode()
    {
        return $this->belongsTo(EbookAccessCode::class);
    }
}
