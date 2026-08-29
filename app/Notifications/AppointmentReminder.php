<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class AppointmentReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Appointment $appointment,
        public readonly string $window,
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
        $appointment = $this->appointment;
        $label = $this->window === '24h' ? 'tomorrow' : 'in about an hour';

        return (new MailMessage)
            ->subject('Reminder: your appointment is '.$label)
            ->greeting('Appointment reminder')
            ->line('This is a reminder that your appointment with Dr. '.$appointment->doctor->name.' is '.$label.'.')
            ->line('Date & time: '.$appointment->appointment_date->format('D, M j, Y').' at '.Carbon::parse($appointment->appointment_time)->format('g:i A'))
            ->line('Clinic: '.$appointment->medicalCenter->name)
            ->action('View appointment', route('appointments.index'));
    }
}
