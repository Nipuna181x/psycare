<?php

namespace Tests\Feature\Console;

use App\Mail\PaymentReceipt;
use App\Models\Appointment;
use App\Models\DoctorAvailabilitySlot;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReconcilePendingPaymentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_paid_checkout_is_confirmed_instead_of_releasing_the_slot(): void
    {
        Mail::fake();
        Notification::fake();
        $slot = DoctorAvailabilitySlot::factory()->create();
        $appointment = Appointment::factory()->for($slot->doctor)->create([
            'medical_center_id' => $slot->clinic_id,
            'doctor_availability_slot_id' => $slot->id,
            'status' => 'pending_payment',
        ]);
        $slot->update(['is_booked' => true, 'appointment_id' => $appointment->id]);
        $payment = Payment::factory()->for($appointment)->create([
            'stripe_session_id' => 'cs_test_paid_abandoned',
            'expires_at' => now()->subMinute(),
        ]);

        Http::fake(Http::response([
            'id' => 'cs_test_paid_abandoned',
            'payment_status' => 'paid',
            'status' => 'complete',
            'amount_total' => (int) round((float) $payment->amount * 100),
            'currency' => 'lkr',
            'metadata' => ['appointment_id' => (string) $appointment->id],
            'payment_intent' => ['id' => 'pi_test_paid_abandoned'],
        ]));

        $this->artisan('payments:reconcile-pending')
            ->expectsOutput('Reconciled payments: 1 succeeded, 0 released.')
            ->assertSuccessful();

        $this->assertSame('succeeded', $payment->fresh()->status);
        $this->assertSame('confirmed', $appointment->fresh()->status);
        $this->assertTrue($slot->fresh()->is_booked);
        $this->assertSame($appointment->id, $slot->fresh()->appointment_id);
        Mail::assertQueued(PaymentReceipt::class);
    }

    public function test_expired_unpaid_checkout_is_expired_at_stripe_and_releases_slot(): void
    {
        $slot = DoctorAvailabilitySlot::factory()->create();
        $appointment = Appointment::factory()->for($slot->doctor)->create([
            'medical_center_id' => $slot->clinic_id,
            'doctor_availability_slot_id' => $slot->id,
            'status' => 'pending_payment',
        ]);
        $slot->update(['is_booked' => true, 'appointment_id' => $appointment->id]);
        $payment = Payment::factory()->for($appointment)->create([
            'stripe_session_id' => 'cs_test_abandoned',
            'expires_at' => now()->subMinute(),
        ]);

        Http::fake(fn (Request $request) => $request->method() === 'GET'
            ? Http::response([
                'id' => 'cs_test_abandoned',
                'payment_status' => 'unpaid',
                'status' => 'open',
                'amount_total' => (int) round((float) $payment->amount * 100),
                'currency' => 'lkr',
                'metadata' => ['appointment_id' => (string) $appointment->id],
            ])
            : Http::response(['id' => 'cs_test_abandoned', 'status' => 'expired']));

        $this->artisan('payments:reconcile-pending')
            ->expectsOutput('Reconciled payments: 0 succeeded, 1 released.')
            ->assertSuccessful();

        $this->assertSame('failed', $payment->fresh()->status);
        $this->assertSame('cancelled', $appointment->fresh()->status);
        $this->assertFalse($slot->fresh()->is_booked);
        $this->assertNull($slot->fresh()->appointment_id);
        Http::assertSent(fn (Request $request): bool => $request->method() === 'POST' && str_ends_with($request->url(), '/expire'));
    }

    public function test_unexpired_payment_is_left_untouched(): void
    {
        $payment = Payment::factory()->create(['expires_at' => now()->addMinutes(10)]);

        Http::fake();

        $this->artisan('payments:reconcile-pending')
            ->expectsOutput('Reconciled payments: 0 succeeded, 0 released.')
            ->assertSuccessful();

        $this->assertSame('pending', $payment->fresh()->status);
        Http::assertNothingSent();
    }
}
