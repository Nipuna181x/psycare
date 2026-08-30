<?php

namespace Tests\Feature\Doctor;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorClinicAffiliation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_switcher_is_hidden_with_a_single_active_affiliation(): void
    {
        $doctor = Doctor::factory()->create();
        DoctorClinicAffiliation::factory()->for($doctor)->create();

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.dashboard'));

        $response->assertOk()->assertDontSee('All clinics');
    }

    public function test_switcher_is_shown_with_multiple_active_affiliations(): void
    {
        $doctor = Doctor::factory()->create();
        DoctorClinicAffiliation::factory()->for($doctor)->count(2)->create();

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.dashboard'));

        $response->assertOk()->assertSee('All clinics');
    }

    public function test_dashboard_shows_empty_state_with_zero_affiliations(): void
    {
        $doctor = Doctor::factory()->create();

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.dashboard'));

        $response->assertOk()->assertSee('not currently affiliated with any clinic');
    }

    public function test_appointments_are_scoped_to_the_active_clinic_context(): void
    {
        $doctor = Doctor::factory()->create();
        $affiliations = DoctorClinicAffiliation::factory()->for($doctor)->count(2)->create();
        [$clinicA, $clinicB] = $affiliations->pluck('clinic_id')->all();

        $appointmentA = Appointment::factory()->for($doctor)->create(['medical_center_id' => $clinicA, 'patient_name' => 'Patient At A']);
        $appointmentB = Appointment::factory()->for($doctor)->create(['medical_center_id' => $clinicB, 'patient_name' => 'Patient At B']);

        $this->actingAs($doctor, 'doctor')->post(route('doctor.clinic-context.update'), ['clinic_id' => $clinicA]);

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.appointments.index'));

        $response->assertOk()->assertSee('Patient At A')->assertDontSee('Patient At B');
    }

    public function test_doctor_cannot_view_appointment_belonging_to_a_different_clinic_than_active_context(): void
    {
        $doctor = Doctor::factory()->create();
        $affiliations = DoctorClinicAffiliation::factory()->for($doctor)->count(2)->create();
        [$clinicA, $clinicB] = $affiliations->pluck('clinic_id')->all();

        $appointment = Appointment::factory()->for($doctor)->create(['medical_center_id' => $clinicB]);

        $this->actingAs($doctor, 'doctor')->post(route('doctor.clinic-context.update'), ['clinic_id' => $clinicA]);

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.appointments.show', $appointment));

        $response->assertStatus(403);
    }

    public function test_doctor_cannot_set_context_to_a_clinic_they_are_not_affiliated_with(): void
    {
        $doctor = Doctor::factory()->create();
        DoctorClinicAffiliation::factory()->for($doctor)->count(2)->create();

        $response = $this->actingAs($doctor, 'doctor')->post(route('doctor.clinic-context.update'), ['clinic_id' => 999999]);

        $response->assertStatus(403);
    }

    public function test_dashboard_active_clinics_card_shows_all_names_when_all_clinics_selected(): void
    {
        $doctor = Doctor::factory()->create();
        $affiliations = DoctorClinicAffiliation::factory()->for($doctor)->count(2)->create();

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.dashboard'));

        $response->assertOk();
        foreach ($affiliations as $affiliation) {
            $response->assertSee($affiliation->clinic->name);
        }
    }

    public function test_dashboard_active_clinics_card_shows_only_selected_clinic_name(): void
    {
        $doctor = Doctor::factory()->create();
        $affiliations = DoctorClinicAffiliation::factory()->for($doctor)->count(2)->create();
        [$clinicA, $clinicB] = $affiliations->pluck('clinic_id')->all();
        $clinicAName = $affiliations->firstWhere('clinic_id', $clinicA)->clinic->name;
        $clinicBName = $affiliations->firstWhere('clinic_id', $clinicB)->clinic->name;

        $this->actingAs($doctor, 'doctor')->post(route('doctor.clinic-context.update'), ['clinic_id' => $clinicA]);

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.dashboard'));

        $response->assertOk()->assertSeeInOrder([$clinicAName, 'Active clinics']);
    }
}
