<?php

namespace App\Models;

use Database\Factories\DoctorAvailabilitySlotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['doctor_id', 'clinic_id', 'date', 'start_time', 'end_time', 'is_booked', 'appointment_id'])]
class DoctorAvailabilitySlot extends Model
{
    /** @use HasFactory<DoctorAvailabilitySlotFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_booked' => 'boolean',
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

    /**
     * @return BelongsTo<Appointment, $this>
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * @param  Builder<DoctorAvailabilitySlot>  $query
     * @return Builder<DoctorAvailabilitySlot>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_booked', false);
    }
}
