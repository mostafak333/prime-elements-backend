<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin;
use App\Models\User;
use App\Models\AddressDetail;
use App\Models\PaymentMethod;
use App\Models\DeliveryMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

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
        'estimated_delivery_date',
        'user_full_name',
        'email',
        'phone_to_number',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'terms_and_condition_agreed' => 'boolean',
        'estimated_delivery_date' => 'date',
    ];

    /**
     * Get the user who owns this order.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the address details for this order.
     */
    public function addressDetail()
    {
        return $this->belongsTo(AddressDetail::class, 'address_details_id');
    }

    /**
     * Get the payment method for this order.
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    /**
     * Get the delivery method for this order.
     */
    public function deliveryMethod()
    {
        return $this->belongsTo(DeliveryMethod::class, 'delivery_method_id');
    }

    /**
     * Get all order items for this order.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
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
        return $this->belongsTo(Admin::class, 'updated_by');
    }
}
