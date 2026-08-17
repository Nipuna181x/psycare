<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\MedicalCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_directory_lists_bookable_doctors(): void
    {
        $doctor = Doctor::factory()->create(['name' => 'Dr. Anusha Perera', 'status' => 'active']);

        $response = $this->get(route('doctors.index'));

        $response
            ->assertOk()
            ->assertSee('Book any doctor, any clinic, one calm search')
            ->assertSee('Dr. Anusha Perera');
    }

    public function test_doctor_directory_hides_inactive_doctors(): void
    {
        Doctor::factory()->create(['name' => 'Dr. Inactive', 'status' => 'inactive']);

        $response = $this->get(route('doctors.index'));

        $response->assertOk()->assertDontSee('Dr. Inactive');
    }

    public function test_doctor_directory_hides_doctors_from_unapproved_clinics(): void
    {
        $pendingCenter = MedicalCenter::factory()->create(['status' => 'pending']);
        Doctor::factory()->for($pendingCenter)->create(['name' => 'Dr. Pending Clinic', 'status' => 'active']);

        $response = $this->get(route('doctors.index'));

        $response->assertOk()->assertDontSee('Dr. Pending Clinic');
    }

    public function test_doctor_profile_page_can_be_viewed(): void
    {
        $doctor = Doctor::factory()->create(['name' => 'Dr. Anusha Perera', 'status' => 'active']);

        $response = $this->get(route('doctors.show', $doctor));

        $response->assertOk()->assertSee('Dr. Anusha Perera');
    }

    public function test_inactive_doctor_profile_returns_404(): void
    {
        $doctor = Doctor::factory()->create(['status' => 'inactive']);

        $response = $this->get(route('doctors.show', $doctor));

        $response->assertNotFound();
    }
}
