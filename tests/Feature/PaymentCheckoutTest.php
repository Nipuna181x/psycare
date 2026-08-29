<?php

namespace Tests\Feature;

use App\Mail\PaymentReceipt;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorAvailabilitySlot;
use App\Models\DoctorClinicAffiliation;
use App\Models\MedicalCenter;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\BookingConfirmed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PaymentCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_checkout_confirms_appointment_splits_ledger_and_queues_receipt_once(): void
    {
        Notification::fake();
        Mail::fake();
        [$patient, $doctor, $clinic, $slot] = $this->bookingContext();

        Http::fake(fn (Request $request) => $request->method() === 'POST'
            ? Http::response([
                'id' => 'cs_test_paid',
                'url' => 'https://checkout.stripe.com/c/pay/test-session',
                'expires_at' => now()->addMinutes(30)->timestamp,
            ])
            : Http::response($this->paidStripeSession()));

        $this->actingAs($patient)
            ->post(route('booking.confirm', $doctor))
            ->assertRedirect('https://checkout.stripe.com/c/pay/test-session');

        $appointment = Appointment::query()->sole();
        $payment = Payment::query()->sole();
        $this->assertSame('pending_payment', $appointment->status);
        $this->assertTrue($slot->fresh()->is_booked);
        $this->assertSame($appointment->id, $slot->fresh()->appointment_id);
        $this->assertSame('4500.00', $payment->doctor_amount);
        $this->assertSame('1000.00', $payment->clinic_amount);
        $this->assertSame('5500.00', $payment->amount);

        Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
            && $request->url() === 'https://api.stripe.com/v1/checkout/sessions'
            && data_get($request->data(), 'metadata.appointment_id') === (string) $appointment->id
            && data_get($request->data(), 'line_items.0.price_data.product_data.name') === 'Doctor session fee'
            && data_get($request->data(), 'line_items.0.price_data.unit_amount') === 450000
            && data_get($request->data(), 'line_items.1.price_data.unit_amount') === 100000);

        $this->actingAs($patient)
            ->get(route('booking.payment.success', ['session_id' => 'cs_test_paid']))
            ->assertRedirect(route('booking.confirmed', $appointment));

        $this->assertSame('confirmed', $appointment->fresh()->status);
        $this->assertSame('succeeded', $payment->fresh()->status);
        $this->assertSame('pi_test_paid', $payment->fresh()->stripe_payment_intent_id);
        $this->assertSame('4242', $payment->fresh()->card_last_four);
        Notification::assertSentTo($patient, BookingConfirmed::class);
        Mail::assertQueued(PaymentReceipt::class, fn (PaymentReceipt $receipt): bool => $receipt->payment->is($payment)
            && $receipt->hasTo($patient->email)
            && ! $receipt->hasTo('jane@example.test'));
        (new PaymentReceipt($payment->fresh(['appointment.doctor', 'appointment.medicalCenter'])))
            ->assertSeeInHtml('Payment Receipt')
            ->assertSeeInHtml('LKR 4,500.00')
            ->assertSeeInHtml('LKR 1,000.00')
            ->assertSeeInHtml('4242')
            ->assertSeeInHtml('pi_test_paid');

        $this->actingAs($patient)
            ->get(route('booking.payment.success', ['session_id' => 'cs_test_paid']))
            ->assertRedirect(route('booking.confirmed', $appointment));

        Notification::assertSentTimes(BookingConfirmed::class, 1);
        Mail::assertQueuedCount(1);
    }

    public function test_unpaid_session_does_not_confirm_or_send_mail(): void
    {
        Notification::fake();
        Mail::fake();
        $appointment = Appointment::factory()->create(['status' => 'pending_payment']);
        $payment = Payment::factory()->for($appointment)->create([
            'amount' => 5000,
            'doctor_amount' => 4000,
            'clinic_amount' => 1000,
            'stripe_session_id' => 'cs_test_unpaid',
        ]);

        Http::fake(['api.stripe.com/v1/checkout/sessions/cs_test_unpaid*' => Http::response([
            'id' => 'cs_test_unpaid',
            'payment_status' => 'unpaid',
            'status' => 'open',
            'amount_total' => 500000,
            'currency' => 'lkr',
            'metadata' => ['appointment_id' => (string) $appointment->id],
            'payment_intent' => null,
        ])]);

        $this->actingAs($appointment->user)
            ->get(route('booking.payment.success', ['session_id' => 'cs_test_unpaid']))
            ->assertOk()
            ->assertSee('Payment not yet completed');

        $this->assertSame('pending_payment', $appointment->fresh()->status);
        $this->assertSame('pending', $payment->fresh()->status);
        Notification::assertNothingSent();
        Mail::assertNothingQueued();
    }

    public function test_mismatched_stripe_amount_is_rejected_without_confirming(): void
    {
        $appointment = Appointment::factory()->create(['status' => 'pending_payment']);
        $payment = Payment::factory()->for($appointment)->create([
            'amount' => 5000,
            'stripe_session_id' => 'cs_test_mismatch',
        ]);

        Http::fake(['api.stripe.com/v1/checkout/sessions/cs_test_mismatch*' => Http::response([
            'id' => 'cs_test_mismatch',
            'payment_status' => 'paid',
            'status' => 'complete',
            'amount_total' => 100,
            'currency' => 'lkr',
            'metadata' => ['appointment_id' => (string) $appointment->id],
            'payment_intent' => ['id' => 'pi_test_mismatch'],
        ])]);

        $this->actingAs($appointment->user)
            ->get(route('booking.payment.success', ['session_id' => 'cs_test_mismatch']))
            ->assertUnprocessable();

        $this->assertSame('pending_payment', $appointment->fresh()->status);
        $this->assertSame('pending', $payment->fresh()->status);
    }

    public function test_stripe_initialization_failure_releases_the_soft_reservation(): void
    {
        [$patient, $doctor, , $slot] = $this->bookingContext();
        Http::fake(['api.stripe.com/v1/checkout/sessions' => Http::response(['error' => ['message' => 'Unavailable']], 503)]);

        $this->actingAs($patient)
            ->post(route('booking.confirm', $doctor))
            ->assertRedirect(route('booking.review', $doctor));

        $appointment = Appointment::query()->sole();
        $this->assertSame('cancelled', $appointment->status);
        $this->assertFalse($slot->fresh()->is_booked);
        $this->assertNull($slot->fresh()->appointment_id);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_pending_checkout_cannot_use_the_confirmation_page(): void
    {
        $appointment = Appointment::factory()->create(['status' => 'pending_payment']);
        $payment = Payment::factory()->for($appointment)->create();

        $this->actingAs($payment->patient)
            ->get(route('booking.confirmed', $payment->appointment))
            ->assertNotFound();
    }

    public function test_cancelled_checkout_releases_reserved_slot_and_fails_payment(): void
    {
        $slot = DoctorAvailabilitySlot::factory()->create();
        $appointment = Appointment::factory()->for($slot->doctor)->create([
            'medical_center_id' => $slot->clinic_id,
            'doctor_availability_slot_id' => $slot->id,
            'status' => 'pending_payment',
        ]);
        $slot->update(['is_booked' => true, 'appointment_id' => $appointment->id]);
        $payment = Payment::factory()->for($appointment)->create();

        $this->actingAs($appointment->user)
            ->get(route('booking.payment.cancel', $appointment))
            ->assertOk()
            ->assertSee('Your slot has been released');

        $this->assertSame('cancelled', $appointment->fresh()->status);
        $this->assertSame('failed', $payment->fresh()->status);
        $this->assertFalse($slot->fresh()->is_booked);
        $this->assertNull($slot->fresh()->appointment_id);
    }

    public function test_patient_cannot_verify_another_patients_payment(): void
    {
        Http::fake();
        $payment = Payment::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('booking.payment.success', ['session_id' => $payment->stripe_session_id]))
            ->assertForbidden();

        Http::assertNothingSent();
    }

    /** @return array{User, Doctor, MedicalCenter, DoctorAvailabilitySlot} */
    private function bookingContext(): array
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create(['consultation_fee' => 4500]);
        $affiliation = DoctorClinicAffiliation::factory()->for($doctor)->create();
        $clinic = $affiliation->clinic;
        $clinic->update(['facility_fee' => 1000]);
        $slot = DoctorAvailabilitySlot::factory()->create([
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '10:30:00',
            'end_time' => '11:00:00',
        ]);

        $this->withSession([
            "booking.{$doctor->id}" => [
                'clinic' => ['clinic_id' => $clinic->id],
                'schedule' => ['appointment_date' => $slot->date->toDateString(), 'appointment_time' => '10:30', 'mode' => 'in_person'],
                'details' => ['patient_name' => 'Jane Patient', 'patient_phone' => '0771234567', 'patient_email' => 'jane@example.test'],
                'assessment' => ['skipped' => true, 'answers' => []],
            ],
        ]);

        return [$patient, $doctor, $clinic, $slot];
    }

    /** @return array<string, mixed> */
    private function paidStripeSession(): array
    {
        return [
            'id' => 'cs_test_paid',
            'payment_status' => 'paid',
            'status' => 'complete',
            'amount_total' => 550000,
            'currency' => 'lkr',
            'metadata' => ['appointment_id' => (string) Appointment::query()->sole()->id],
            'payment_intent' => [
                'id' => 'pi_test_paid',
                'payment_method' => ['type' => 'card', 'card' => ['last4' => '4242']],
            ],
        ];
    }
}
