<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRegisterNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $otp;

    public $otpTime;

    /**
     * Create a new notification instance.
     *
     * @throws \Exception
     */
    public function __construct(public User $user)
    {
        $otpData = $this->user->generateOTP(config('travel.otp.TYPE'), config('travel.otp.LENGTH'), config('travel.otp.VALID'));
        $this->otp = $otpData['otp'];
        $this->otpTime = $otpData['valid'];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('Hello '.$this->user->name)
            ->line('otp '.$this->otp)
            ->line('valid for '.$this->otpTime.' minutes');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
