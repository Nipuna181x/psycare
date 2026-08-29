<?php

namespace App\Http\Controllers\MedicalCenter;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CurrentClinic;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    /**
     * List every patient with at least one appointment at this clinic,
     * deduplicated, across any of the clinic's doctors.
     */
    public function index(Request $request, CurrentClinic $currentClinic): View
    {
        $clinicId = $currentClinic->id();
        $nameFilter = trim((string) $request->string('name'));
        $doctorFilter = $request->integer('doctor_id') ?: null;

        $patients = User::query()
            ->whereHas('appointments', fn ($query) => $query->where('medical_center_id', $clinicId)
                ->when($doctorFilter, fn ($query) => $query->where('doctor_id', $doctorFilter)))
            ->when($nameFilter !== '', fn ($query) => $query->where('name', 'like', "%{$nameFilter}%"))
            ->withCount(['appointments' => fn ($query) => $query->where('medical_center_id', $clinicId)
                ->when($doctorFilter, fn ($query) => $query->where('doctor_id', $doctorFilter))])
            ->with(['appointments' => fn ($query) => $query->where('medical_center_id', $clinicId)
                ->when($doctorFilter, fn ($query) => $query->where('doctor_id', $doctorFilter))
                ->with('doctor')
                ->orderByDesc('appointment_date')
                ->orderByDesc('appointment_time')
                ->limit(1)])
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('medical-center.patients.index', [
            'patients' => $patients,
            'doctorOptions' => $currentClinic->model()->affiliatedDoctors,
            'filters' => [
                'name' => $nameFilter,
                'doctor_id' => $doctorFilter,
            ],
        ]);
    }

    /**
     * Display a patient's appointment history at this clinic only.
     */
    public function show(User $patient, CurrentClinic $currentClinic): View
    {
        $clinicId = $currentClinic->id();

        abort_unless($patient->appointments()->where('medical_center_id', $clinicId)->exists(), 403);

        return view('medical-center.patients.show', [
            'patient' => $patient,
            'appointments' => $patient->appointments()
                ->where('medical_center_id', $clinicId)
                ->with(['doctor', 'prescription.items'])
                ->orderByDesc('appointment_date')
                ->orderByDesc('appointment_time')
                ->get(),
        ]);
    }
}
