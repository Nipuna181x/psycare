<?php

namespace App\Http\Controllers;

use App\Models\NlpClassificationResult;
use App\Models\PatientNlpReport;
use App\Services\PatientRiskInsights;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HealthRecordsController extends Controller
{
    /**
     * Display the authenticated patient's own full health record: appointment
     * history, prescriptions, and Lumi AI companion report insights.
     */
    public function index(PatientRiskInsights $riskInsights): View
    {
        $patient = Auth::user();

        /** @var Collection<int, NlpClassificationResult> $classifications */
        $classifications = $patient->nlpClassificationResults()->orderBy('entry_date')->get();

        $reports = $patient->patientNlpReports()->oldest('generated_at')->get();

        return view('patient.health-records.index', [
            'patient' => $patient,
            'appointments' => $patient->appointments()
                ->with(['doctor', 'medicalCenter', 'prescription.items'])
                ->orderByDesc('appointment_date')
                ->get(),
            'reportsByDay' => $reports->groupBy(fn (PatientNlpReport $report): string => $report->generated_at->toDateString())->sortKeysDesc(),
            'riskProgression' => $riskInsights->riskProgression($reports),
            'latestReport' => $reports->last(),
            'chartData' => $riskInsights->chartData($classifications),
        ]);
    }
}
