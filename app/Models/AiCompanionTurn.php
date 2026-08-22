<?php

namespace App\Models;

use Database\Factories\AiCompanionTurnFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ai_companion_session_id', 'role', 'sequence', 'content'])]
class AiCompanionTurn extends Model
{
    /** @use HasFactory<AiCompanionTurnFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['content' => 'encrypted'];
    }

    /** @return BelongsTo<AiCompanionSession, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(AiCompanionSession::class, 'ai_companion_session_id');
    }
}
