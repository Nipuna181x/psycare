<?php

namespace Tests\Unit\Unit;

use App\Models\AiCompanionSession;
use App\Models\Appointment;
use App\Models\NlpClassificationResult;
use App\Services\PatientNlpClassifier;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PatientNlpClassifierTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_stores_a_classification_result_from_the_service_response(): void
    {
        config(['services.psycare_nlp.url' => 'http://127.0.0.1:8080']);
        Http::preventStrayRequests();
        Http::fake(['127.0.0.1:8080/classify' => Http::response([
            'risk_level' => 'moderate',
            'self_harm_flag' => false,
            'self_harm_confidence' => 0.12,
            'phq9_severity' => 'moderate',
            'gad7_severity' => 'mild',
            'symptoms' => ['insomnia', 'fatigue'],
            'symptom_scores' => ['insomnia' => 0.8],
        ])]);

        $session = AiCompanionSession::factory()->create();
        $session->turns()->create(['role' => 'user', 'sequence' => 1, 'content' => 'I have not slept well.']);

        $result = (new PatientNlpClassifier)->classify($session, null);

        $this->assertSame($session->user_id, $result->patient_id);
        $this->assertSame($session->id, $result->ai_companion_session_id);
        $this->assertSame('moderate', $result->risk_level);
        $this->assertFalse($result->self_harm_flag);
        $this->assertSame(['insomnia', 'fatigue'], $result->symptoms);
        Http::assertSent(fn ($request): bool => $request['patient_id'] === (string) $session->user_id
            && $request['conversation_text'] === 'Patient: I have not slept well.');
    }

    public function test_a_deterministic_self_harm_flag_always_forces_urgent_risk(): void
    {
        config(['services.psycare_nlp.url' => 'http://127.0.0.1:8080']);
        Http::preventStrayRequests();
        Http::fake(['127.0.0.1:8080/classify' => Http::response([
            'risk_level' => 'low',
            'self_harm_flag' => false,
        ])]);

        $session = AiCompanionSession::factory()->create();
        $session->turns()->create(['role' => 'user', 'sequence' => 1, 'content' => 'Everything is fine.']);
        $appointment = Appointment::factory()->for($session->user)->create(['self_harm_flag' => true]);

        $result = (new PatientNlpClassifier)->classify($session, $appointment);

        $this->assertTrue($result->self_harm_flag);
        $this->assertSame('urgent', $result->risk_level);
    }

    public function test_repeated_classification_of_the_same_session_updates_the_existing_row(): void
    {
        config(['services.psycare_nlp.url' => 'http://127.0.0.1:8080']);
        Http::preventStrayRequests();
        Http::fakeSequence('127.0.0.1:8080/classify')
            ->push(['risk_level' => 'low', 'self_harm_flag' => false])
            ->push(['risk_level' => 'high', 'self_harm_flag' => false]);

        $session = AiCompanionSession::factory()->create();
        $session->turns()->create(['role' => 'user', 'sequence' => 1, 'content' => 'Checking in.']);
        $classifier = new PatientNlpClassifier;

        $first = $classifier->classify($session, null);
        $second = $classifier->classify($session, null);

        $this->assertSame($first->id, $second->id);
        $this->assertSame('high', $second->fresh()->risk_level);
        $this->assertSame(1, NlpClassificationResult::query()->count());
    }
}
