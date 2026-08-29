<?php

namespace Tests\Feature;

use App\Http\Controllers\BookingController;
use App\Models\Doctor;
use App\Models\DoctorClinicAffiliation;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\ElevatedRiskFlagged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ElevatedRiskNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_is_notified_by_email_when_pre_assessment_is_elevated_risk(): void
    {
        Notification::fake();
        Mail::fake();
        $this->fakeSuccessfulStripeCheckout();

        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create(['consultation_fee' => 4000]);
        $affiliation = DoctorClinicAffiliation::factory()->for($doctor)->create();

        $date = now()->addDay()->toDateString();

        $this->actingAs($patient)->post(route('booking.schedule', $doctor), [
            'appointment_date' => $date,
            'appointment_time' => '10:30',
        ]);

        $this->actingAs($patient)->post(route('booking.details', $doctor), [
            'patient_name' => 'Jane Doe',
            'patient_age' => 29,
            'patient_gender' => 'female',
            'patient_phone' => '0771234567',
            'patient_email' => 'jane@example.com',
            'reason' => 'Feeling anxious lately',
        ]);

        $answers = collect(BookingController::ASSESSMENT_QUESTIONS)
            ->map(fn (array $question): array => [
                'key' => $question['key'],
                'instrument' => $question['instrument'],
                'question' => $question['question'],
                'score' => $question['key'] === 'phq_9' ? 1 : 0,
                'answer' => '',
                'confidence' => 'manual',
                'extracted_context' => '',
            ])
            ->all();

        $this->actingAs($patient)->post(route('booking.assessment', $doctor), [
            'answers' => $answers,
            'open_notes' => '',
        ]);

        $this->actingAs($patient)->post(route('booking.confirm', $doctor));

        $appointment = $patient->appointments()->firstOrFail();

        $this->actingAs($patient)->get(route('booking.payment.success', [
            'session_id' => 'cs_test_risk_notification',
        ]));

        $this->assertTrue($appointment->requiresCrisisEscalation());

        Notification::assertSentTo(
            $doctor,
            ElevatedRiskFlagged::class,
            fn (ElevatedRiskFlagged $notification) => $notification->appointment->is($appointment)
        );
    }

    public function test_doctor_is_not_notified_when_pre_assessment_is_not_elevated_risk(): void
    {
        Notification::fake();
        Mail::fake();
        $this->fakeSuccessfulStripeCheckout();

        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create(['consultation_fee' => 4000]);
        DoctorClinicAffiliation::factory()->for($doctor)->create();

        $date = now()->addDay()->toDateString();

        $this->actingAs($patient)->post(route('booking.schedule', $doctor), [
            'appointment_date' => $date,
            'appointment_time' => '10:30',
        ]);

        $this->actingAs($patient)->post(route('booking.details', $doctor), [
            'patient_name' => 'Jane Doe',
            'patient_age' => 29,
            'patient_gender' => 'female',
            'patient_phone' => '0771234567',
            'patient_email' => 'jane@example.com',
            'reason' => 'Feeling anxious lately',
        ]);

        $answers = collect(BookingController::ASSESSMENT_QUESTIONS)
            ->map(fn (array $question): array => [
                'key' => $question['key'],
                'instrument' => $question['instrument'],
                'question' => $question['question'],
                'score' => 0,
                'answer' => '',
                'confidence' => 'manual',
                'extracted_context' => '',
            ])
            ->all();

        $this->actingAs($patient)->post(route('booking.assessment', $doctor), [
            'answers' => $answers,
            'open_notes' => '',
        ]);

        $this->actingAs($patient)->post(route('booking.confirm', $doctor));

        $this->actingAs($patient)->get(route('booking.payment.success', [
            'session_id' => 'cs_test_risk_notification',
        ]));

        Notification::assertNotSentTo($doctor, ElevatedRiskFlagged::class);
    }

    private function fakeSuccessfulStripeCheckout(): void
    {
        Http::fake(function (Request $request) {
            if ($request->method() === 'POST') {
                return Http::response([
                    'id' => 'cs_test_risk_notification',
                    'url' => 'https://checkout.stripe.com/c/pay/risk-notification',
                    'expires_at' => now()->addMinutes(30)->timestamp,
                ]);
            }

            $payment = Payment::query()->sole();

            return Http::response([
                'id' => 'cs_test_risk_notification',
                'payment_status' => 'paid',
                'status' => 'complete',
                'amount_total' => (int) round(((float) $payment->amount) * 100),
                'currency' => $payment->currency,
                'metadata' => ['appointment_id' => (string) $payment->appointment_id],
                'payment_intent' => ['id' => 'pi_test_risk_notification'],
            ]);
        });
    }
}
