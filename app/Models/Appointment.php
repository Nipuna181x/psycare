<?php

namespace App\Models;

use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'doctor_id', 'medical_center_id',
    'appointment_date', 'appointment_time', 'mode',
    'patient_name', 'patient_age', 'patient_gender', 'patient_phone', 'patient_email', 'reason',
    'consultation_fee',
    'pre_assessment', 'pre_assessment_mood_rating', 'pre_assessment_summary', 'pre_assessment_risk_level',
    'status',
])]
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'pre_assessment' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
    public function medicalCenter(): BelongsTo
    {
        return $this->belongsTo(MedicalCenter::class);
    }

    /**
     * Scope appointments to those still ahead (confirmed and not in the past).
     *
     * @param  Builder<Appointment>  $query
     * @return Builder<Appointment>
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('status', 'confirmed')
            ->where(function (Builder $query): void {
                $query->whereDate('appointment_date', '>', now()->toDateString())
                    ->orWhere(function (Builder $query): void {
                        $query->whereDate('appointment_date', now()->toDateString())
                            ->whereTime('appointment_time', '>=', now()->toTimeString());
                    });
            });
    }

    /**
     * Scope appointments to today.
     *
     * @param  Builder<Appointment>  $query
     * @return Builder<Appointment>
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('appointment_date', now()->toDateString());
    }
}
