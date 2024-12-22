<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarReservation extends Model
{
    protected $fillable = [
        'user_id',
        'car_id',
        'pickup_location',
        'arrival_location',
        'departing',
        'returning',
        'packages',
        'childrens',
        'adults',
        'trip_type',
        'additional_notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
