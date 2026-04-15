<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'user_type',
        'amount',
        'type',
        'transaction_type',
        'description',
        'balance_before',
        'balance_after',
        'ride_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }
}
