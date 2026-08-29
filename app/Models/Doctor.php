<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\DoctorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;

#[Fillable(['name', 'email', 'password', 'license_number', 'phone', 'specialization', 'bio', 'profile_photo', 'years_of_experience', 'consultation_fee', 'consultation_mode', 'rating', 'status', 'onboarding_step', 'approved_at', 'approved_by'])]
#[Hidden(['password', 'remember_token'])]
class Doctor extends Authenticatable
{
    /** @use HasFactory<DoctorFactory> */
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
            'consultation_fee' => 'decimal:2',
            'rating' => 'decimal:1',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<DoctorClinicAffiliation, $this>
     */
    public function affiliations(): HasMany
    {
        return $this->hasMany(DoctorClinicAffiliation::class);
    }

    /**
     * @return HasMany<DoctorClinicAffiliation, $this>
     */
    public function activeAffiliations(): HasMany
    {
        return $this->affiliations()->where('status', 'active');
    }

    /**
     * @return BelongsToMany<MedicalCenter, $this>
     */
    public function clinics(): BelongsToMany
    {
        return $this->belongsToMany(MedicalCenter::class, 'doctor_clinic_affiliations', 'doctor_id', 'clinic_id')
            ->wherePivot('status', 'active')
            ->withPivot(['status', 'requested_by_clinic_at', 'responded_by_doctor_at']);
    }

    /**
     * @return HasMany<DoctorAvailabilitySlot, $this>
     */
    public function availabilitySlots(): HasMany
    {
        return $this->hasMany(DoctorAvailabilitySlot::class);
    }

    /**
     * @return HasMany<Appointment, $this>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** @return HasMany<DoctorPayout, $this> */
    public function payouts(): HasMany
    {
        return $this->hasMany(DoctorPayout::class);
    }

    /**
     * @return HasMany<TherapyRoom, $this>
     */
    public function therapyRooms(): HasMany
    {
        return $this->hasMany(TherapyRoom::class);
    }

    /** @return HasMany<Prescription, $this> */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /** @return HasMany<PatientConsent, $this> */
    public function consentsReceived(): HasMany
    {
        return $this->hasMany(PatientConsent::class);
    }

    /**
     * @return BelongsTo<Admin, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public function isBookable(): bool
    {
        return $this->status === 'approved' && $this->onboarding_step === 'profile_complete';
    }

    public function hasActiveAffiliation(): bool
    {
        return $this->activeAffiliations()->exists();
    }

    public function isPriced(): bool
    {
        return $this->consultation_fee !== null;
    }

    public function nextAvailableLabel(): string
    {
        $next = $this->appointments()->upcoming()->orderBy('appointment_date')->orderBy('appointment_time')->first();

        if (! $next) {
            return 'By request';
        }

        $date = $next->appointment_date->isToday() ? 'Today' : $next->appointment_date->format('D, M j');

        return $date.', '.Carbon::parse($next->appointment_time)->format('g:i A');
    }

    public function initials(): string
    {
        return collect(preg_split('/\s+/', trim($this->name)))
            ->filter()
            ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
            ->take(2)
            ->implode('');
    }

    public function avatarUrl(): ?string
    {
        if (! $this->profile_photo) {
            return null;
        }

        return str_starts_with($this->profile_photo, 'doctor-avatars/')
            ? Storage::disk('public')->url($this->profile_photo)
            : Vite::asset('resources/images/psycare/'.$this->profile_photo);
    }

    public function consultationModeLabel(): string
    {
        return match ($this->consultation_mode) {
            'in_person' => 'In-person',
            'online' => 'Online',
            default => 'In-person & online',
        };
    }
}
