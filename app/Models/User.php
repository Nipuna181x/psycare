<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'mobile', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return HasMany<Appointment, $this>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /** @return HasMany<AiCompanionSession, $this> */
    public function aiCompanionSessions(): HasMany
    {
        return $this->hasMany(AiCompanionSession::class);
    }

    /** @return HasMany<PatientNlpReport, $this> */
    public function patientNlpReports(): HasMany
    {
        return $this->hasMany(PatientNlpReport::class);
    }

    /** @return HasMany<NlpClassificationResult, $this> */
    public function nlpClassificationResults(): HasMany
    {
        return $this->hasMany(NlpClassificationResult::class, 'patient_id');
    }

    /** @return HasMany<TherapyRoomParticipant, $this> */
    public function therapyRoomParticipations(): HasMany
    {
        return $this->hasMany(TherapyRoomParticipant::class, 'patient_id');
    }

    /** @return HasMany<Prescription, $this> */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'patient_id');
    }

    /** @return HasMany<PatientConsent, $this> */
    public function consents(): HasMany
    {
        return $this->hasMany(PatientConsent::class, 'patient_id');
    }
}
