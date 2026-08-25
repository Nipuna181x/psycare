<?php

namespace App\Notifications;

use App\Models\TherapyRoomParticipant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TherapyRoomParticipantRemoved extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
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
            ->subject('Group session cancelled: '.$room->title)
            ->line("You've been removed from the group session \"{$room->title}\" that was scheduled for ".$room->scheduled_at->format('D, M j, Y g:i A').'.')
            ->line('You will no longer be able to join this session.');
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
            'message' => "You've been removed from \"{$room->title}\".",
        ];
    }
}
