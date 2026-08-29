<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DoctorController extends Controller
{
    /**
     * Display every bookable doctor for patients to search and filter.
     */
    public function index(): View
    {
        $doctors = Doctor::query()
            ->where('status', 'approved')
            ->where('onboarding_step', 'profile_complete')
            ->with(['bookableAffiliations.clinic', 'availabilitySlots' => fn ($query) => $query->available()])
            ->orderBy('name')
            ->get();

        $cities = $doctors
            ->flatMap(fn (Doctor $doctor) => $doctor->bookableAffiliations->pluck('clinic.address'))
            ->filter()
            ->map(fn (string $address): string => trim(Str::afterLast($address, ',')))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('doctors.index', [
            'doctors' => $doctors,
            'specializations' => $doctors->pluck('specialization')->filter()->unique()->sort()->values(),
            'cities' => $cities,
        ]);
    }

    /**
     * Display a single doctor's public profile.
     */
    public function show(Doctor $doctor): View
    {
        abort_unless($doctor->status === 'approved' && $doctor->onboarding_step === 'profile_complete', 404);

        return view('doctors.show', [
            'doctor' => $doctor->load('bookableAffiliations.clinic'),
            'hasActiveAffiliation' => $doctor->hasBookableAffiliation(),
        ]);
    }
}
