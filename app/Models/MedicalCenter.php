<?php

namespace App\Models;

use Database\Factories\MedicalCenterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'phone', 'address', 'registration_number', 'password', 'status', 'facility_fee', 'logo_path', 'operating_hours', 'description'])]
#[Hidden(['password', 'remember_token'])]
class MedicalCenter extends Authenticatable
{
    /** @use HasFactory<MedicalCenterFactory> */
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
            'facility_fee' => 'decimal:2',
            'operating_hours' => 'array',
        ];
    }

    /**
     * @return HasMany<DoctorClinicAffiliation, $this>
     */
    public function affiliations(): HasMany
    {
        return $this->hasMany(DoctorClinicAffiliation::class, 'clinic_id');
    }

    /**
     * @return BelongsToMany<Doctor, $this>
     */
    public function affiliatedDoctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class, 'doctor_clinic_affiliations', 'clinic_id', 'doctor_id')
            ->wherePivot('status', 'active');
    }

    /**
     * @return HasMany<Appointment, $this>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * @return HasMany<ClinicStaff, $this>
     */
    public function staff(): HasMany
    {
        return $this->hasMany(ClinicStaff::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'clinic_id');
    }

    /** @return HasMany<DoctorPayout, $this> */
    public function doctorPayouts(): HasMany
    {
        return $this->hasMany(DoctorPayout::class, 'clinic_id');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPriced(): bool
    {
        return $this->facility_fee !== null;
    }

    public function logoUrl(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->logo_path);
    }
}
