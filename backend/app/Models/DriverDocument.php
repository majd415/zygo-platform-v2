<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'national_id_front',
        'national_id_back',
        'car_photo',
        'car_photo_front',
        'car_photo_back',
        'car_photo_left',
        'car_photo_right',
        'car_type',
        'car_model',
        'car_year',
        'car_plate',
        'car_color',
        'driving_license',
        'license_back',
        'registration_front',
        'registration_back',
        'insurance_photo'
    ];

    public function user() { return $this->belongsTo(User::class); }
}
