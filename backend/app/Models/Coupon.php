<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ['code', 'discount_percentage', 'fixed_discount', 'expiration_date', 'usage_limit', 'used_count', 'is_active'];
}
