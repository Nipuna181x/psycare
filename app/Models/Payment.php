<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'appointment_id', 'doctor_id', 'clinic_id', 'patient_id', 'doctor_payout_id',
    'stripe_session_id', 'stripe_payment_intent_id', 'amount', 'currency',
    'doctor_amount', 'clinic_amount', 'status', 'doctor_payout_status',
    'payment_method', 'card_last_four', 'doctor_paid_at', 'processed_at',
    'notifications_sent_at', 'expires_at',
])]
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $attributes = [
        'currency' => 'lkr',
        'status' => 'pending',
        'doctor_payout_status' => 'unpaid',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'doctor_amount' => 'decimal:2',
            'clinic_amount' => 'decimal:2',
            'doctor_paid_at' => 'datetime',
            'processed_at' => 'datetime',
            'notifications_sent_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Appointment, $this> */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /** @return BelongsTo<Doctor, $this> */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /** @return BelongsTo<MedicalCenter, $this> */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(MedicalCenter::class, 'clinic_id');
    }

    /** @return BelongsTo<User, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /** @return BelongsTo<DoctorPayout, $this> */
    public function doctorPayout(): BelongsTo
    {
        return $this->belongsTo(DoctorPayout::class);
    }

    /** @param Builder<Payment> $query @return Builder<Payment> */
    public function scopeSucceeded(Builder $query): Builder
    {
        return $query->where('status', 'succeeded');
    }

    /** @param Builder<Payment> $query @return Builder<Payment> */
    public function scopeUnpaidToDoctor(Builder $query): Builder
    {
        return $query->where('doctor_payout_status', 'unpaid');
    }
}
