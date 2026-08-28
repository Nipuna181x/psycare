<?php

namespace Tests\Feature\Patient;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\PatientConsent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('settings.index'))->assertRedirect(route('login'));
    }

    public function test_patient_sees_only_doctors_they_have_been_treated_by(): void
    {
        $patient = User::factory()->create();
        $treatingDoctor = Doctor::factory()->create(['name' => 'Treating Doctor']);
        $otherDoctor = Doctor::factory()->create(['name' => 'Unrelated Doctor']);
        Appointment::factory()->for($treatingDoctor)->create(['user_id' => $patient->id]);

        $response = $this->actingAs($patient)->get(route('settings.index'));

        $response->assertOk()->assertSee('Treating Doctor')->assertDontSee('Unrelated Doctor');
    }

    public function test_patient_can_grant_access(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        Appointment::factory()->for($doctor)->create(['user_id' => $patient->id]);

        $this->actingAs($patient)
            ->patch(route('settings.care-access.update', $doctor), ['grant' => '1'])
            ->assertRedirect();

        $this->assertDatabaseHas('patient_consents', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'revoked_at' => null,
        ]);
    }

    public function test_patient_can_revoke_access_without_creating_a_second_row(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        Appointment::factory()->for($doctor)->create(['user_id' => $patient->id]);
        PatientConsent::factory()->create(['patient_id' => $patient->id, 'doctor_id' => $doctor->id]);

        $this->actingAs($patient)
            ->patch(route('settings.care-access.update', $doctor), ['grant' => '0'])
            ->assertRedirect();

        $this->assertSame(1, PatientConsent::query()->where('patient_id', $patient->id)->where('doctor_id', $doctor->id)->count());
        $this->assertDatabaseHas('patient_consents', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);
        $this->assertNotNull(PatientConsent::query()->where('patient_id', $patient->id)->first()->revoked_at);
    }

    public function test_granting_then_revoking_then_granting_again_reuses_the_same_row(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        Appointment::factory()->for($doctor)->create(['user_id' => $patient->id]);

        $this->actingAs($patient)->patch(route('settings.care-access.update', $doctor), ['grant' => '1']);
        $this->actingAs($patient)->patch(route('settings.care-access.update', $doctor), ['grant' => '0']);
        $this->actingAs($patient)->patch(route('settings.care-access.update', $doctor), ['grant' => '1']);

        $this->assertSame(1, PatientConsent::query()->count());
        $this->assertNull(PatientConsent::query()->first()->revoked_at);
    }

    public function test_patient_cannot_toggle_consent_for_a_doctor_they_have_never_seen(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();

        $this->actingAs($patient)
            ->patch(route('settings.care-access.update', $doctor), ['grant' => '1'])
            ->assertForbidden();

        $this->assertSame(0, PatientConsent::query()->count());
    }
}
