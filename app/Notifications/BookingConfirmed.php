<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class BookingConfirmed extends Notification implements ShouldQueue
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
        $doctor = $appointment->doctor;
        $clinic = $appointment->medicalCenter;
        $amountPaid = (float) $appointment->doctor_fee_charged + (float) $appointment->clinic_fee_charged;

        return (new MailMessage)
            ->subject('Your appointment is confirmed')
            ->greeting('Booking confirmed')
            ->line('Your appointment with Dr. '.$doctor->name.' has been confirmed.')
            ->line('Clinic: '.$clinic->name.' — '.$clinic->address)
            ->line('Date & time: '.$appointment->appointment_date->format('D, M j, Y').' at '.Carbon::parse($appointment->appointment_time)->format('g:i A'))
            ->line('Amount paid: LKR '.number_format($amountPaid, 2))
            ->action('View my appointments', route('appointments.index'));
    }
}
