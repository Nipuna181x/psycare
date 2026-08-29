<?php

namespace App\Models;

use Database\Factories\ClinicStaffFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['medical_center_id', 'name', 'email', 'password', 'status'])]
#[Hidden(['password', 'remember_token'])]
class ClinicStaff extends Authenticatable
{
    /** @use HasFactory<ClinicStaffFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * @return BelongsTo<MedicalCenter, $this>
     */
    public function medicalCenter(): BelongsTo
    {
        return $this->belongsTo(MedicalCenter::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
