<?php

namespace App\Notifications;

use App\Models\DoctorClinicAffiliation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClinicWorkRequestReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly DoctorClinicAffiliation $affiliation,
    ) {}

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
        $clinic = $this->affiliation->clinic;

        return (new MailMessage)
            ->subject('New clinic work request: '.$clinic->name)
            ->greeting('New work request')
            ->line($clinic->name.' has invited you to work with them.')
            ->line('Location: '.$clinic->address)
            ->action('View request', route('doctor.clinic-requests.index'));
    }
}
