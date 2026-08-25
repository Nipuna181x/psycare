<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Relays a single SDP offer/answer or ICE candidate between two verified participants
 * of a therapy room. Only ever dispatched from an authenticated, policy-checked HTTP
 * endpoint (see TherapyRoomController::signal) — never triggered client-to-client, so
 * every signal is re-authorized server-side on every send.
 */
class TherapyRoomSignal implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, mixed>  $payload  Raw SDP or ICE candidate data.
     */
    public function __construct(
        public readonly int $therapyRoomId,
        public readonly string $from,
        public readonly string $to,
        public readonly string $type,
        public readonly array $payload,
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
        return 'signal';
    }
}
