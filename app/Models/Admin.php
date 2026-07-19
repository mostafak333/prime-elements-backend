<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

#[Fillable([
    'name',
    'email',
    'password',
    'is_super',
    'invitation_token',
    'invitation_token_expires_at',
    'password_reset_token',
    'password_reset_token_expires_at',
    'is_active',
])]
#[Hidden(['password', 'remember_token', 'invitation_token', 'password_reset_token'])]
class Admin extends Authenticatable implements JWTSubject
{
    use HasRoles, SoftDeletes;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'guard' => 'api-admin',
        ];
    }

    protected function casts(): array
    {
        return [
            'email_verified_at'           => 'datetime',
            'password'                    => 'hashed',
            'is_super'                    => 'boolean',
            'is_active'                   => 'boolean',
            'invitation_token_expires_at'  => 'datetime',
            'password_reset_token_expires_at' => 'datetime',
        ];
    }
}
