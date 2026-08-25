<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AiCompanionSession;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\NlpClassificationResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientConversationHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_treating_the_patient_can_see_conversations_grouped_by_day(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();
        Appointment::factory()->for($patient)->for($doctor)->create(['medical_center_id' => $doctor->medical_center_id]);

        $session = AiCompanionSession::factory()->for($patient)->create(['created_at' => now()->subDay(), 'ended_at' => now()->subDay()]);
        $session->turns()->create(['role' => 'user', 'sequence' => 1, 'content' => 'I feel anxious.']);
        NlpClassificationResult::factory()->for($patient, 'patient')->create(['ai_companion_session_id' => $session->id, 'risk_level' => 'moderate']);

        $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.patients.conversations.index', $patient))
            ->assertOk()
            ->assertSee('Conversation history')
            ->assertSee('moderate');
    }

    public function test_doctor_without_an_appointment_cannot_view_conversations(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();

        $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.patients.conversations.index', $patient))
            ->assertStatus(403);
    }

    public function test_transcript_shows_the_risk_level_assessed_for_that_conversation(): void
    {
        $admin = Admin::factory()->create();
        $patient = User::factory()->create();
        $session = AiCompanionSession::factory()->for($patient)->create(['ended_at' => now()]);
        $session->turns()->create(['role' => 'user', 'sequence' => 1, 'content' => 'I have been struggling to sleep.']);
        $session->turns()->create(['role' => 'model', 'sequence' => 2, 'content' => 'Tell me more about that.']);
        NlpClassificationResult::factory()->for($patient, 'patient')->create([
            'ai_companion_session_id' => $session->id,
            'risk_level' => 'high',
            'self_harm_flag' => false,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.patients.conversations.show', [$patient, $session]))
            ->assertOk()
            ->assertSee('Risk at this moment')
            ->assertSee('high')
            ->assertSee('I have been struggling to sleep.')
            ->assertSee('Tell me more about that.');
    }

    public function test_transcript_shows_self_harm_banner_when_that_conversation_was_flagged(): void
    {
        $admin = Admin::factory()->create();
        $patient = User::factory()->create();
        $session = AiCompanionSession::factory()->for($patient)->create(['ended_at' => now()]);
        $session->turns()->create(['role' => 'user', 'sequence' => 1, 'content' => 'Everything feels hopeless.']);
        NlpClassificationResult::factory()->for($patient, 'patient')->selfHarmFlagged()->create(['ai_companion_session_id' => $session->id]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.patients.conversations.show', [$patient, $session]))
            ->assertOk()
            ->assertSee('Self-harm signal in this conversation');
    }

    public function test_cannot_view_another_patients_conversation(): void
    {
        $admin = Admin::factory()->create();
        $patient = User::factory()->create();
        $otherPatient = User::factory()->create();
        $session = AiCompanionSession::factory()->for($otherPatient)->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.patients.conversations.show', [$patient, $session]))
            ->assertNotFound();
    }
}
