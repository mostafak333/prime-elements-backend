<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\User\AddressResource;
use App\Http\Resources\User\DeliveryMethodResource;
use App\Http\Resources\User\PaymentMethodResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'subtotal' => $this->subtotal,
            'shipping' => $this->shipping,
            'discount' => $this->discount,
            'tax' => $this->tax,
            'total' => $this->total,
            'user_full_name' => $this->user_full_name,
            'email' => $this->email,
            'phone_to_number' => $this->phone_to_number,
            'notes' => $this->notes,
            'estimated_delivery_date' => $this->estimated_delivery_date?->format('Y-m-d'),
            'terms_and_condition_agreed' => $this->terms_and_condition_agreed,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'phone' => $this->user?->phone,
            ],
            'payment_method' => new PaymentMethodResource($this->whenLoaded('paymentMethod')),
            'delivery_method' => new DeliveryMethodResource($this->whenLoaded('deliveryMethod')),
            'address' => new AddressResource($this->whenLoaded('addressDetail')),
            'items' => AdminOrderItemResource::collection($this->whenLoaded('orderItems')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
