<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens,
        HasFactory,
        InteractsWithMedia,
        Notifiable,
        SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'email',
        'email_verified_at',
        'password',
        'provider',
        'provider_id',
        'otp',
        'otp_till',
        'phone',
        'country',
        'gender',
        'website',
        'social_media',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    //#################### OTP #####################
    public function generateOTP(string $type, int $length, int $time)
    {
        switch ($type) {
            case 'alphanumeric':
                $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                $otp = substr(str_shuffle(str_repeat($characters, $length)), 0, $length);
                break;

            case 'numeric':
                $otp = str_pad(mt_rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
                break;

            default:
                throw new Exception("Invalid OTP type: $type. Allowed types are 'numeric' and 'alphanumeric'.");
        }
        $this->otp = $otp;
        $this->otp_till = now()->addMinutes($time);
        $this->save();

        return [
            'otp' => $otp,
            'valid' => $time,
        ];
    }

    public function resetOTP()
    {
        $this->otp = null;
        $this->otp_till = null;
        $this->save();
    }

    //#################### Spatie Media #####################
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('Profile')->singleFile();
    }

    public function carReservations(): HasMany
    {
        return $this->hasMany(CarReservation::class);
    }

    public function conversations()
    {
        return $this->hasMany(conversations::class);
    }

    public function messages()
    {
        return $this->hasMany(messages::class);
    }

}
