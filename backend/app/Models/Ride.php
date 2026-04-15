<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    use HasFactory;

    protected $fillable = [
        'rider_id',
        'driver_id',
        'ride_code',
        'share_token',
        'pickup_address',
        'pickup_lat',
        'pickup_lng',
        'dropoff_address',
        'dropoff_lat',
        'dropoff_lng',
        'ride_price',
        'currency',
        'status',
        'car_type',
        'payment_method',
        'coupon_id',
        'discount_amount',
        'distance_text',
        'duration_text',
        'accepted_at',
        'arrived_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'distance_meters',
        'duration_seconds',
        'rating',
        'rating_comment',
        'cancel_reason',
        'scheduled_at',
        'commission_amount',
        'driver_earnings',
        'request_expires_at',
    ];

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function requests()
    {
        return $this->hasMany(RideRequest::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
