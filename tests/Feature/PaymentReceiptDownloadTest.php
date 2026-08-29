<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentReceiptDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_download_their_succeeded_payment_receipt(): void
    {
        $appointment = Appointment::factory()->create(['status' => 'confirmed']);
        $payment = Payment::factory()->for($appointment)->succeeded()->create();

        $response = $this->actingAs($appointment->user)
            ->get(route('payments.receipt.download', $payment));

        $response->assertOk()->assertDownload(mb_strtolower($payment->reference()).'-receipt.pdf');
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString(
            'Payment ID: '.$payment->reference(),
            view('payments.receipt-pdf', ['payment' => $payment->load('appointment.doctor', 'appointment.medicalCenter')])->render(),
        );
    }

    public function test_payment_id_and_receipt_link_are_visible_after_payment(): void
    {
        $appointment = Appointment::factory()->create(['status' => 'confirmed']);
        $payment = Payment::factory()->for($appointment)->succeeded()->create();

        $this->actingAs($appointment->user)
            ->get(route('booking.confirmed', $appointment))
            ->assertOk()
            ->assertSee($payment->reference())
            ->assertSee(route('payments.receipt.download', $payment));

        $this->actingAs($appointment->user)
            ->get(route('appointments.index'))
            ->assertOk()
            ->assertSee($payment->reference())
            ->assertSee(route('payments.receipt.download', $payment));
    }

    public function test_patient_cannot_download_another_patients_receipt(): void
    {
        $payment = Payment::factory()->succeeded()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('payments.receipt.download', $payment))
            ->assertForbidden();
    }

    public function test_non_succeeded_payment_has_no_downloadable_receipt(): void
    {
        $payment = Payment::factory()->create(['status' => 'failed']);

        $this->actingAs($payment->patient)
            ->get(route('payments.receipt.download', $payment))
            ->assertNotFound();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $payment = Payment::factory()->succeeded()->create();

        $this->get(route('payments.receipt.download', $payment))
            ->assertRedirect(route('login'));
    }
}
