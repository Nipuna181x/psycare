<?php

namespace App\Http\Controllers\MedicalCenter;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorClinicAffiliation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DoctorSearchController extends Controller
{
    /**
     * Search approved doctors by licence number (primary) or name (secondary).
     */
    public function index(Request $request): View
    {
        $clinic = Auth::guard('medical_center')->user();
        $licenseNumber = trim((string) $request->string('license_number'));
        $name = trim((string) $request->string('name'));

        $doctors = collect();

        if ($licenseNumber !== '' || $name !== '') {
            $doctors = Doctor::query()
                ->where('status', 'approved')
                ->where('onboarding_step', 'profile_complete')
                ->when($licenseNumber !== '', fn ($query) => $query->where('license_number', 'like', "%{$licenseNumber}%"))
                ->when($licenseNumber === '' && $name !== '', fn ($query) => $query->where('name', 'like', "%{$name}%"))
                ->withCount('activeAffiliations')
                ->orderBy('name')
                ->get()
                ->map(fn (Doctor $doctor): array => [
                    'doctor' => $doctor,
                    'existingAffiliation' => $doctor->affiliations()->where('clinic_id', $clinic->id)->first(),
                ]);
        }

        return view('medical-center.doctor-search.index', [
            'results' => $doctors,
            'filters' => [
                'license_number' => $licenseNumber,
                'name' => $name,
            ],
        ]);
    }

    /**
     * Send a work request to a doctor.
     */
    public function sendRequest(Doctor $doctor): RedirectResponse
    {
        $clinic = Auth::guard('medical_center')->user();

        abort_if(
            $doctor->affiliations()->where('clinic_id', $clinic->id)->whereIn('status', ['requested', 'active'])->exists(),
            422,
            'A request is already pending or active with this doctor.'
        );

        DoctorClinicAffiliation::create([
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'status' => 'requested',
            'requested_by_clinic_at' => now(),
        ]);

        return back()->with('status', "Work request sent to Dr. {$doctor->name}.");
    }
}
