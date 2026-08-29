<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StorePrescriptionRequest;
use App\Models\Appointment;
use App\Services\DoctorClinicContext;
use App\Services\PrescriptionPdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PrescriptionController extends Controller
{
    /**
     * Create or replace the prescription for an appointment, along with all its items.
     */
    public function store(StorePrescriptionRequest $request, Appointment $appointment, DoctorClinicContext $clinicContext): RedirectResponse
    {
        $this->authorizeDoctorOwnsAppointment($appointment, $clinicContext);

        $validated = $request->validated();

        $prescription = $appointment->prescription()->updateOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'doctor_id' => $appointment->doctor_id,
                'patient_id' => $appointment->user_id,
                'clinic_id' => $appointment->medical_center_id,
                'notes' => $validated['notes'] ?? null,
                'issued_at' => now(),
            ],
        );

        $prescription->items()->delete();
        $prescription->items()->createMany(
            collect($validated['items'])->map(fn (array $item): array => [
                'medicine_name' => $item['medicine_name'],
                'dosage' => $item['dosage'],
                'frequency' => $item['frequency'],
                'duration' => $item['duration'] ?? null,
                'special_instructions' => $item['special_instructions'] ?? null,
            ])->all()
        );

        return back()->with('status', 'Prescription saved.');
    }

    /**
     * Download the appointment's prescription as a PDF.
     */
    public function download(Appointment $appointment, DoctorClinicContext $clinicContext, PrescriptionPdf $prescriptionPdf): Response
    {
        $this->authorizeDoctorOwnsAppointment($appointment, $clinicContext);

        return $prescriptionPdf->download($appointment);
    }

    private function authorizeDoctorOwnsAppointment(Appointment $appointment, DoctorClinicContext $clinicContext): void
    {
        $doctor = Auth::guard('doctor')->user();

        abort_unless($appointment->doctor_id === $doctor->id, 403);

        $clinicId = $clinicContext->current($doctor);
        abort_if($clinicId && $appointment->medical_center_id !== $clinicId, 403);
    }
}
