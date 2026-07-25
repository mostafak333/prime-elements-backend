<?php

namespace App\Http\Resources\User;

use App\Models\Order;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'email'          => $this->email,
            'phone'          => $this->phone,
            'avatar'         => $this->avatar,
            'country'        => $this->country,
            'city'           => $this->city,
            'street_address' => $this->street_address,
            'apartment'      => $this->apartment,
            'member_since'   => $this->created_at?->toDateString(),
            'statistics'     => [
                'total_orders'     => Order::where('user_id', $this->id)->count(),
                'delivered_orders' => Order::where('user_id', $this->id)->where('status', 'delivered')->count(),
                'wishlist_items'   => Wishlist::where('user_id', $this->id)->count(),
            ],
        ];
    }
}
