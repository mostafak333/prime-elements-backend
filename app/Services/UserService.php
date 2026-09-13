<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = User::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }

        if (! empty($filters['email'])) {
            $query->where('email', 'like', "%{$filters['email']}%");
        }

        if (! empty($filters['phone'])) {
            $query->where('phone', 'like', "%{$filters['phone']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function find(int $id): User
    {
        return User::findOrFail($id);
    }

    public function updateStatus(User $user, string $status): User
    {
        $user->update(['status' => $status]);

        // Invalidate every token already issued to this user so the change
        // (e.g. blocking) takes effect immediately on the client side.
        $user->increment('token_version');

        return $user->fresh();
    }
}
