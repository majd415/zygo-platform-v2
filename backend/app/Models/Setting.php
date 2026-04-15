<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_name', 
        'logo', 
        'price_per_km_usd', 
        'price_per_km_syp', 
        'support_phone',
        'whatsapp_phone',
        'email_support',
        'search_radius_km',
        'commission_rate',
        'comfort_multiplier',
        'premium_multiplier',
        'platform_earnings',
        'magic_login_enabled',
        'min_gift_amount'
    ];
}
