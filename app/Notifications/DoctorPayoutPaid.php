<?php

namespace App\Notifications;

use App\Models\DoctorPayout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DoctorPayoutPaid extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly DoctorPayout $payout) {}

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
        $clinic = $this->payout->clinic;

        return (new MailMessage)
            ->subject('Payout recorded by '.$clinic->name)
            ->greeting('Your clinic payout has been recorded')
            ->line($clinic->name.' marked a payout of LKR '.number_format((float) $this->payout->amount, 2).' as paid.')
            ->line('Payment count: '.$this->payout->payment_count)
            ->line('Recorded at: '.$this->payout->paid_at->format('D, M j, Y \a\t g:i A'))
            ->line('Once the funds reach you, open Payouts and select “I’ve received it” to complete the audit record.')
            ->action('Review payout', route('doctor.payouts.index'));
    }
}
