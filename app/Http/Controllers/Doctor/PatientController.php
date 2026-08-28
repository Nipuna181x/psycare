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
    /**
     * Ordinal ranking of risk levels, lowest to highest severity, used to derive the
     * day-by-day risk progression.
     *
     * @var array<string, int>
     */
    private const RISK_LEVEL_ORDER = ['low' => 0, 'minimal' => 0, 'moderate' => 1, 'mild' => 1, 'high' => 2, 'urgent' => 3, 'severe' => 3];

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
            'riskProgression' => $this->riskProgression($reports),
            'latestReport' => $reports->last(),
            'pendingSessions' => $pendingSessions,
            'chartData' => $this->chartData($classifications),
        ]);
    }

    /**
     * Compare each day's risk level against the one before it to describe whether the
     * patient's risk is trending up, down, or holding steady over time.
     *
     * @param  Collection<int, PatientNlpReport>  $reports  ordered oldest first
     * @return array{trend: string|null, points: array<int, array{date: string, level: string, rank: int|null}>}
     */
    private function riskProgression(Collection $reports): array
    {
        $points = $reports->map(fn (PatientNlpReport $report): array => [
            'date' => $report->generated_at->format('j M Y'),
            'level' => $report->report['risk']['level'] ?? 'unknown',
            'rank' => self::RISK_LEVEL_ORDER[$report->report['risk']['level'] ?? ''] ?? null,
        ])->values()->all();

        $ranked = collect($points)->filter(fn (array $point): bool => $point['rank'] !== null);
        $earliest = $ranked->first();
        $latest = $ranked->last();

        $trend = null;
        if ($earliest && $latest && $earliest !== $latest) {
            $trend = match (true) {
                $latest['rank'] > $earliest['rank'] => 'increasing',
                $latest['rank'] < $earliest['rank'] => 'decreasing',
                default => 'stable',
            };
        }

        return ['trend' => $trend, 'points' => $points];
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
            'riskProgression' => $this->riskProgression($reports),
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

    /**
     * Shape the classification history into arrays Chart.js can consume directly.
     *
     * @param  Collection<int, NlpClassificationResult>  $classifications
     * @return array<string, mixed>
     */
    private function chartData(Collection $classifications): array
    {
        $severityScore = fn (?string $severity): ?int => match ($severity) {
            'minimal' => 0, 'mild' => 1, 'moderate' => 2, 'moderately_severe' => 3, 'severe' => 4,
            default => null,
        };

        return [
            'labels' => $classifications->map(fn (NlpClassificationResult $result): string => $result->entry_date->format('j M Y'))->values()->all(),
            'phq9' => $classifications->map(fn (NlpClassificationResult $result): ?int => $severityScore($result->phq9_severity))->values()->all(),
            'gad7' => $classifications->map(fn (NlpClassificationResult $result): ?int => $severityScore($result->gad7_severity))->values()->all(),
            'symptomCounts' => $classifications
                ->flatMap(fn (NlpClassificationResult $result): array => $result->symptoms ?? [])
                ->countBy()
                ->sortDesc()
                ->take(8)
                ->all(),
        ];
    }
}
