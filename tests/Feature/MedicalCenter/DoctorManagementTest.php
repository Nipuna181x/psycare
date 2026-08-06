<?php

namespace Tests\Feature\MedicalCenter;

use App\Models\Doctor;
use App\Models\MedicalCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_medical_center_can_view_its_doctors(): void
    {
        $medicalCenter = MedicalCenter::factory()->approved()->create();
        Doctor::factory()->for($medicalCenter)->create();

        $response = $this->actingAs($medicalCenter, 'medical_center')
            ->get(route('medical-center.doctor-managment.index'));

        $response->assertStatus(200);
    }

    public function test_medical_center_can_create_a_doctor_with_username_and_password(): void
    {
        $medicalCenter = MedicalCenter::factory()->approved()->create();

        $response = $this->actingAs($medicalCenter, 'medical_center')
            ->post(route('medical-center.doctor-managment.store'), [
                'name' => 'Dr. Alex Smith',
                'email' => 'alex@example.com',
                'username' => 'dr.alex',
                'password' => 'password',
                'password_confirmation' => 'password',
                'specialization' => 'Psychiatry',
                'phone' => '0712345678',
            ]);

        $response->assertRedirect(route('medical-center.doctor-managment.index'));

        $this->assertDatabaseHas('doctors', [
            'medical_center_id' => $medicalCenter->id,
            'username' => 'dr.alex',
            'status' => 'active',
        ]);
    }

    public function test_medical_center_can_update_its_own_doctor(): void
    {
        $medicalCenter = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->for($medicalCenter)->create();

        $response = $this->actingAs($medicalCenter, 'medical_center')
            ->put(route('medical-center.doctor-managment.update', $doctor), [
                'name' => 'Dr. Updated Name',
                'email' => $doctor->email,
                'username' => $doctor->username,
                'specialization' => $doctor->specialization,
                'phone' => $doctor->phone,
                'status' => 'inactive',
            ]);

        $response->assertRedirect(route('medical-center.doctor-managment.index'));

        $this->assertDatabaseHas('doctors', [
            'id' => $doctor->id,
            'name' => 'Dr. Updated Name',
            'status' => 'inactive',
        ]);
    }

    public function test_medical_center_can_delete_its_own_doctor(): void
    {
        $medicalCenter = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->for($medicalCenter)->create();

        $response = $this->actingAs($medicalCenter, 'medical_center')
            ->delete(route('medical-center.doctor-managment.destroy', $doctor));

        $response->assertRedirect(route('medical-center.doctor-managment.index'));
        $this->assertDatabaseMissing('doctors', ['id' => $doctor->id]);
    }

    public function test_medical_center_cannot_manage_another_medical_centers_doctor(): void
    {
        $medicalCenter = MedicalCenter::factory()->approved()->create();
        $otherDoctor = Doctor::factory()->create();

        $response = $this->actingAs($medicalCenter, 'medical_center')
            ->get(route('medical-center.doctor-managment.edit', $otherDoctor));

        $response->assertStatus(403);
    }
}
