<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class messages extends Model
{
    protected $table='messages';
    protected $fillable=[
      'id',
      'conversation_id',
      'user_id',
      'message',
      'message_type',
      'read_at',
      'created_at',
      'updated_at'
    ];

    public $timestamps=true;
//    public function getMessageAttribute($val)
//    {
//        return ($val!=null)? asset('assets/'.$val):"";
//    }
    public function markAsRead()
    {
        $this->read_at = now();
        $this->save();
    }

    public function conversation()
    {
        return $this->belongsTo(conversations::class,'conversation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

}
