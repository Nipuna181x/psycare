<?php

namespace App\Models;

use Database\Factories\DoctorPayoutFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Internal bookkeeping record only. A DoctorPayout never initiates a bank
 * transfer; real money movement remains outside PsyCare without Stripe Connect.
 */
#[Fillable([
    'clinic_id', 'doctor_id', 'marked_by_type', 'marked_by_id', 'marked_by_name',
    'amount', 'payment_count', 'paid_at',
])]
class DoctorPayout extends Model
{
    /** @use HasFactory<DoctorPayoutFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_count' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<MedicalCenter, $this> */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(MedicalCenter::class, 'clinic_id');
    }

    /** @return BelongsTo<Doctor, $this> */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
