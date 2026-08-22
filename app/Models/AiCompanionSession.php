<?php

namespace App\Models;

use Database\Factories\AiCompanionSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['public_id', 'user_id', 'language', 'consented_at', 'ended_at'])]
class AiCompanionSession extends Model
{
    /** @use HasFactory<AiCompanionSessionFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['consented_at' => 'datetime', 'ended_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<AiCompanionTurn, $this> */
    public function turns(): HasMany
    {
        return $this->hasMany(AiCompanionTurn::class)->orderBy('sequence');
    }

    /** @return HasOne<PatientNlpReport, $this> */
    public function report(): HasOne
    {
        return $this->hasOne(PatientNlpReport::class);
    }
}
