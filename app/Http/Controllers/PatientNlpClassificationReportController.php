<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPatientAccess;
use App\Models\Appointment;
use App\Models\NlpClassificationResult;
use App\Models\User;
use App\Services\PatientNlpClassifier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class PatientNlpClassificationReportController extends Controller
{
    use AuthorizesPatientAccess;

    /**
     * Ordinal ranking of risk levels, lowest to highest severity, used to derive the trend indicator.
     *
     * @var array<string, int>
     */
    private const RISK_LEVEL_ORDER = ['low' => 0, 'moderate' => 1, 'high' => 2, 'urgent' => 3];

    /**
     * Display a patient's full NLP classification history as a clinician-readable report.
     */
    public function show(User $patient): View
    {
        $guard = $this->authorizeAccess($patient);

        /** @var Collection<int, NlpClassificationResult> $results */
        $results = $patient->nlpClassificationResults()->orderBy('entry_date')->get();

        return view("{$guard}.patients.nlp-report", [
            'patient' => $patient,
            'results' => $results,
            'latest' => $results->last(),
            'trend' => $this->trend($results),
            'symptomCounts' => $results
                ->flatMap(fn (NlpClassificationResult $result): array => $result->symptoms ?? [])
                ->countBy()
                ->sortDesc(),
        ]);
    }

    /**
     * Manually classify any of the patient's ended AI companion conversations that don't yet
     * have a classification result — a fallback for when the automatic sync on session finish
     * didn't run or the classification service was unreachable at the time.
     */
    public function sync(User $patient, PatientNlpClassifier $classifier): RedirectResponse
    {
        $this->authorizeAccess($patient);

        $url = config('services.psycare_nlp.url');

        if (! is_string($url) || $url === '') {
            return back()->with('status', 'The NLP classification service is not configured.');
        }

        $sessions = $patient->aiCompanionSessions()
            ->whereNotNull('ended_at')
            ->whereDoesntHave('classificationResult')
            ->whereHas('turns', fn ($query) => $query->where('role', 'user'))
            ->get();

        if ($sessions->isEmpty()) {
            return back()->with('status', 'Nothing to sync — every conversation already has a classification result.');
        }

        $appointment = Appointment::query()->whereBelongsTo($patient)->latest('appointment_date')->first();
        $synced = 0;

        foreach ($sessions as $session) {
            try {
                $classifier->classify($session, $appointment);
                $synced++;
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        $message = $synced === $sessions->count()
            ? "Synced {$synced} conversation(s)."
            : "Synced {$synced} of {$sessions->count()} conversation(s) — check the logs for the rest.";

        return back()->with('status', $message);
    }

    /**
     * Compare the earliest and latest risk levels in the history to derive a trend label.
     *
     * @param  Collection<int, NlpClassificationResult>  $results
     */
    private function trend(Collection $results): ?string
    {
        $earliest = $results->first();
        $latest = $results->last();

        if (! $earliest || ! $latest || $earliest->is($latest)) {
            return null;
        }

        $earlyRank = self::RISK_LEVEL_ORDER[$earliest->risk_level] ?? null;
        $lateRank = self::RISK_LEVEL_ORDER[$latest->risk_level] ?? null;

        if ($earlyRank === null || $lateRank === null) {
            return null;
        }

        return match (true) {
            $lateRank < $earlyRank => 'improving',
            $lateRank > $earlyRank => 'worsening',
            default => 'stable',
        };
    }
}
