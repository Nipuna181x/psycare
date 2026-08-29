<?php

namespace Tests\Feature\MedicalCenter;

use App\Models\ClinicStaff;
use App\Models\Doctor;
use App\Models\DoctorClinicAffiliation;
use App\Models\MedicalCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_tab_shows_my_doctors_with_active_affiliations_only(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $activeDoctor = Doctor::factory()->create(['name' => 'Dr. Active One']);
        $requestedDoctor = Doctor::factory()->create(['name' => 'Dr. Requested One']);
        DoctorClinicAffiliation::factory()->for($activeDoctor)->create(['clinic_id' => $clinic->id, 'status' => 'active']);
        DoctorClinicAffiliation::factory()->for($requestedDoctor)->requested()->create(['clinic_id' => $clinic->id]);

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.doctors.index'));

        $response->assertOk()->assertSee('Dr. Active One')->assertDontSee('Dr. Requested One');
    }

    public function test_pending_tab_shows_only_requested_and_recent_activity_shows_responded(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $requestedDoctor = Doctor::factory()->create(['name' => 'Dr. Waiting']);
        $activeDoctor = Doctor::factory()->create(['name' => 'Dr. Now Active']);
        DoctorClinicAffiliation::factory()->for($requestedDoctor)->requested()->create(['clinic_id' => $clinic->id]);
        DoctorClinicAffiliation::factory()->for($activeDoctor)->create([
            'clinic_id' => $clinic->id,
            'status' => 'active',
            'responded_by_doctor_at' => now(),
        ]);

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.doctors.index', ['tab' => 'pending']));

        $response->assertOk()->assertSee('Dr. Waiting')->assertSee('Dr. Now Active');
    }

    public function test_search_tab_finds_doctors_by_license_number(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create(['license_number' => 'SLMC-9999']);
        Doctor::factory()->create(['license_number' => 'SLMC-1111']);

        $response = $this->actingAs($clinic, 'medical_center')
            ->get(route('medical-center.doctors.index', ['tab' => 'search', 'license_number' => 'SLMC-9999']));

        $response->assertOk()->assertSee($doctor->name)->assertDontSee('SLMC-1111');
    }

    public function test_search_tab_finds_doctors_by_name(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create(['name' => 'Dr. Nadeesha Perera']);
        Doctor::factory()->create(['name' => 'Dr. Someone Else']);

        $response = $this->actingAs($clinic, 'medical_center')
            ->get(route('medical-center.doctors.index', ['tab' => 'search', 'name' => 'Nadeesha']));

        $response->assertOk()->assertSee($doctor->name)->assertDontSee('Dr. Someone Else');
    }

    public function test_search_excludes_doctors_pending_approval(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        Doctor::factory()->pendingApproval()->create(['name' => 'Dr. Pending', 'license_number' => 'SLMC-2222']);

        $response = $this->actingAs($clinic, 'medical_center')
            ->get(route('medical-center.doctors.index', ['tab' => 'search', 'license_number' => 'SLMC-2222']));

        $response->assertOk()->assertDontSee('Dr. Pending');
    }

    public function test_clinic_can_send_a_work_request(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();

        $response = $this->actingAs($clinic, 'medical_center')
            ->post(route('medical-center.doctors.request', $doctor));

        $response->assertRedirect();
        $this->assertDatabaseHas('doctor_clinic_affiliations', [
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'status' => 'requested',
        ]);
    }

    public function test_clinic_cannot_send_a_duplicate_request(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        DoctorClinicAffiliation::factory()->for($doctor)->requested()->create(['clinic_id' => $clinic->id]);

        $response = $this->actingAs($clinic, 'medical_center')
            ->post(route('medical-center.doctors.request', $doctor));

        $response->assertStatus(422);
    }

    public function test_my_doctors_card_offers_no_send_request_button(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        DoctorClinicAffiliation::factory()->for($doctor)->create(['clinic_id' => $clinic->id, 'status' => 'active']);

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.doctors.index'));

        $response->assertOk()->assertDontSee('Send Work Request');
    }

    public function test_search_result_offers_send_request_when_no_existing_affiliation(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        Doctor::factory()->create(['license_number' => 'SLMC-3333']);

        $response = $this->actingAs($clinic, 'medical_center')
            ->get(route('medical-center.doctors.index', ['tab' => 'search', 'license_number' => 'SLMC-3333']));

        $response->assertOk()->assertSee('Send Work Request', false);
    }

    public function test_clinic_only_sees_its_own_affiliations(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $otherClinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create(['name' => 'Dr. Other Clinic Doctor']);
        DoctorClinicAffiliation::factory()->for($doctor)->requested()->create(['clinic_id' => $otherClinic->id]);

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.doctors.index', ['tab' => 'pending']));

        $response->assertOk()->assertDontSee('Dr. Other Clinic Doctor');
    }

    public function test_staff_login_sees_identical_my_doctors_data(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create(['name' => 'Dr. Shared Roster']);
        DoctorClinicAffiliation::factory()->for($doctor)->create(['clinic_id' => $clinic->id, 'status' => 'active']);
        $staff = ClinicStaff::factory()->for($clinic, 'medicalCenter')->create();

        $response = $this->actingAs($staff, 'clinic_staff')->get(route('medical-center.doctors.index'));

        $response->assertOk()->assertSee('Dr. Shared Roster');
    }
}
