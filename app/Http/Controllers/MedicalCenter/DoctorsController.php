<?php

namespace App\Http\Controllers\MedicalCenter;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorClinicAffiliation;
use App\Services\CurrentClinic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorsController extends Controller
{
    /**
     * Display the Doctors page: My Doctors, Pending Requests, and Search & Request tabs.
     */
    public function index(Request $request, CurrentClinic $currentClinic): View
    {
        $clinic = $currentClinic->model();
        $tab = in_array($request->string('tab')->value(), ['my-doctors', 'pending', 'search'], true)
            ? $request->string('tab')->value()
            : 'my-doctors';

        $licenseNumber = trim((string) $request->string('license_number'));
        $name = trim((string) $request->string('name'));

        $searchResults = collect();
        if ($tab === 'search' && ($licenseNumber !== '' || $name !== '')) {
            $searchResults = Doctor::query()
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

        $myDoctors = $clinic->affiliations()
            ->where('status', 'active')
            ->with(['doctor' => fn ($query) => $query->withCount(['appointments' => fn ($q) => $q->where('medical_center_id', $clinic->id)])])
            ->latest()
            ->get();

        return view('medical-center.doctors.index', [
            'tab' => $tab,
            'myDoctors' => $myDoctors,
            'pendingRequests' => $clinic->affiliations()->where('status', 'requested')->with('doctor')->latest()->get(),
            'recentActivity' => $clinic->affiliations()
                ->whereIn('status', ['active', 'declined'])
                ->whereNotNull('responded_by_doctor_at')
                ->with('doctor')
                ->latest('responded_by_doctor_at')
                ->take(10)
                ->get(),
            'searchResults' => $searchResults,
            'filters' => [
                'license_number' => $licenseNumber,
                'name' => $name,
            ],
        ]);
    }

    /**
     * Send a work request to a doctor.
     */
    public function sendRequest(Doctor $doctor, CurrentClinic $currentClinic): RedirectResponse
    {
        $clinic = $currentClinic->model();

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
