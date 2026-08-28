<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\NlpClassificationResult;
use App\Models\PatientNlpReport;
use App\Models\User;
use App\Services\DoctorClinicContext;
use App\Services\PatientHistoryVisibility;
use App\Services\PatientNlpReportGenerator;
use App\Services\PatientRiskInsights;
use App\Services\PdfFontRegistrar;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PatientController extends Controller
{
    public function __construct(private readonly PatientRiskInsights $riskInsights) {}

    /**
     * List every patient the authenticated doctor has an appointment history with.
     */
    public function index(DoctorClinicContext $clinicContext): View
    {
        $doctor = Auth::guard('doctor')->user();
        $clinicId = $clinicContext->current($doctor);

        $patients = User::query()
            ->whereHas('appointments', fn ($query) => $query->where('doctor_id', $doctor->id)->when($clinicId, fn ($query) => $query->where('medical_center_id', $clinicId)))
            ->withCount(['appointments' => fn ($query) => $query->where('doctor_id', $doctor->id)->when($clinicId, fn ($query) => $query->where('medical_center_id', $clinicId))])
            ->with(['patientNlpReports' => fn ($query) => $query->latest('generated_at')->limit(1)])
            ->orderBy('name')
            ->get();

        return view('doctor.patients.index', [
            'patients' => $patients,
        ]);
    }

    /**
     * Display a single patient's profile: appointment history, a day-by-day Lumi report
     * timeline, and whether their risk level is trending up or down.
     */
    public function show(User $patient, DoctorClinicContext $clinicContext, PatientHistoryVisibility $visibility): View
    {
        $this->authorizeDoctorTreatsPatient($patient, $clinicContext);

        $doctor = Auth::guard('doctor')->user();
        $clinicId = $clinicContext->current($doctor);

        /** @var Collection<int, NlpClassificationResult> $classifications */
        $classifications = $patient->nlpClassificationResults()->orderBy('entry_date')->get();

        $reports = $patient->patientNlpReports()->oldest('generated_at')->get();

        $pendingSessions = $patient->aiCompanionSessions()
            ->whereNotNull('ended_at')
            ->whereDoesntHave('report')
            ->whereHas('turns', fn ($query) => $query->where('role', 'user'))
            ->count();

        return view('doctor.patients.show', [
            'patient' => $patient,
            'appointments' => $patient->appointments()
                ->where('doctor_id', $doctor->id)
                ->when($clinicId, fn ($query) => $query->where('medical_center_id', $clinicId))
                ->with(['prescription.items', 'prescription.doctor', 'prescription.clinic'])
                ->orderByDesc('appointment_date')
                ->get(),
            'otherProvidersHistory' => $visibility->otherProvidersHistoryFor($patient, $doctor),
            'reportsByDay' => $reports->groupBy(fn (PatientNlpReport $report): string => $report->generated_at->toDateString())->sortKeysDesc(),
            'riskProgression' => $this->riskInsights->riskProgression($reports),
            'latestReport' => $reports->last(),
            'pendingSessions' => $pendingSessions,
            'chartData' => $this->riskInsights->chartData($classifications),
        ]);
    }

    /**
     * Manually generate Lumi reports for any of the patient's ended conversations that don't
     * yet have one — a fallback for when generation failed or was interrupted when the
     * conversation ended.
     */
    public function generateReports(User $patient, PatientNlpReportGenerator $generator, DoctorClinicContext $clinicContext): RedirectResponse
    {
        $this->authorizeDoctorTreatsPatient($patient, $clinicContext);

        $sessions = $patient->aiCompanionSessions()
            ->whereNotNull('ended_at')
            ->whereDoesntHave('report')
            ->whereHas('turns', fn ($query) => $query->where('role', 'user'))
            ->get();

        if ($sessions->isEmpty()) {
            return back()->with('status', 'Nothing to generate — every conversation already has a report.');
        }

        $appointment = Appointment::query()->whereBelongsTo($patient)->latest('appointment_date')->first();
        $generated = 0;

        foreach ($sessions as $session) {
            try {
                $reportData = $generator->generate($session, $appointment);
                PatientNlpReport::query()->updateOrCreate(
                    ['ai_companion_session_id' => $session->id],
                    [
                        'user_id' => $patient->id,
                        'appointment_id' => $appointment?->id,
                        'report' => $reportData,
                        'generated_at' => now(),
                        'status' => 'generated',
                    ],
                );
                $generated++;
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        $message = $generated === $sessions->count()
            ? "Generated {$generated} report(s)."
            : "Generated {$generated} of {$sessions->count()} report(s) — check the logs for the rest.";

        return back()->with('status', $message);
    }

    /**
     * Download a single AI companion NLP report as a PDF.
     */
    public function downloadReport(User $patient, PatientNlpReport $report, DoctorClinicContext $clinicContext): Response
    {
        $this->authorizeDoctorTreatsPatient($patient, $clinicContext);
        abort_unless($report->user_id === $patient->id, 404);

        PdfFontRegistrar::ensureSinhalaFontIsRegistered();

        $report->load(['appointment.doctor', 'appointment.medicalCenter', 'session']);

        $filename = 'lumi-report-'.$patient->id.'-'.$report->generated_at->format('Ymd-His').'.pdf';

        return Pdf::loadView('doctor.patients.nlp-report-pdf', [
            'patient' => $patient,
            'report' => $report,
        ])->download($filename);
    }

    /**
     * Download the patient's full day-by-day Lumi report as a single PDF, showing the
     * risk progression across every recorded conversation.
     */
    public function downloadHistory(User $patient, DoctorClinicContext $clinicContext): Response
    {
        $this->authorizeDoctorTreatsPatient($patient, $clinicContext);

        PdfFontRegistrar::ensureSinhalaFontIsRegistered();

        $reports = $patient->patientNlpReports()->with('session')->oldest('generated_at')->get();
        $reportsByDay = $reports->groupBy(fn (PatientNlpReport $report): string => $report->generated_at->toDateString())->sortKeysDesc();

        $filename = 'lumi-full-report-'.$patient->id.'-'.now()->format('Ymd-His').'.pdf';

        return Pdf::loadView('doctor.patients.nlp-history-pdf', [
            'patient' => $patient,
            'doctor' => Auth::guard('doctor')->user(),
            'reportsByDay' => $reportsByDay,
            'riskProgression' => $this->riskInsights->riskProgression($reports),
        ])->download($filename);
    }

    private function authorizeDoctorTreatsPatient(User $patient, DoctorClinicContext $clinicContext): void
    {
        $doctor = Auth::guard('doctor')->user();
        $clinicId = $clinicContext->current($doctor);

        abort_unless(
            $doctor->appointments()
                ->where('user_id', $patient->id)
                ->when($clinicId, fn ($query) => $query->where('medical_center_id', $clinicId))
                ->exists(),
            403
        );
    }
}
