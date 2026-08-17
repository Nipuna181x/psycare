<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\View\View;

class DoctorController extends Controller
{
    /**
     * Display every bookable doctor for patients to search and filter.
     */
    public function index(): View
    {
        $doctors = Doctor::query()
            ->where('status', 'active')
            ->whereHas('medicalCenter', fn ($query) => $query->where('status', 'approved'))
            ->with('medicalCenter')
            ->orderBy('name')
            ->get();

        return view('doctors.index', [
            'doctors' => $doctors,
            'specializations' => $doctors->pluck('specialization')->filter()->unique()->sort()->values(),
        ]);
    }

    /**
     * Display a single doctor's public profile.
     */
    public function show(Doctor $doctor): View
    {
        abort_unless($doctor->isBookable(), 404);

        return view('doctors.show', [
            'doctor' => $doctor->load('medicalCenter'),
        ]);
    }
}
