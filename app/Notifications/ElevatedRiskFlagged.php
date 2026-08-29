<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ElevatedRiskFlagged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Appointment $appointment,
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

        return (new MailMessage)
            ->subject('Urgent: Elevated-risk pre-assessment requires review')
            ->greeting('Elevated-risk pre-assessment flagged')
            ->line('Patient: '.$appointment->patient_name)
            ->line('PHQ-9: '.($appointment->phq9_total ?? '—').' ('.($appointment->phq9_severity ?? 'not scored').')')
            ->line('GAD-7: '.($appointment->gad7_total ?? '—').' ('.($appointment->gad7_severity ?? 'not scored').')')
            ->line('Risk level: '.($appointment->pre_assessment_risk_level ?? 'unspecified'))
            ->line('Please review this case promptly.')
            ->action('Review case', route('doctor.appointments.show', $appointment));
    }
}
