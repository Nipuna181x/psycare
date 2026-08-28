<?php

namespace Tests\Feature\Doctor;

use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrisisQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_renders_reassuring_empty_state(): void
    {
        $doctor = Doctor::factory()->create();

        $this->actingAs($doctor, 'doctor')->get(route('doctor.crisis-queue.index'))
            ->assertOk()->assertSee('No urgent reviews right now.');
    }

    public function test_queue_shows_only_latest_flagged_assessment_per_patient(): void
    {
        $flagged = Appointment::factory()->create([
            'screener_completed_at' => now(), 'requires_immediate_escalation' => true,
            'self_harm_flag' => false, 'pre_assessment_risk_level' => 'elevated', 'phq9_total' => 20, 'gad7_total' => 16,
        ]);
        Appointment::factory()->create([
            'doctor_id' => $flagged->doctor_id, 'medical_center_id' => $flagged->medical_center_id,
            'user_id' => $flagged->user_id, 'patient_name' => $flagged->patient_name,
            'screener_completed_at' => now()->subDay(), 'requires_immediate_escalation' => true,
        ]);
        Appointment::factory()->create(['doctor_id' => $flagged->doctor_id, 'medical_center_id' => $flagged->medical_center_id, 'screener_completed_at' => now(), 'requires_immediate_escalation' => false, 'self_harm_flag' => false]);

        $this->actingAs($flagged->doctor, 'doctor')->get(route('doctor.crisis-queue.index'))
            ->assertOk()->assertSee($flagged->patient_name)->assertSee('20')->assertSee('16');
    }

    public function test_acknowledged_escalation_moves_to_reviewed_section(): void
    {
        $appointment = Appointment::factory()->create(['screener_completed_at' => now(), 'self_harm_flag' => true]);

        $this->actingAs($appointment->doctor, 'doctor')
            ->patch(route('doctor.crisis-queue.acknowledge', $appointment))->assertRedirect();

        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'escalation_reviewed' => true]);
        $this->actingAs($appointment->doctor, 'doctor')->get(route('doctor.crisis-queue.index'))
            ->assertOk()->assertSee('Reviewed')->assertSee($appointment->patient_name);
    }

    public function test_doctor_cannot_acknowledge_another_doctors_escalation(): void
    {
        $appointment = Appointment::factory()->create(['screener_completed_at' => now(), 'self_harm_flag' => true]);

        $this->actingAs(Doctor::factory()->create(), 'doctor')
            ->patch(route('doctor.crisis-queue.acknowledge', $appointment))->assertForbidden();
    }
}
