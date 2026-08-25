<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when the doctor removes a participant from a live call. This is a live-session
 * control distinct from removing them from the room roster before it goes live — clients tear
 * down the peer connection for the targeted synthetic id only.
 */
class TherapyRoomParticipantKicked implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $therapyRoomId,
        public readonly string $targetId,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('therapy-room.'.$this->therapyRoomId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'participant.kicked';
    }
}
