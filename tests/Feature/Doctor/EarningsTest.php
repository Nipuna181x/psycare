<?php

namespace Tests\Feature\Doctor;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorClinicAffiliation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EarningsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('doctor.earnings.index'))->assertRedirect(route('doctor.login'));
    }

    public function test_earnings_page_shows_a_clean_empty_state_with_no_appointments(): void
    {
        $doctor = Doctor::factory()->create();

        $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.earnings.index'))
            ->assertOk()
            ->assertSee('LKR 0')
            ->assertSee('No earnings yet');
    }

    public function test_earnings_totals_are_accurate_across_multiple_clinics(): void
    {
        $doctor = Doctor::factory()->create();
        $affiliationA = DoctorClinicAffiliation::factory()->for($doctor)->create();
        $affiliationB = DoctorClinicAffiliation::factory()->for($doctor)->create();

        Appointment::factory()->for($doctor)->create([
            'medical_center_id' => $affiliationA->clinic_id,
            'status' => 'completed',
            'doctor_fee_charged' => 3000,
        ]);
        Appointment::factory()->for($doctor)->create([
            'medical_center_id' => $affiliationB->clinic_id,
            'status' => 'completed',
            'doctor_fee_charged' => 4000,
        ]);

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.earnings.index'));

        $response->assertOk()->assertSee('LKR '.number_format(7000));
    }

    public function test_cancelled_appointments_are_excluded_from_earnings(): void
    {
        $doctor = Doctor::factory()->create();
        $affiliation = DoctorClinicAffiliation::factory()->for($doctor)->create();

        Appointment::factory()->for($doctor)->create([
            'medical_center_id' => $affiliation->clinic_id,
            'status' => 'completed',
            'doctor_fee_charged' => 3000,
        ]);
        Appointment::factory()->for($doctor)->create([
            'medical_center_id' => $affiliation->clinic_id,
            'status' => 'cancelled',
            'doctor_fee_charged' => 9999,
        ]);

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.earnings.index'));

        $response->assertOk()->assertSee('LKR '.number_format(3000))->assertDontSee('LKR '.number_format(9999));
    }

    public function test_this_month_only_counts_the_current_month(): void
    {
        $doctor = Doctor::factory()->create();
        $affiliation = DoctorClinicAffiliation::factory()->for($doctor)->create();

        Appointment::factory()->for($doctor)->create([
            'medical_center_id' => $affiliation->clinic_id,
            'status' => 'completed',
            'doctor_fee_charged' => 2000,
            'appointment_date' => now(),
        ]);
        Appointment::factory()->for($doctor)->create([
            'medical_center_id' => $affiliation->clinic_id,
            'status' => 'completed',
            'doctor_fee_charged' => 5000,
            'appointment_date' => now()->subMonths(2),
        ]);

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.earnings.index'));

        $response->assertOk();
        $response->assertSeeInOrder(['This month', 'LKR '.number_format(2000)]);
    }
}
