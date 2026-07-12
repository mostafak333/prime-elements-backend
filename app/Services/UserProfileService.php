<?php

namespace App\Services;

use App\Models\User;

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

        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        if (isset($data['phone'])) {
            $updateData['phone'] = $data['phone'];
        }

        if (isset($data['avatar'])) {
            $updateData['avatar'] = $data['avatar'];
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        return $user->fresh();
    }
}
