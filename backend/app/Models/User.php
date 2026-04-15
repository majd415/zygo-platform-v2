<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    
    protected $appends = ['avatar_url'];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'service_category',
        'avatar',
        'verification_code',
        'email_verified_at',
        'status',
        'rejection_reason',
        'wallet_balance',
        'latitude',
        'longitude',
        'language',
        'is_online',
        'last_latitude',
        'last_longitude',
        'last_location_at',
        'rating',
        'rating_count',
        'fcm_token',
        'bio',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getAvatarUrlAttribute()
    {
        if (empty($this->avatar)) return null;
        if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) return $this->avatar;
        return asset($this->avatar);
    }

    public function savedLocations()
    {
        return $this->hasMany(SavedLocation::class);
    }
}

