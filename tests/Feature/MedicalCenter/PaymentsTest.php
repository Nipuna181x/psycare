<?php

namespace Tests\Feature\MedicalCenter;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorPayout;
use App\Models\MedicalCenter;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_clinic_payment_page_lists_only_succeeded_payments_for_current_clinic(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $otherClinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create(['name' => 'Amina Silva']);
        $patient = User::factory()->create(['name' => 'Maya Patient']);

        $visibleAppointment = Appointment::factory()->for($doctor)->for($patient)->create([
            'medical_center_id' => $clinic->id,
            'patient_name' => 'Maya Patient',
        ]);
        Payment::factory()->for($visibleAppointment)->succeeded()->create([
            'doctor_amount' => 4000,
            'clinic_amount' => 1000,
            'amount' => 5000,
        ]);

        $failedAppointment = Appointment::factory()->for($doctor)->create(['medical_center_id' => $clinic->id, 'patient_name' => 'Failed Patient']);
        Payment::factory()->for($failedAppointment)->create(['status' => 'failed']);

        $otherAppointment = Appointment::factory()->for($doctor)->create(['medical_center_id' => $otherClinic->id, 'patient_name' => 'Other Clinic Patient']);
        Payment::factory()->for($otherAppointment)->succeeded()->create();

        $this->actingAs($clinic, 'medical_center')
            ->get(route('medical-center.payments.index'))
            ->assertOk()
            ->assertSee('Maya Patient')
            ->assertSee('Amina Silva')
            ->assertSee('LKR 5,000.00')
            ->assertDontSee('Failed Patient')
            ->assertDontSee('Other Clinic Patient');
    }

    public function test_filters_search_by_patient_doctor_date_and_payout_status(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create(['name' => 'Filter Doctor']);
        $otherDoctor = Doctor::factory()->create(['name' => 'Other Doctor']);

        $matchingAppointment = Appointment::factory()->for($doctor)->create(['medical_center_id' => $clinic->id, 'patient_name' => 'Matching Patient']);
        Payment::factory()->for($matchingAppointment)->succeeded()->create(['processed_at' => now(), 'doctor_payout_status' => 'unpaid']);

        $otherAppointment = Appointment::factory()->for($otherDoctor)->create(['medical_center_id' => $clinic->id, 'patient_name' => 'Hidden Patient']);
        Payment::factory()->for($otherAppointment)->paidToDoctor()->create(['processed_at' => now()->subMonth()]);

        $this->actingAs($clinic, 'medical_center')
            ->get(route('medical-center.payments.index', [
                'from' => today()->toDateString(),
                'to' => today()->toDateString(),
                'doctor_id' => $doctor->id,
                'payout_status' => 'unpaid',
                'search' => 'Matching',
            ]))
            ->assertOk()
            ->assertSee('Matching Patient')
            ->assertDontSee('Hidden Patient');
    }

    public function test_clinic_marks_all_unpaid_succeeded_payments_for_one_doctor_as_paid_with_audit(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create(['name' => 'Wellbeing Clinic']);
        $doctor = Doctor::factory()->create(['name' => 'Nadia Perera']);
        $otherDoctor = Doctor::factory()->create();

        $payments = collect([3000, 4000])->map(function (int $doctorAmount) use ($clinic, $doctor): Payment {
            $appointment = Appointment::factory()->for($doctor)->create(['medical_center_id' => $clinic->id]);

            return Payment::factory()->for($appointment)->succeeded()->create([
                'doctor_amount' => $doctorAmount,
                'clinic_amount' => 1000,
                'amount' => $doctorAmount + 1000,
            ]);
        });

        $untouchedAppointment = Appointment::factory()->for($otherDoctor)->create(['medical_center_id' => $clinic->id]);
        $untouched = Payment::factory()->for($untouchedAppointment)->succeeded()->create();

        $this->actingAs($clinic, 'medical_center')
            ->patch(route('medical-center.payments.doctors.mark-paid', $doctor))
            ->assertRedirect()
            ->assertSessionHas('status', 'Recorded LKR 7,000.00 as paid to Dr. Nadia Perera.');

        foreach ($payments as $payment) {
            $this->assertSame('paid', $payment->fresh()->doctor_payout_status);
            $this->assertNotNull($payment->fresh()->doctor_paid_at);
            $this->assertNotNull($payment->fresh()->doctor_payout_id);
        }

        $this->assertSame('unpaid', $untouched->fresh()->doctor_payout_status);
        $payout = DoctorPayout::query()->sole();
        $this->assertSame('7000.00', $payout->amount);
        $this->assertSame(2, $payout->payment_count);
        $this->assertSame('medical_center', $payout->marked_by_type);
        $this->assertSame($clinic->id, $payout->marked_by_id);

        $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.payouts.index'))
            ->assertOk()
            ->assertSee('Wellbeing Clinic')
            ->assertSee('LKR 7,000.00')
            ->assertSee('Paid');
    }
}
