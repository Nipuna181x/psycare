<?php

namespace Tests\Feature\Feature;

use App\Models\AiCompanionSession;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\PatientNlpReport;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PatientNlpReportTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use LazilyRefreshDatabase;

    public function test_patient_can_finish_owned_session_and_generate_encrypted_report(): void
    {
        config(['services.gemini.api_key' => 'test-key', 'services.gemini.model' => 'gemini-test']);
        Http::preventStrayRequests();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => json_encode([
                'summary' => 'Work stress is affecting rest.',
                'presenting_concerns' => [], 'symptoms' => [], 'stressors' => [], 'protective_factors' => [], 'functional_impact' => [],
                'risk' => ['level' => 'low', 'requires_immediate_review' => false, 'evidence' => [], 'recommended_action' => 'Routine review.'],
                'inconsistencies' => [], 'clinician_follow_up_questions' => [], 'limitations' => [],
            ])]]]]],
        ])]);
        $patient = User::factory()->create();
        $session = AiCompanionSession::factory()->for($patient)->create();
        $session->turns()->create(['role' => 'user', 'sequence' => 1, 'content' => 'Work follows me home.']);

        $this->actingAs($patient)->postJson(route('ai-companion.finish'), ['session_id' => $session->public_id])
            ->assertOk()->assertJson(['status' => 'generated']);

        $report = PatientNlpReport::query()->firstOrFail();
        $this->assertSame('Work stress is affecting rest.', $report->report['summary']);
        $this->assertNotSame(json_encode($report->report), $report->getRawOriginal('report'));
        $this->assertNotNull($session->fresh()->ended_at);
    }

    public function test_patient_cannot_finish_another_patients_session(): void
    {
        $owner = User::factory()->create();
        $session = AiCompanionSession::factory()->for($owner)->create();

        $this->actingAs(User::factory()->create())
            ->postJson(route('ai-companion.finish'), ['session_id' => $session->public_id])
            ->assertNotFound();
    }

    public function test_appointment_page_no_longer_shows_the_lumi_report_panel(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->for($patient)->for($doctor)->create();
        $session = AiCompanionSession::factory()->for($patient)->create();
        PatientNlpReport::factory()->create([
            'user_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'ai_companion_session_id' => $session->id,
            'report' => [
                'summary' => 'Work stress is affecting sleep.',
                'presenting_concerns' => [], 'symptoms' => [], 'stressors' => [], 'protective_factors' => [], 'functional_impact' => [],
                'risk' => ['level' => 'low', 'requires_immediate_review' => false, 'evidence' => [], 'recommended_action' => 'Routine review.'],
                'clinician_follow_up_questions' => [],
            ],
        ]);

        $this->actingAs($doctor, 'doctor')->get(route('doctor.appointments.show', $appointment))
            ->assertOk()
            ->assertDontSee('Lumi conversation report')
            ->assertSee('Patient profile');
    }

    public function test_owning_doctor_can_view_generated_report_on_patient_profile(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->for($patient)->for($doctor)->create();
        $session = AiCompanionSession::factory()->for($patient)->create();
        PatientNlpReport::factory()->create([
            'user_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'ai_companion_session_id' => $session->id,
            'report' => [
                'summary' => 'Work stress is affecting sleep.',
                'presenting_concerns' => [], 'symptoms' => [], 'stressors' => [], 'protective_factors' => [], 'functional_impact' => [],
                'risk' => ['level' => 'low', 'requires_immediate_review' => false, 'evidence' => [], 'recommended_action' => 'Routine review.'],
                'clinician_follow_up_questions' => [],
            ],
        ]);

        $this->actingAs($doctor, 'doctor')->get(route('doctor.patients.show', $patient))
            ->assertOk()
            ->assertSee('Day-by-day Lumi reports')
            ->assertSee('Work stress is affecting sleep.')
            ->assertSee('not a diagnosis');
    }
}
