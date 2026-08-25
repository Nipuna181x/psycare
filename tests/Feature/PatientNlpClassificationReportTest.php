<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AiCompanionSession;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\NlpClassificationResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PatientNlpClassificationReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $patient = User::factory()->create();

        $this->get(route('doctor.patients.nlp-report.show', $patient))
            ->assertRedirect(route('doctor.login'));
    }

    public function test_doctor_treating_the_patient_can_view_the_report(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();
        Appointment::factory()->for($patient)->for($doctor)->create(['medical_center_id' => $doctor->medical_center_id]);
        NlpClassificationResult::factory()->for($patient, 'patient')->create(['risk_level' => 'low']);

        $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.patients.nlp-report.show', $patient))
            ->assertOk()
            ->assertSee('NLP classification report');
    }

    public function test_doctor_without_an_appointment_cannot_view_the_report(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();
        NlpClassificationResult::factory()->for($patient, 'patient')->create();

        $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.patients.nlp-report.show', $patient))
            ->assertStatus(403);
    }

    public function test_admin_can_view_any_patients_report(): void
    {
        $admin = Admin::factory()->create();
        $patient = User::factory()->create();
        NlpClassificationResult::factory()->for($patient, 'patient')->create(['risk_level' => 'moderate']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.patients.nlp-report.show', $patient))
            ->assertOk()
            ->assertSee('NLP classification report');
    }

    public function test_self_harm_banner_shows_whenever_any_entry_is_flagged(): void
    {
        $admin = Admin::factory()->create();
        $patient = User::factory()->create();
        NlpClassificationResult::factory()->for($patient, 'patient')->create(['entry_date' => now()->subDays(10), 'self_harm_flag' => false]);
        NlpClassificationResult::factory()->for($patient, 'patient')->selfHarmFlagged()->create(['entry_date' => now()->subDays(3)]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.patients.nlp-report.show', $patient))
            ->assertOk()
            ->assertSee('Self-harm signal detected in patient history')
            ->assertSee('screening signal only, not a standalone safety determination', false);
    }

    public function test_self_harm_banner_is_hidden_when_no_entry_is_flagged(): void
    {
        $admin = Admin::factory()->create();
        $patient = User::factory()->create();
        NlpClassificationResult::factory()->for($patient, 'patient')->create(['self_harm_flag' => false]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.patients.nlp-report.show', $patient))
            ->assertOk()
            ->assertDontSee('Self-harm signal detected');
    }

    public function test_trend_reflects_earliest_versus_latest_risk_level(): void
    {
        $admin = Admin::factory()->create();
        $patient = User::factory()->create();
        NlpClassificationResult::factory()->for($patient, 'patient')->create(['entry_date' => now()->subDays(30), 'risk_level' => 'high']);
        NlpClassificationResult::factory()->for($patient, 'patient')->create(['entry_date' => now()->subDays(1), 'risk_level' => 'low']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.patients.nlp-report.show', $patient))
            ->assertOk()
            ->assertSee('Improving');
    }

    public function test_symptom_counts_are_aggregated_across_history(): void
    {
        $admin = Admin::factory()->create();
        $patient = User::factory()->create();
        NlpClassificationResult::factory()->for($patient, 'patient')->create(['symptoms' => ['insomnia', 'fatigue']]);
        NlpClassificationResult::factory()->for($patient, 'patient')->create(['symptoms' => ['insomnia']]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.patients.nlp-report.show', $patient))
            ->assertOk()
            ->assertSeeInOrder(['Insomnia', '2']);
    }

    public function test_sync_classifies_ended_sessions_that_are_missing_a_result(): void
    {
        config(['services.psycare_nlp.url' => 'http://127.0.0.1:8080']);
        Http::preventStrayRequests();
        Http::fake(['127.0.0.1:8080/classify' => Http::response(['risk_level' => 'moderate', 'self_harm_flag' => false])]);

        $admin = Admin::factory()->create();
        $patient = User::factory()->create();
        $session = AiCompanionSession::factory()->for($patient)->create(['ended_at' => now()]);
        $session->turns()->create(['role' => 'user', 'sequence' => 1, 'content' => 'I have been anxious.']);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.patients.nlp-report.sync', $patient))
            ->assertRedirect()
            ->assertSessionHas('status', 'Synced 1 conversation(s).');

        $this->assertSame(1, NlpClassificationResult::query()->where('patient_id', $patient->id)->count());
    }

    public function test_sync_skips_sessions_that_already_have_a_result(): void
    {
        config(['services.psycare_nlp.url' => 'http://127.0.0.1:8080']);
        Http::preventStrayRequests();

        $admin = Admin::factory()->create();
        $patient = User::factory()->create();
        $session = AiCompanionSession::factory()->for($patient)->create(['ended_at' => now()]);
        $session->turns()->create(['role' => 'user', 'sequence' => 1, 'content' => 'Doing better this week.']);
        NlpClassificationResult::factory()->for($patient, 'patient')->create(['ai_companion_session_id' => $session->id]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.patients.nlp-report.sync', $patient))
            ->assertSessionHas('status', 'Nothing to sync — every conversation already has a classification result.');

        Http::assertNothingSent();
    }

    public function test_doctor_without_an_appointment_cannot_sync_the_report(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();

        $this->actingAs($doctor, 'doctor')
            ->post(route('doctor.patients.nlp-report.sync', $patient))
            ->assertStatus(403);
    }
}
