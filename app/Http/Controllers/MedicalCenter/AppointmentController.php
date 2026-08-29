<?php

namespace App\Http\Controllers\MedicalCenter;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\CurrentClinic;
use App\Services\PrescriptionPdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AppointmentController extends Controller
{
    /**
     * Display every appointment booked across the clinic's doctors, with
     * optional filters by patient name and date range.
     */
    public function index(Request $request, CurrentClinic $currentClinic): View
    {
        $name = trim((string) $request->string('name'));
        $dateFrom = trim((string) $request->string('date_from'));
        $dateTo = trim((string) $request->string('date_to'));

        $appointments = $currentClinic->model()->appointments()
            ->when($name !== '', fn ($query) => $query->where('patient_name', 'like', "%{$name}%"))
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('appointment_date', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('appointment_date', '<=', $dateTo))
            ->with(['doctor', 'user'])
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->paginate(15)
            ->withQueryString();

        return view('medical-center.appoinment-managment.index', [
            'appointments' => $appointments,
            'filters' => [
                'name' => $name,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    /**
     * Display a single appointment booked at this clinic.
     */
    public function show(Appointment $appointment, CurrentClinic $currentClinic): View
    {
        abort_unless($appointment->medical_center_id === $currentClinic->id(), 403);

        return view('medical-center.appoinment-managment.show', [
            'appointment' => $appointment->load(['doctor', 'user', 'prescription.items']),
        ]);
    }

    /**
     * Cancel an appointment booked at this clinic. Clinics may only cancel —
     * marking an appointment completed remains a clinical decision reserved
     * for the treating doctor.
     */
    public function updateStatus(Request $request, Appointment $appointment, CurrentClinic $currentClinic): RedirectResponse
    {
        abort_unless($appointment->medical_center_id === $currentClinic->id(), 403);

        $request->validate([
            'status' => ['required', Rule::in(['cancelled'])],
        ]);

        $appointment->update(['status' => 'cancelled']);

        return back()->with('status', 'Appointment cancelled.');
    }

    /**
     * Download the appointment's prescription as a PDF (read-only, same
     * generation logic as the doctor-side download).
     */
    public function downloadPrescription(Appointment $appointment, CurrentClinic $currentClinic, PrescriptionPdf $prescriptionPdf): Response
    {
        abort_unless($appointment->medical_center_id === $currentClinic->id(), 403);

        return $prescriptionPdf->download($appointment);
    }
}
