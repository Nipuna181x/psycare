<?php

namespace Tests\Unit\Unit;

use App\Models\AiCompanionSession;
use App\Models\Appointment;
use App\Services\PatientNlpReportGenerator;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PatientNlpReportGeneratorTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    use LazilyRefreshDatabase;

    public function test_it_generates_structured_report_and_preserves_deterministic_screening_risk(): void
    {
        config(['services.gemini.api_key' => 'test-key', 'services.gemini.model' => 'gemini-test']);
        Http::preventStrayRequests();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => json_encode($this->geminiReport())]]]]],
        ])]);
        $session = AiCompanionSession::factory()->create();
        $session->turns()->create(['role' => 'user', 'sequence' => 1, 'content' => 'I feel hopeless most days.']);
        $appointment = Appointment::factory()->for($session->user)->create([
            'phq9_total' => 18,
            'phq9_severity' => 'moderately_severe',
            'gad7_total' => 11,
            'gad7_severity' => 'moderate',
            'self_harm_flag' => true,
            'requires_immediate_escalation' => true,
            'screener_completed_at' => now(),
        ]);

        $report = (new PatientNlpReportGenerator)->generate($session, $appointment);

        $this->assertSame(18, $report['screening']['phq9_total']);
        $this->assertSame('urgent', $report['risk']['level']);
        $this->assertTrue($report['risk']['requires_immediate_review']);
        Http::assertSent(fn ($request): bool => $request['generationConfig']['responseMimeType'] === 'application/json'
            && $request['generationConfig']['responseJsonSchema']['type'] === 'object');
    }

    public function test_it_instructs_the_model_to_write_the_report_in_english_only(): void
    {
        config(['services.gemini.api_key' => 'test-key', 'services.gemini.model' => 'gemini-test']);
        Http::preventStrayRequests();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => json_encode($this->geminiReport())]]]]],
        ])]);
        $session = AiCompanionSession::factory()->create(['language' => 'si']);
        $session->turns()->create(['role' => 'user', 'sequence' => 1, 'content' => 'මට හොඳ නෑ.']);

        (new PatientNlpReportGenerator)->generate($session, null);

        Http::assertSent(function ($request): bool {
            $instructions = $request['systemInstruction']['parts'][0]['text'];

            return str_contains($instructions, 'Write the entire report in English')
                && str_contains($instructions, 'Never quote or reproduce the patient\'s original wording verbatim')
                && str_contains($instructions, 'never include non-English text');
        });
    }

    /** @return array<string, mixed> */
    private function geminiReport(): array
    {
        return [
            'summary' => 'Patient reports persistent hopelessness.',
            'presenting_concerns' => [], 'symptoms' => [], 'stressors' => [], 'protective_factors' => [], 'functional_impact' => [],
            'risk' => ['level' => 'low', 'requires_immediate_review' => false, 'evidence' => [], 'recommended_action' => 'Routine review.'],
            'inconsistencies' => [], 'clinician_follow_up_questions' => ['Clarify current safety.'], 'limitations' => [],
        ];
    }
}
