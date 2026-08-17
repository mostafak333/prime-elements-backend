<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

#[Fillable([
    'name',
    'email',
    'password',
    'email_verification_token',
    'email_verification_token_expires_at',
    'password_reset_token',
    'password_reset_token_expires_at',
    'phone',
    'avatar',
    'email_verified_at',
    'status',
    'country',
    'city',
    'street_address',
    'apartment',
    'social_provider',
    'social_id',
])]
#[Hidden([
    'password',
    'remember_token',
    'email_verification_token',
    'password_reset_token',
    'social_id',
])]
class User extends Authenticatable implements JWTSubject
{
    use HasRoles, SoftDeletes;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'guard' => 'api-user',
        ];
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_verification_token_expires_at' => 'datetime',
            'password_reset_token_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function emailSubscriptions(): HasMany
    {
        return $this->hasMany(EmailSubscription::class);
    }
}
