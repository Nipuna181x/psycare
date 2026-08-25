<?php

namespace App\Services;

use App\Models\AiCompanionSession;
use App\Models\Appointment;
use App\Models\NlpClassificationResult;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PatientNlpClassifier
{
    /** @var array<int, string> */
    private const RISK_LEVELS = ['low', 'moderate', 'high', 'urgent'];

    /**
     * Call the PsyCare NLP classification service and mirror the result into nlp_classification_results.
     */
    public function classify(AiCompanionSession $session, ?Appointment $appointment): NlpClassificationResult
    {
        $url = config('services.psycare_nlp.url');
        $timeout = (int) config('services.psycare_nlp.timeout', 30);

        if (! is_string($url) || $url === '') {
            throw new RuntimeException('The patient NLP classification service is not configured.');
        }

        $response = Http::acceptJson()
            ->connectTimeout(5)->timeout($timeout)->retry([500, 1500])
            ->post(rtrim($url, '/').'/classify', [
                'conversation_text' => $this->transcript($session),
                'patient_id' => (string) $session->user_id,
                'entry_date' => now()->toDateString(),
                'store_for_trend' => false,
            ])->throw();

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('The patient NLP classification service returned an invalid response.');
        }

        return NlpClassificationResult::query()->updateOrCreate(
            ['ai_companion_session_id' => $session->id],
            [
                'patient_id' => $session->user_id,
                'entry_date' => now()->toDateString(),
                ...$this->normalize($payload, $appointment),
            ],
        );
    }

    /**
     * Render the conversation as a plain "Patient: ... / Lumi: ..." transcript,
     * which is the format the classification service expects.
     */
    private function transcript(AiCompanionSession $session): string
    {
        return $session->turns()->get(['role', 'content'])
            ->map(fn ($turn): string => ($turn->role === 'user' ? 'Patient' : 'Lumi').': '.$turn->content)
            ->implode("\n");
    }

    /**
     * Normalize the classifier's response, defaulting to the deterministic screener where the
     * classifier is silent, and never allowing the classifier to soften a deterministic self-harm signal.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalize(array $payload, ?Appointment $appointment): array
    {
        $riskLevel = $payload['risk_level'] ?? null;
        $riskLevel = is_string($riskLevel) && in_array($riskLevel, self::RISK_LEVELS, true) ? $riskLevel : 'moderate';

        $selfHarmFlag = (bool) ($payload['self_harm_flag'] ?? false) || (bool) $appointment?->self_harm_flag;

        if ($selfHarmFlag) {
            $riskLevel = 'urgent';
        }

        $symptoms = is_array($payload['symptoms'] ?? null)
            ? array_values(array_filter($payload['symptoms'], 'is_string'))
            : [];

        return [
            'risk_level' => $riskLevel,
            'self_harm_flag' => $selfHarmFlag,
            'self_harm_confidence' => is_numeric($payload['self_harm_confidence'] ?? null) ? (float) $payload['self_harm_confidence'] : null,
            'phq9_severity' => is_string($payload['phq9_severity'] ?? null) ? $payload['phq9_severity'] : $appointment?->phq9_severity,
            'gad7_severity' => is_string($payload['gad7_severity'] ?? null) ? $payload['gad7_severity'] : $appointment?->gad7_severity,
            'symptoms' => $symptoms,
            'symptom_scores' => is_array($payload['symptom_scores'] ?? null) ? $payload['symptom_scores'] : [],
        ];
    }
}
