<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $otp;
    public $userName;
    public $otpTime;

    public function __construct(public User $user)
    {
        $otpData = $this->user->generateOTP(config('travel.otp.TYPE'), config('travel.otp.LENGTH'), config('travel.otp.VALID'));
        $this->otp = $otpData['otp'];
        $this->otpTime = $otpData['valid'];
        $this->userName = $this->user->name;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
//        return new Envelope(
//            subject: 'Test Email',
//        );
        return new Envelope(
            subject: 'Your OTP Code',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
//        return new Content(
//            view: 'emails.test',
//        );
        return new Content(
            view: 'emails.otp', // قم بإنشاء هذا العرض
            with: [
                'userName' => $this->userName,
                'otp' => $this->otp,
                'otpTime' => $this->otpTime,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
