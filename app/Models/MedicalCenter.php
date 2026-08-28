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

#[Fillable(['name', 'email', 'phone', 'address', 'registration_number', 'password', 'status'])]
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

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
