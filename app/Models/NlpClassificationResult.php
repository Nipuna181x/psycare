<?php

namespace App\Models;

use Database\Factories\NlpClassificationResultFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'patient_id', 'ai_companion_session_id', 'entry_date', 'risk_level', 'self_harm_flag', 'self_harm_confidence',
    'phq9_severity', 'gad7_severity', 'symptoms', 'symptom_scores',
])]
class NlpClassificationResult extends Model
{
    /** @use HasFactory<NlpClassificationResultFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'self_harm_flag' => 'boolean',
            'self_harm_confidence' => 'float',
            'symptoms' => 'array',
            'symptom_scores' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /** @return BelongsTo<AiCompanionSession, $this> */
    public function aiCompanionSession(): BelongsTo
    {
        return $this->belongsTo(AiCompanionSession::class);
    }
}
