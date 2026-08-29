<?php

namespace App\Models;

use Database\Factories\MoodEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['patient_id', 'mood_score', 'mood_tags', 'sleep_hours', 'note', 'entry_date'])]
class MoodEntry extends Model
{
    /** @use HasFactory<MoodEntryFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'mood_score' => 'integer',
            'mood_tags' => 'array',
            'sleep_hours' => 'decimal:1',
            'entry_date' => 'date',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
