<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentNotification extends Notification
{
    use Queueable;

    private $paymentDetails;

    /**
     * Create a new notification instance.
     */
    public function __construct($paymentDetails)
    {
        $this->paymentDetails = $paymentDetails;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */

//    public function toMail(object $notifiable): MailMessage
//    {
//        return (new MailMessage)
//                    ->line('The introduction to the notification.')
//                    ->action('Notification Action', url('/'))
//                    ->line('Thank you for using our application!');
//    }


    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Payment Successful',
            'message' => 'Your payment of $' . $this->paymentDetails['amount'] . ' was successful.',
            'transaction_id' => $this->paymentDetails['transaction_id'],
            'cashback' => $this->paymentDetails['cashback'], // إذا كنت تريد تضمين الكاش باك
        ];
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
//            'amount' => $this->paymentDetails['amount'],
//            'currency' => $this->paymentDetails['currency'],
//            'status' => $this->paymentDetails['status'],
//            'product_name' => $this->paymentDetails['product_name'],
        ];
    }
}
