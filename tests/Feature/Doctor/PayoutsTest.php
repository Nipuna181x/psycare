<?php

namespace Tests\Feature\Doctor;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorPayout;
use App\Models\MedicalCenter;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayoutsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_doctor_login(): void
    {
        $this->get(route('doctor.payouts.index'))->assertRedirect(route('doctor.login'));
    }

    public function test_doctor_sees_pending_by_clinic_and_paid_history_read_only(): void
    {
        $doctor = Doctor::factory()->create();
        $otherDoctor = Doctor::factory()->create();
        $clinic = MedicalCenter::factory()->approved()->create(['name' => 'Calm Clinic']);
        $otherClinic = MedicalCenter::factory()->approved()->create(['name' => 'Hope Centre']);

        $pendingAppointment = Appointment::factory()->for($doctor)->create(['medical_center_id' => $clinic->id]);
        Payment::factory()->for($pendingAppointment)->succeeded()->create(['doctor_amount' => 3000, 'clinic_amount' => 500, 'amount' => 3500]);

        $currentPayout = DoctorPayout::factory()->create([
            'doctor_id' => $doctor->id,
            'clinic_id' => $otherClinic->id,
            'amount' => 5000,
            'paid_at' => now(),
        ]);
        $paidAppointment = Appointment::factory()->for($doctor)->create(['medical_center_id' => $otherClinic->id]);
        Payment::factory()->for($paidAppointment)->paidToDoctor()->create([
            'doctor_amount' => 5000,
            'clinic_amount' => 500,
            'amount' => 5500,
            'doctor_payout_id' => $currentPayout->id,
            'doctor_paid_at' => now(),
        ]);

        $oldPayout = DoctorPayout::factory()->create([
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'amount' => 7000,
            'paid_at' => now()->subMonths(2),
        ]);
        $oldAppointment = Appointment::factory()->for($doctor)->create(['medical_center_id' => $clinic->id]);
        Payment::factory()->for($oldAppointment)->paidToDoctor()->create([
            'doctor_amount' => 7000,
            'clinic_amount' => 500,
            'amount' => 7500,
            'doctor_payout_id' => $oldPayout->id,
            'doctor_paid_at' => now()->subMonths(2),
        ]);

        $hiddenAppointment = Appointment::factory()->for($otherDoctor)->create(['medical_center_id' => $clinic->id]);
        Payment::factory()->for($hiddenAppointment)->succeeded()->create(['doctor_amount' => 9999]);

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.payouts.index'));

        $response->assertOk()
            ->assertSee('Payout status is managed by each clinic')
            ->assertSee('LKR 3,000.00')
            ->assertSee('LKR 12,000.00')
            ->assertSee('LKR 5,000.00')
            ->assertSee('Calm Clinic')
            ->assertSee('Hope Centre')
            ->assertDontSee('LKR 9,999.00')
            ->assertDontSee(route('medical-center.payments.doctors.mark-paid', $doctor));
    }

    public function test_empty_state_is_clear(): void
    {
        $doctor = Doctor::factory()->create();

        $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.payouts.index'))
            ->assertOk()
            ->assertSee('No pending payouts.')
            ->assertSee('No paid payouts recorded yet.');
    }
}
