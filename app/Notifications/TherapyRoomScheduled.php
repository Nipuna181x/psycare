<?php

namespace App\Notifications;

use App\Models\TherapyRoomParticipant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TherapyRoomScheduled extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * Takes the specific participant (not just the room) so mail/database content can only
     * ever render this recipient's own anonymous label, never another participant's.
     */
    public function __construct(
        public readonly TherapyRoomParticipant $participant,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $room = $this->participant->therapyRoom;

        return (new MailMessage)
            ->subject('Group session scheduled: '.$room->title)
            ->line("You've been added to a group session as {$this->participant->anonymous_label}.")
            ->line('Scheduled for: '.$room->scheduled_at->format('D, M j, Y g:i A'))
            ->line('Duration: '.$room->duration_minutes.' minutes')
            ->action('View session', route('therapy-rooms.show', $room));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $room = $this->participant->therapyRoom;

        return [
            'therapy_room_id' => $room->id,
            'title' => $room->title,
            'scheduled_at' => $room->scheduled_at->toIso8601String(),
            'anonymous_label' => $this->participant->anonymous_label,
            'url' => route('therapy-rooms.show', $room),
        ];
    }
}
