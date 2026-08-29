<?php

namespace App\Services;

use App\Models\Appointment;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class PrescriptionPdf
{
    /**
     * Render an appointment's prescription as a downloadable PDF.
     */
    public function download(Appointment $appointment): Response
    {
        abort_unless($appointment->prescription, 404);

        PdfFontRegistrar::ensureSinhalaFontIsRegistered();

        $appointment->load(['prescription.items', 'prescription.doctor', 'prescription.clinic', 'medicalCenter', 'doctor']);

        $filename = 'prescription-'.$appointment->id.'-'.now()->format('Ymd-His').'.pdf';

        return Pdf::loadView('doctor.appointments.prescription-pdf', [
            'appointment' => $appointment,
            'prescription' => $appointment->prescription,
        ])->download($filename);
    }
}
