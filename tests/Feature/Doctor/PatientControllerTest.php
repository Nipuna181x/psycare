<?php

namespace Tests\Feature\Doctor;

use App\Models\AiCompanionSession;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\NlpClassificationResult;
use App\Models\PatientNlpReport;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PatientControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_from_patients_index(): void
    {
        $this->get(route('doctor.patients.index'))->assertRedirect(route('doctor.login'));
    }

    public function test_doctor_only_sees_patients_they_have_treated(): void
    {
        $doctor = Doctor::factory()->create();
        $otherDoctor = Doctor::factory()->create();

        $myPatient = User::factory()->create(['name' => 'My Patient']);
        $otherPatient = User::factory()->create(['name' => 'Other Patient']);

        Appointment::factory()->for($myPatient)->for($doctor)->create();
        Appointment::factory()->for($otherPatient)->for($otherDoctor)->create();

        $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.patients.index'))
            ->assertOk()
            ->assertSee('My Patient')
            ->assertDontSee('Other Patient');
    }

    public function test_patients_index_can_be_filtered_by_name(): void
    {
        $doctor = Doctor::factory()->create();
        $amaya = User::factory()->create(['name' => 'Amaya Silva']);
        $nimal = User::factory()->create(['name' => 'Nimal Perera']);

        Appointment::factory()->for($amaya)->for($doctor)->create();
        Appointment::factory()->for($nimal)->for($doctor)->create();

        $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.patients.index', ['name' => 'Amaya']))
            ->assertOk()
            ->assertSee('Amaya Silva')
            ->assertDontSee('Nimal Perera');
    }

    public function test_patients_index_can_be_filtered_by_risk_level(): void
    {
        $doctor = Doctor::factory()->create();
        $lowRiskPatient = User::factory()->create(['name' => 'Low Risk Patient']);
        $highRiskPatient = User::factory()->create(['name' => 'High Risk Patient']);

        Appointment::factory()->for($lowRiskPatient)->for($doctor)->create();
        Appointment::factory()->for($highRiskPatient)->for($doctor)->create();

        $lowSession = AiCompanionSession::factory()->for($lowRiskPatient)->create();
        PatientNlpReport::factory()->create([
            'user_id' => $lowRiskPatient->id,
            'ai_companion_session_id' => $lowSession->id,
            'report' => [
                'summary' => 'Doing fine.',
                'presenting_concerns' => [], 'symptoms' => [], 'stressors' => [], 'protective_factors' => [], 'functional_impact' => [],
                'risk' => ['level' => 'low', 'requires_immediate_review' => false, 'evidence' => [], 'recommended_action' => 'Routine review.'],
                'clinician_follow_up_questions' => [],
            ],
        ]);

        $highSession = AiCompanionSession::factory()->for($highRiskPatient)->create();
        PatientNlpReport::factory()->create([
            'user_id' => $highRiskPatient->id,
            'ai_companion_session_id' => $highSession->id,
            'report' => [
                'summary' => 'Struggling badly.',
                'presenting_concerns' => [], 'symptoms' => [], 'stressors' => [], 'protective_factors' => [], 'functional_impact' => [],
                'risk' => ['level' => 'elevated', 'requires_immediate_review' => true, 'evidence' => [], 'recommended_action' => 'Escalate.'],
                'clinician_follow_up_questions' => [],
            ],
        ]);

        $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.patients.index', ['risk' => 'elevated']))
            ->assertOk()
            ->assertSee('High Risk Patient')
            ->assertDontSee('Low Risk Patient');
    }

    public function test_doctor_cannot_view_a_patient_they_have_not_treated(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();

        $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.patients.show', $patient))
            ->assertForbidden();
    }

    public function test_doctor_can_view_a_treated_patients_profile_with_charts(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();
        Appointment::factory()->for($patient)->for($doctor)->create();

        NlpClassificationResult::factory()->for($patient, 'patient')->create([
            'entry_date' => now()->subDays(5),
            'phq9_severity' => 'moderate',
            'gad7_severity' => 'mild',
            'symptoms' => ['insomnia', 'anxiety'],
        ]);
        NlpClassificationResult::factory()->for($patient, 'patient')->create([
            'entry_date' => now(),
            'phq9_severity' => 'mild',
            'gad7_severity' => 'minimal',
            'symptoms' => ['insomnia'],
        ]);

        $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.patients.show', $patient))
            ->assertOk()
            ->assertSee($patient->name)
            ->assertSee('severity-trend-chart', false)
            ->assertSee('symptom-frequency-chart', false);
    }

    public function test_doctor_can_download_a_patients_nlp_report_as_pdf(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();
        $appointment = Appointment::factory()->for($patient)->for($doctor)->create();
        $session = AiCompanionSession::factory()->for($patient)->create();
        $report = PatientNlpReport::factory()->create([
            'user_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'ai_companion_session_id' => $session->id,
            'report' => [
                'summary' => 'Patient reports difficulty sleeping.',
                'presenting_concerns' => [], 'symptoms' => [], 'stressors' => [], 'protective_factors' => [], 'functional_impact' => [],
                'risk' => ['level' => 'low', 'requires_immediate_review' => false, 'evidence' => [], 'recommended_action' => 'Routine review.'],
                'clinician_follow_up_questions' => [],
            ],
        ]);

        $response = $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.patients.reports.download', [$patient, $report]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_doctor_cannot_download_another_doctors_patient_report(): void
    {
        $doctor = Doctor::factory()->create();
        $otherDoctor = Doctor::factory()->create();
        $patient = User::factory()->create();
        $appointment = Appointment::factory()->for($patient)->for($otherDoctor)->create();
        $session = AiCompanionSession::factory()->for($patient)->create();
        $report = PatientNlpReport::factory()->create([
            'user_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'ai_companion_session_id' => $session->id,
        ]);

        $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.patients.reports.download', [$patient, $report]))
            ->assertForbidden();
    }

    public function test_patient_profile_groups_reports_by_day(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();
        Appointment::factory()->for($patient)->for($doctor)->create();

        $session = AiCompanionSession::factory()->for($patient)->create();
        PatientNlpReport::factory()->create([
            'user_id' => $patient->id,
            'ai_companion_session_id' => $session->id,
            'generated_at' => now()->subDay(),
            'report' => [
                'summary' => 'Yesterday summary.',
                'presenting_concerns' => [], 'symptoms' => [], 'stressors' => [], 'protective_factors' => [], 'functional_impact' => [],
                'risk' => ['level' => 'low', 'requires_immediate_review' => false, 'evidence' => [], 'recommended_action' => 'Routine review.'],
                'clinician_follow_up_questions' => [],
            ],
        ]);

        $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.patients.show', $patient))
            ->assertOk()
            ->assertSee('Day-by-day Lumi reports')
            ->assertSee('Yesterday summary.')
            ->assertSee('Download Full Report');
    }

    public function test_doctor_can_generate_missing_reports_for_ended_sessions(): void
    {
        config([
            'services.gemini.api_key' => 'test-key',
            'services.gemini.model' => 'gemini-test',
        ]);
        Http::preventStrayRequests();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => json_encode([
                'summary' => 'Generated retroactively.',
                'presenting_concerns' => [], 'symptoms' => [], 'stressors' => [], 'protective_factors' => [], 'functional_impact' => [],
                'risk' => ['level' => 'low', 'requires_immediate_review' => false, 'evidence' => [], 'recommended_action' => 'Routine review.'],
                'inconsistencies' => [], 'clinician_follow_up_questions' => [], 'limitations' => [],
            ])]]]]],
        ])]);

        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();
        Appointment::factory()->for($patient)->for($doctor)->create();

        $session = AiCompanionSession::factory()->for($patient)->create(['ended_at' => now()]);
        $session->turns()->create(['role' => 'user', 'sequence' => 1, 'content' => 'I have been anxious.']);

        $this->actingAs($doctor, 'doctor')
            ->post(route('doctor.patients.reports.generate', $patient))
            ->assertRedirect();

        $this->assertDatabaseHas('patient_nlp_reports', ['ai_companion_session_id' => $session->id]);
    }

    public function test_doctor_can_download_full_report_history_as_pdf(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();
        Appointment::factory()->for($patient)->for($doctor)->create();

        $session = AiCompanionSession::factory()->for($patient)->create();
        PatientNlpReport::factory()->create([
            'user_id' => $patient->id,
            'ai_companion_session_id' => $session->id,
            'report' => [
                'summary' => 'History entry.',
                'presenting_concerns' => [], 'symptoms' => [], 'stressors' => [], 'protective_factors' => [], 'functional_impact' => [],
                'risk' => ['level' => 'low', 'requires_immediate_review' => false, 'evidence' => [], 'recommended_action' => 'Routine review.'],
                'clinician_follow_up_questions' => [],
            ],
        ]);

        $response = $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.patients.reports.history-download', $patient));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_downloaded_pdf_embeds_a_sinhala_capable_font(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();
        $appointment = Appointment::factory()->for($patient)->for($doctor)->create();
        $session = AiCompanionSession::factory()->for($patient)->create();
        $report = PatientNlpReport::factory()->create([
            'user_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'ai_companion_session_id' => $session->id,
            'report' => [
                'summary' => 'රෝගියා වැඩ පීඩනය ගැන කතා කළේය.',
                'presenting_concerns' => [], 'symptoms' => [], 'stressors' => [], 'protective_factors' => [], 'functional_impact' => [],
                'risk' => ['level' => 'low', 'requires_immediate_review' => false, 'evidence' => [], 'recommended_action' => 'Routine review.'],
                'clinician_follow_up_questions' => [],
            ],
        ]);

        $response = $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.patients.reports.download', [$patient, $report]));

        $response->assertOk();
        $this->assertStringContainsString('NotoSansSinhala', $response->getContent());
    }

    public function test_day_by_day_entry_shows_screening_and_risk_evidence(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();
        Appointment::factory()->for($patient)->for($doctor)->create();

        $session = AiCompanionSession::factory()->for($patient)->create();
        PatientNlpReport::factory()->create([
            'user_id' => $patient->id,
            'ai_companion_session_id' => $session->id,
            'report' => [
                'summary' => 'Elevated risk noted.',
                'presenting_concerns' => [], 'symptoms' => [], 'stressors' => [], 'protective_factors' => [], 'functional_impact' => [],
                'risk' => [
                    'level' => 'urgent', 'requires_immediate_review' => true,
                    'evidence' => ['Positive self-harm flag on PHQ-9 item 9.'],
                    'recommended_action' => 'Follow the crisis workflow immediately.',
                ],
                'inconsistencies' => ['Patient denied symptoms present in screening.'],
                'limitations' => ['Session was brief; limited evidence gathered.'],
                'clinician_follow_up_questions' => [],
                'screening' => [
                    'available' => true, 'phq9_total' => 21, 'phq9_severity' => 'severe',
                    'gad7_total' => 14, 'gad7_severity' => 'moderate', 'self_harm_flag' => true,
                ],
            ],
        ]);

        $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.patients.show', $patient))
            ->assertOk()
            ->assertSee('Positive self-harm flag on PHQ-9 item 9.')
            ->assertSee('Patient denied symptoms present in screening.')
            ->assertSee('Session was brief; limited evidence gathered.')
            ->assertSee('21') // PHQ-9 total
            ->assertSee('Follow the crisis workflow immediately.')
            ->assertDontSee('Deterministic classification');
    }

    public function test_patient_profile_shows_increasing_risk_progression(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();
        Appointment::factory()->for($patient)->for($doctor)->create();

        $earlierSession = AiCompanionSession::factory()->for($patient)->create();
        PatientNlpReport::factory()->create([
            'user_id' => $patient->id,
            'ai_companion_session_id' => $earlierSession->id,
            'generated_at' => now()->subDays(3),
            'report' => [
                'summary' => 'Doing okay.',
                'presenting_concerns' => [], 'symptoms' => [], 'stressors' => [], 'protective_factors' => [], 'functional_impact' => [],
                'risk' => ['level' => 'low', 'requires_immediate_review' => false, 'evidence' => [], 'recommended_action' => 'Routine review.'],
                'clinician_follow_up_questions' => [],
            ],
        ]);

        $laterSession = AiCompanionSession::factory()->for($patient)->create();
        PatientNlpReport::factory()->create([
            'user_id' => $patient->id,
            'ai_companion_session_id' => $laterSession->id,
            'generated_at' => now(),
            'report' => [
                'summary' => 'Things have gotten worse.',
                'presenting_concerns' => [], 'symptoms' => [], 'stressors' => [], 'protective_factors' => [], 'functional_impact' => [],
                'risk' => ['level' => 'high', 'requires_immediate_review' => false, 'evidence' => [], 'recommended_action' => 'Schedule a follow-up soon.'],
                'clinician_follow_up_questions' => [],
            ],
        ]);

        $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.patients.show', $patient))
            ->assertOk()
            ->assertSee('Risk is increasing');
    }
}
