<?php

namespace App\Models;

use Database\Factories\DoctorClinicAffiliationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['doctor_id', 'clinic_id', 'status', 'requested_by_clinic_at', 'responded_by_doctor_at', 'ended_at'])]
class DoctorClinicAffiliation extends Model
{
    /** @use HasFactory<DoctorClinicAffiliationFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requested_by_clinic_at' => 'datetime',
            'responded_by_doctor_at' => 'datetime',
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
     * @return BelongsTo<MedicalCenter, $this>
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(MedicalCenter::class, 'clinic_id');
    }
}
