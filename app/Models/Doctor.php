<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\DoctorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Vite;

#[Fillable(['medical_center_id', 'name', 'email', 'username', 'password', 'specialization', 'avatar', 'bio', 'years_experience', 'consultation_fee', 'consultation_mode', 'rating', 'phone', 'status'])]
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
        ];
    }

    /**
     * @return BelongsTo<MedicalCenter, $this>
     */
    public function medicalCenter(): BelongsTo
    {
        return $this->belongsTo(MedicalCenter::class);
    }

    /**
     * @return HasMany<Appointment, $this>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function isBookable(): bool
    {
        return $this->status === 'active' && $this->medicalCenter?->isApproved();
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
        return $this->avatar ? Vite::asset('resources/images/psycare/'.$this->avatar) : null;
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
