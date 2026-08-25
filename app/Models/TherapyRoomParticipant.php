<?php

namespace App\Models;

use Database\Factories\TherapyRoomParticipantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['therapy_room_id', 'patient_id', 'anonymous_label', 'join_order', 'removed_at'])]
class TherapyRoomParticipant extends Model
{
    /** @use HasFactory<TherapyRoomParticipantFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'removed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<TherapyRoom, $this>
     */
    public function therapyRoom(): BelongsTo
    {
        return $this->belongsTo(TherapyRoom::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
