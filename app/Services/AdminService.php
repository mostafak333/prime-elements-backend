<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Mail\AdminInvitationMail;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class AdminService
{
    public function createAdminInvitation(array $data): Admin
    {
        return DB::transaction(function () use ($data) {
            $token = Str::random(64);

            $admin = Admin::create([
                'name'                       => $data['name'],
                'email'                      => $data['email'],
                'password'                   => Hash::make(Str::random(40)),
                'invitation_token'           => $token,
                'invitation_token_expires_at' => now()->addHours(48),
                'is_active'                  => false,
                'phone'                      => $data['phone'] ?? null,
                'avatar'                     => $data['avatar'] ?? null,
                'is_super'                   => $data['is_super'] ?? false,
            ]);

            $admin->syncRoles($data['roles']);

            SendEmailJob::dispatch(
                $admin->email,
                new AdminInvitationMail($admin, $token, 2880)
            );

            return $admin;
        });
    }
    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = Admin::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }

        if (!empty($filters['email'])) {
            $query->where('email', 'like', "%{$filters['email']}%");
        }

        if (!empty($filters['phone'])) {
            $query->where('phone', 'like', "%{$filters['phone']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function find(int $id): Admin
    {
        return Admin::findOrFail($id);
    }

    public function updateStatus(Admin $admin, string $status): Admin
    {
        $status = strtolower($status) === 'active' ? true : false;
        $admin->update(['is_active' => $status]);
        return $admin->fresh();
    }

    public function update(Admin $admin, array $data): Admin
    {
        $admin->update($data);
        return $admin->fresh();
    }
}
