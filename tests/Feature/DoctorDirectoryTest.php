<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\DoctorClinicAffiliation;
use App\Models\MedicalCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_directory_lists_approved_doctors(): void
    {
        $doctor = Doctor::factory()->create(['name' => 'Dr. Anusha Perera']);

        $response = $this->get(route('doctors.index'));

        $response
            ->assertOk()
            ->assertSee('Book any doctor, any clinic, one calm search')
            ->assertSee('Dr. Anusha Perera');
    }

    public function test_doctor_directory_lists_approved_doctors_with_zero_affiliations(): void
    {
        Doctor::factory()->create(['name' => 'Dr. No Clinic']);

        $response = $this->get(route('doctors.index'));

        $response->assertOk()->assertSee('Dr. No Clinic');
    }

    public function test_doctor_directory_hides_doctors_pending_approval(): void
    {
        Doctor::factory()->pendingApproval()->create(['name' => 'Dr. Pending']);

        $response = $this->get(route('doctors.index'));

        $response->assertOk()->assertDontSee('Dr. Pending');
    }

    public function test_doctor_directory_hides_doctors_who_have_not_finished_onboarding(): void
    {
        Doctor::factory()->create(['name' => 'Dr. Incomplete', 'onboarding_step' => 'basic_info_done']);

        $response = $this->get(route('doctors.index'));

        $response->assertOk()->assertDontSee('Dr. Incomplete');
    }

    public function test_doctor_profile_page_can_be_viewed(): void
    {
        $doctor = Doctor::factory()->create(['name' => 'Dr. Anusha Perera']);

        $response = $this->get(route('doctors.show', $doctor));

        $response->assertOk()->assertSee('Dr. Anusha Perera');
    }

    public function test_unapproved_doctor_profile_returns_404(): void
    {
        $doctor = Doctor::factory()->pendingApproval()->create();

        $response = $this->get(route('doctors.show', $doctor));

        $response->assertNotFound();
    }

    public function test_doctor_with_active_affiliation_shows_book_button(): void
    {
        $doctor = Doctor::factory()->create();
        DoctorClinicAffiliation::factory()->for($doctor)->create();

        $response = $this->get(route('doctors.show', $doctor));

        $response
            ->assertOk()
            ->assertSee('Book appointment')
            ->assertSee(route('booking.clinic', $doctor), false);
    }

    public function test_doctor_with_only_an_unapproved_clinic_is_not_offered_for_booking(): void
    {
        $doctor = Doctor::factory()->create();
        $clinic = MedicalCenter::factory()->create(['status' => 'pending']);
        DoctorClinicAffiliation::factory()->for($doctor)->create(['clinic_id' => $clinic->id]);

        $response = $this->get(route('doctors.show', $doctor));

        $response
            ->assertOk()
            ->assertSee('Not currently accepting bookings')
            ->assertDontSee(route('booking.clinic', $doctor), false);
    }

    public function test_doctor_without_active_affiliation_shows_view_only_profile(): void
    {
        $doctor = Doctor::factory()->create();

        $response = $this->get(route('doctors.show', $doctor));

        $response->assertOk()
            ->assertSee('Not currently accepting bookings')
            ->assertDontSee('Book appointment');
    }

    public function test_directory_shows_location_filter_populated_from_active_clinics(): void
    {
        $doctor = Doctor::factory()->create();
        $affiliation = DoctorClinicAffiliation::factory()->for($doctor)->create();
        $affiliation->clinic->update(['address' => '123 Galle Road, Colombo']);

        $response = $this->get(route('doctors.index'));

        $response->assertOk()->assertSee('Colombo');
    }
}
