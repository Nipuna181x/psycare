<?php

namespace App\Models;

use Database\Factories\TherapyRoomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['doctor_id', 'title', 'topic', 'status', 'scheduled_at', 'duration_minutes', 'started_at', 'ended_at'])]
class TherapyRoom extends Model
{
    /** @use HasFactory<TherapyRoomFactory> */
    use HasFactory;

    /**
     * Maximum patients allowed in a single room. Full-mesh WebRTC degrades in
     * bandwidth/CPU per client past this point; an SFU would be needed to raise it.
     */
    public const MAX_PARTICIPANTS = 8;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Doctor, $this>
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * All participant pivot rows, including ones removed before the room went live.
     *
     * @return HasMany<TherapyRoomParticipant, $this>
     */
    public function participants(): HasMany
    {
        return $this->hasMany(TherapyRoomParticipant::class);
    }

    /**
     * @return HasMany<TherapyRoomParticipant, $this>
     */
    public function activeParticipants(): HasMany
    {
        return $this->participants()->whereNull('removed_at');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'therapy_room_participants', 'therapy_room_id', 'patient_id')
            ->wherePivotNull('removed_at')
            ->withPivot(['anonymous_label', 'join_order', 'removed_at'])
            ->withTimestamps();
    }

    /**
     * Scope rooms that are still ahead (scheduled or currently live).
     *
     * @param  Builder<TherapyRoom>  $query
     * @return Builder<TherapyRoom>
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereIn('status', ['scheduled', 'live'])->orderBy('scheduled_at');
    }

    public function isJoinable(): bool
    {
        return in_array($this->status, ['scheduled', 'live'], true);
    }

    public function isEditable(): bool
    {
        return $this->status === 'scheduled';
    }
}
