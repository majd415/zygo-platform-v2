<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'ride_id',
        'driver_id',
        'passenger_id',
        'rating',
        'comment',
    ];

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function passenger()
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }
}
