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

    public function test_doctor_sees_pending_by_clinic_and_paid_history(): void
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
            ->assertSee('Once the money reaches you')
            ->assertSee('LKR 3,000.00')
            ->assertSee('LKR 12,000.00')
            ->assertSee('LKR 5,000.00')
            ->assertSee('Calm Clinic')
            ->assertSee('Hope Centre')
            ->assertSee(route('doctor.payouts.received', $currentPayout))
            ->assertDontSee('LKR 9,999.00')
            ->assertDontSee(route('medical-center.payments.doctors.mark-paid', $doctor));
    }

    public function test_doctor_can_acknowledge_a_paid_payout_as_received(): void
    {
        $doctor = Doctor::factory()->create();
        $clinic = MedicalCenter::factory()->approved()->create(['name' => 'Wellbeing Clinic']);
        $payout = DoctorPayout::factory()->create([
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'status' => 'paid',
        ]);
        $appointment = Appointment::factory()->for($doctor)->create(['medical_center_id' => $clinic->id]);
        $payment = Payment::factory()->for($appointment)->paidToDoctor()->create([
            'doctor_payout_id' => $payout->id,
        ]);

        $this->actingAs($doctor, 'doctor')
            ->patch(route('doctor.payouts.received', $payout))
            ->assertRedirect()
            ->assertSessionHas('status', 'Payout marked as received.');

        $this->assertSame('completed', $payout->fresh()->status);
        $this->assertNotNull($payout->fresh()->received_at);
        $this->assertSame('paid', $payment->fresh()->doctor_payout_status);

        $this->actingAs($clinic, 'medical_center')
            ->get(route('medical-center.payments.index'))
            ->assertOk()
            ->assertSee('completed');
    }

    public function test_doctor_cannot_acknowledge_another_doctors_payout(): void
    {
        $payout = DoctorPayout::factory()->create();

        $this->actingAs(Doctor::factory()->create(), 'doctor')
            ->patch(route('doctor.payouts.received', $payout))
            ->assertForbidden();

        $this->assertSame('paid', $payout->fresh()->status);
        $this->assertNull($payout->fresh()->received_at);
    }

    public function test_received_acknowledgment_is_idempotent(): void
    {
        $payout = DoctorPayout::factory()->completed()->create();
        $receivedAt = $payout->received_at;

        $this->actingAs($payout->doctor, 'doctor')
            ->patch(route('doctor.payouts.received', $payout))
            ->assertRedirect()
            ->assertSessionHas('status', 'This payout was already marked as received.');

        $this->assertTrue($receivedAt->equalTo($payout->fresh()->received_at));
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
