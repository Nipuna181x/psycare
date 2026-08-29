<?php

namespace App\Models;

use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

#[Fillable([
    'user_id', 'doctor_id', 'medical_center_id', 'doctor_availability_slot_id',
    'appointment_date', 'appointment_time', 'mode',
    'patient_name', 'patient_age', 'patient_gender', 'patient_phone', 'patient_email', 'reason',
    'consultation_fee', 'doctor_fee_charged', 'clinic_fee_charged',
    'pre_assessment', 'pre_assessment_mood_rating', 'pre_assessment_summary', 'pre_assessment_risk_level',
    'phq9_total', 'phq9_severity', 'gad7_total', 'gad7_severity', 'self_harm_flag',
    'requires_immediate_escalation', 'screener_open_notes', 'screener_completed_at',
    'escalation_reviewed', 'escalation_reviewed_at',
    'reminder_24h_sent_at', 'reminder_1h_sent_at',
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
            'self_harm_flag' => 'boolean',
            'requires_immediate_escalation' => 'boolean',
            'escalation_reviewed' => 'boolean',
            'escalation_reviewed_at' => 'datetime',
            'reminder_24h_sent_at' => 'datetime',
            'reminder_1h_sent_at' => 'datetime',
            'screener_completed_at' => 'datetime',
            'doctor_fee_charged' => 'decimal:2',
            'clinic_fee_charged' => 'decimal:2',
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
     * @return BelongsTo<DoctorAvailabilitySlot, $this>
     */
    public function availabilitySlot(): BelongsTo
    {
        return $this->belongsTo(DoctorAvailabilitySlot::class, 'doctor_availability_slot_id');
    }

    /** @return HasMany<PatientNlpReport, $this> */
    public function patientNlpReports(): HasMany
    {
        return $this->hasMany(PatientNlpReport::class);
    }

    /** @return HasOne<Prescription, $this> */
    public function prescription(): HasOne
    {
        return $this->hasOne(Prescription::class);
    }

    /** @return HasOne<Payment, $this> */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function requiresCrisisEscalation(): bool
    {
        return $this->requires_immediate_escalation || $this->self_harm_flag;
    }

    /**
     * The appointment's scheduled date and time combined into a single instant.
     */
    public function startsAt(): Carbon
    {
        return Carbon::parse($this->appointment_date->format('Y-m-d').' '.$this->appointment_time);
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
     * Hide temporary Checkout reservations from clinical and financial views
     * until Stripe has confirmed the appointment.
     *
     * @param  Builder<Appointment>  $query
     * @return Builder<Appointment>
     */
    public function scopeVisibleToCareTeam(Builder $query): Builder
    {
        return $query->where('status', '!=', 'pending_payment');
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
