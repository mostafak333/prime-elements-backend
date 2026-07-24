<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Support\Facades\DB;

class UserProfileService
{
    public function getProfile(): User
    {
        return auth()->guard('api-user')->user();
    }

    public function updateProfile(array $data): User
    {
        $user = auth()->guard('api-user')->user();

        $updateData = [];

        foreach (['name', 'phone', 'avatar', 'country', 'city', 'street_address', 'apartment'] as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (!empty($updateData)) {
            DB::transaction(function () use ($user, $updateData) {
                $user->update($updateData);
            });
        }

        return $user->fresh();
    }

    public function getStatistics(User $user): array
    {
        $totalOrders = Order::where('user_id', $user->id)->count();

        $deliveredOrders = Order::where('user_id', $user->id)
            ->where('status', 'delivered')
            ->count();

        $wishlistItems = Wishlist::where('user_id', $user->id)->count();

        return [
            'total_orders'    => $totalOrders,
            'delivered_orders' => $deliveredOrders,
            'wishlist_items'  => $wishlistItems,
        ];
    }

    public function getProfileWithStats(): array
    {
        $user = $this->getProfile();
        $statistics = $this->getStatistics($user);

        return [
            'user'       => $user,
            'statistics' => $statistics,
        ];
    }
}
