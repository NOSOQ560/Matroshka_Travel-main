<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'amount',
        'currency',
        'product_name',
        'description',
        'payment_status',
        'created_at',
        'updated_at',
    ];
    public $timestamps=true;

    public function cashback()
    {
        return $this->hasOne(Cashback::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
