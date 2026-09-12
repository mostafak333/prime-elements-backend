<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_number',
        'address_details_id',
        'payment_method_id',
        'delivery_method_id',
        'subtotal',
        'shipping',
        'discount',
        'tax',
        'total',
        'status',
        'payment_status',
        'terms_and_condition_agreed',
        'privacy_policy_agreed',
        'estimated_delivery_date',
        'user_full_name',
        'email',
        'phone_to_number',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'terms_and_condition_agreed' => 'boolean',
        'privacy_policy_agreed' => 'boolean',
        'estimated_delivery_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function addressDetail()
    {
        return $this->belongsTo(AddressDetail::class, 'address_details_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function deliveryMethod()
    {
        return $this->belongsTo(DeliveryMethod::class, 'delivery_method_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
