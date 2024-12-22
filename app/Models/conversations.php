<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class conversations extends Model
{
    protected $table='conversations';
    protected $fillable=[
      'id',
       'user_id',
        'customer_service_id',
        'created_at',
        'updated_at',
    ];

    public $timestamps=true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customerService()
    {
        return $this->belongsTo(User::class, 'customer_service_id');
    }

    public function messages()
    {
        return $this->hasMany(messages::class,'conversation_id');
    }

}
