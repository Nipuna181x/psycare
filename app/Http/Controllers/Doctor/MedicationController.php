<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StoreMedicationRequest;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;

class MedicationController extends Controller
{
    public function store(StoreMedicationRequest $request, Appointment $appointment): RedirectResponse
    {
        $appointment->prescriptions()->create([
            ...$request->validated(),
            'patient_id' => $appointment->user_id,
            'doctor_id' => $appointment->doctor_id,
        ]);

        return back()->with('status', 'Medication added to this appointment.');
    }
}
