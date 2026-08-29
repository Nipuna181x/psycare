<?php

namespace App\Notifications;

use App\Models\DoctorClinicAffiliation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClinicRequestResponded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly DoctorClinicAffiliation $affiliation,
        public readonly bool $accepted,
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
        $doctor = $this->affiliation->doctor;
        $statusWord = $this->accepted ? 'accepted' : 'declined';

        $message = (new MailMessage)
            ->subject('Dr. '.$doctor->name.' '.$statusWord.' your work request')
            ->greeting('Work request '.$statusWord)
            ->line('Dr. '.$doctor->name.' has '.$statusWord.' your work request.');

        if ($this->accepted) {
            $message->action('View my doctors', route('medical-center.doctors.index', ['tab' => 'my-doctors']));
        }

        return $message;
    }
}
