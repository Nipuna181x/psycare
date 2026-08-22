<?php

namespace App\Models;

use Database\Factories\PatientNlpReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'appointment_id', 'ai_companion_session_id', 'status', 'schema_version', 'report', 'generated_at', 'reviewed_at'])]
class PatientNlpReport extends Model
{
    /** @use HasFactory<PatientNlpReportFactory> */
    use HasFactory;

    protected $attributes = ['status' => 'generated', 'schema_version' => '1.0'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['report' => 'encrypted:array', 'generated_at' => 'datetime', 'reviewed_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Appointment, $this> */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /** @return BelongsTo<AiCompanionSession, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(AiCompanionSession::class, 'ai_companion_session_id');
    }
}
