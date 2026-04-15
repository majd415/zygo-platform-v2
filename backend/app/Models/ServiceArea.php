<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ServiceArea extends Model
{
    protected $fillable = ['name', 'latitude', 'longitude', 'radius_km', 'is_active'];
}
