<?php

namespace Tests\Feature\Doctor;

use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_can_view_own_appointments(): void
    {
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->for($doctor)->create([
            'appointment_date' => now()->addDay(),
            'mode' => 'online',
            'pre_assessment_risk_level' => 'moderate',
            'screener_completed_at' => now(),
        ]);

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.appointments.index'));

        $response->assertOk()
            ->assertSee($appointment->patient_name)
            ->assertSee('Moderate risk')
            ->assertSee('Video consultation')
            ->assertSee('Pre-assessment: Ready')
            ->assertSee('Filter appointments')
            ->assertSee('Filtered results')
            ->assertSee('data-filter-results-list', false);
    }

    public function test_appointments_index_renders_clinical_empty_states(): void
    {
        $doctor = Doctor::factory()->create();

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.appointments.index'));

        $response->assertOk()
            ->assertSee('Nothing on the calendar for today.')
            ->assertSee('No upcoming appointments.')
            ->assertSee('No past appointments yet.');
    }

    public function test_low_risk_appointment_detail_hides_crisis_banner(): void
    {
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->for($doctor)->create([
            'pre_assessment_risk_level' => 'low',
            'requires_immediate_escalation' => false,
            'phq9_total' => 3,
            'phq9_severity' => 'minimal',
            'gad7_total' => 4,
            'gad7_severity' => 'minimal',
        ]);

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.appointments.show', $appointment));

        $response->assertOk()
            ->assertSee('Back to appointments')
            ->assertSee('Question-by-question review')
            ->assertSee('Doctor notes')
            ->assertDontSee('Immediate clinical review required')
            ->assertDontSee('@endif')
            ->assertDontSee('@if ($appointment->requires_immediate_escalation', false);
    }

    public function test_elevated_risk_appointment_detail_shows_crisis_banner(): void
    {
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->for($doctor)->create([
            'pre_assessment_risk_level' => 'elevated',
            'requires_immediate_escalation' => true,
        ]);

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.appointments.show', $appointment));

        $response->assertOk()
            ->assertSee('Immediate clinical review required')
            ->assertSee('Elevated risk')
            ->assertDontSee('@endif')
            ->assertDontSee('@if ($appointment->requires_immediate_escalation', false);
    }

    public function test_self_harm_flag_shows_crisis_banner_even_when_risk_is_not_elevated(): void
    {
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->for($doctor)->create([
            'pre_assessment_risk_level' => 'moderate',
            'self_harm_flag' => true,
            'requires_immediate_escalation' => false,
        ]);

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.appointments.show', $appointment));

        $response->assertOk()
            ->assertSee('Immediate clinical review required')
            ->assertSee('positive response relating to death or self-harm');
    }

    public function test_doctor_cannot_view_another_doctors_appointment(): void
    {
        $doctor = Doctor::factory()->create();
        $otherAppointment = Appointment::factory()->create();

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.appointments.show', $otherAppointment));

        $response->assertStatus(403);
    }

    public function test_doctor_can_mark_appointment_completed(): void
    {
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->for($doctor)->create([
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($doctor, 'doctor')
            ->patch(route('doctor.appointments.status', $appointment), ['status' => 'completed']);

        $response->assertRedirect();
        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 'completed']);
    }

    public function test_doctor_cannot_update_another_doctors_appointment_status(): void
    {
        $doctor = Doctor::factory()->create();
        $otherAppointment = Appointment::factory()->create(['status' => 'confirmed']);

        $response = $this->actingAs($doctor, 'doctor')
            ->patch(route('doctor.appointments.status', $otherAppointment), ['status' => 'completed']);

        $response->assertStatus(403);
    }
}
