<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Car extends Model
{
    use SoftDeletes;

    protected $table = 'cars';

    protected $fillable = [
        'name',
        'brand',
        'type',
        'passenger_from',
        'passenger_to',
        'package_from',
        'package_to',
        'airport_to_town',
        'hour_in_town',
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(CarReservation::class);
    }
}
