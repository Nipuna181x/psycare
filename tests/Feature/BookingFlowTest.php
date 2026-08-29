<?php

namespace Tests\Feature;

use App\Http\Controllers\BookingController;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorClinicAffiliation;
use App\Models\MedicalCenter;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_starting_a_booking(): void
    {
        $doctor = Doctor::factory()->create();

        $response = $this->get(route('booking.schedule', $doctor));

        $response->assertRedirect(route('login'));
    }

    public function test_doctor_without_a_clinic_affiliation_cannot_be_booked(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();

        $response = $this->actingAs($patient)->get(route('booking.schedule', $doctor));

        $response->assertNotFound();
    }

    public function test_patient_can_complete_the_full_booking_flow(): void
    {
        Mail::fake();
        Notification::fake();
        $this->fakeSuccessfulStripeCheckout();

        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create(['consultation_fee' => 4000]);
        $affiliation = DoctorClinicAffiliation::factory()->for($doctor)->create();

        $date = now()->addDay()->toDateString();

        $this->actingAs($patient)
            ->post(route('booking.schedule', $doctor), [
                'appointment_date' => $date,
                'appointment_time' => '10:30',
                'mode' => 'in_person',
            ])
            ->assertRedirect(route('booking.details', $doctor));

        $this->actingAs($patient)
            ->post(route('booking.details', $doctor), [
                'patient_name' => 'Jane Doe',
                'patient_age' => 29,
                'patient_gender' => 'female',
                'patient_phone' => '0771234567',
                'patient_email' => 'jane@example.com',
                'reason' => 'Feeling anxious lately',
            ])
            ->assertRedirect(route('booking.assessment', $doctor));

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

        $this->actingAs($patient)
            ->post(route('booking.assessment', $doctor), [
                'answers' => $answers,
                'open_notes' => 'Work has been stressful.',
            ])
            ->assertRedirect(route('booking.review', $doctor));

        $this->actingAs($patient)
            ->get(route('booking.review', $doctor))
            ->assertOk()
            ->assertSee('Jane Doe')
            ->assertSee('session fee')
            ->assertSee($affiliation->clinic->name.' facility fee')
            ->assertSee('LKR '.number_format(4000))
            ->assertSee('LKR '.number_format(4000 + $affiliation->clinic->facility_fee));

        $checkoutResponse = $this->actingAs($patient)->post(route('booking.confirm', $doctor));

        $checkoutResponse->assertRedirect('https://checkout.stripe.com/c/pay/booking-flow');

        $appointment = $patient->appointments()->firstOrFail();

        $confirmResponse = $this->actingAs($patient)->get(route('booking.payment.success', [
            'session_id' => 'cs_test_booking_flow',
        ]));

        $this->assertDatabaseHas('appointments', [
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'medical_center_id' => $affiliation->clinic_id,
            'patient_name' => 'Jane Doe',
            'appointment_date' => $date,
            'status' => 'confirmed',
            'phq9_total' => 1,
            'gad7_total' => 0,
            'self_harm_flag' => true,
            'requires_immediate_escalation' => true,
            'doctor_fee_charged' => 4000,
            'clinic_fee_charged' => $affiliation->clinic->facility_fee,
        ]);

        $confirmResponse->assertRedirect(route('booking.confirmed', $appointment));

        $this->actingAs($patient)
            ->get(route('booking.confirmed', $appointment))
            ->assertOk();
    }

    public function test_patient_can_skip_the_voice_screening_step(): void
    {
        Mail::fake();
        Notification::fake();
        $this->fakeSuccessfulStripeCheckout();

        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create(['consultation_fee' => 4000]);
        DoctorClinicAffiliation::factory()->for($doctor)->create();
        $date = now()->addDay()->toDateString();

        $this->actingAs($patient)
            ->post(route('booking.schedule', $doctor), [
                'appointment_date' => $date,
                'appointment_time' => '10:30',
                'mode' => 'in_person',
            ])
            ->assertRedirect(route('booking.details', $doctor));

        $this->actingAs($patient)
            ->post(route('booking.details', $doctor), [
                'patient_name' => 'Jane Doe',
                'patient_phone' => '0771234567',
            ])
            ->assertRedirect(route('booking.assessment', $doctor));

        $this->actingAs($patient)
            ->post(route('booking.assessment', $doctor), ['skipped' => true])
            ->assertRedirect(route('booking.review', $doctor));

        $this->actingAs($patient)
            ->get(route('booking.review', $doctor))
            ->assertOk()
            ->assertSee('chose to skip the screening');

        $checkoutResponse = $this->actingAs($patient)->post(route('booking.confirm', $doctor));

        $checkoutResponse->assertRedirect('https://checkout.stripe.com/c/pay/booking-flow');

        $appointment = $patient->appointments()->firstOrFail();
        $confirmResponse = $this->actingAs($patient)->get(route('booking.payment.success', [
            'session_id' => 'cs_test_booking_flow',
        ]));

        $this->assertDatabaseHas('appointments', [
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'status' => 'confirmed',
            'phq9_total' => null,
            'gad7_total' => null,
            'pre_assessment_risk_level' => null,
            'self_harm_flag' => false,
            'requires_immediate_escalation' => false,
            'screener_completed_at' => null,
        ]);

        $confirmResponse->assertRedirect(route('booking.confirmed', $appointment));
    }

    public function test_booking_step_cannot_be_skipped(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        DoctorClinicAffiliation::factory()->for($doctor)->create();

        $response = $this->actingAs($patient)->get(route('booking.review', $doctor));

        $response->assertRedirect(route('booking.schedule', $doctor));
    }

    public function test_cannot_book_an_already_taken_slot(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        $affiliation = DoctorClinicAffiliation::factory()->for($doctor)->create();
        $date = now()->addDay()->toDateString();

        Appointment::factory()->for($doctor)->create([
            'medical_center_id' => $affiliation->clinic_id,
            'appointment_date' => $date,
            'appointment_time' => '10:30',
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($patient)->post(route('booking.schedule', $doctor), [
            'appointment_date' => $date,
            'appointment_time' => '10:30',
            'mode' => 'in_person',
        ]);

        $response->assertSessionHasErrors('appointment_time');
    }

    public function test_patient_must_choose_a_clinic_when_doctor_has_multiple_active_affiliations(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        DoctorClinicAffiliation::factory()->for($doctor)->count(2)->create();

        $response = $this->actingAs($patient)->get(route('booking.schedule', $doctor));

        $response->assertRedirect(route('booking.clinic', $doctor));
    }

    public function test_patient_can_select_a_clinic_when_doctor_has_multiple_active_affiliations(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        $affiliations = DoctorClinicAffiliation::factory()->for($doctor)->count(2)->create();
        $chosen = $affiliations->first();

        $this->actingAs($patient)->get(route('booking.clinic', $doctor))
            ->assertOk()
            ->assertSee($chosen->clinic->name);

        $this->actingAs($patient)
            ->post(route('booking.clinic', $doctor), ['clinic_id' => $chosen->clinic_id])
            ->assertRedirect(route('booking.schedule', $doctor));

        $this->actingAs($patient)->get(route('booking.schedule', $doctor))->assertOk();
    }

    public function test_clinic_step_is_skipped_when_doctor_has_a_single_active_affiliation(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        DoctorClinicAffiliation::factory()->for($doctor)->create();

        $response = $this->actingAs($patient)->get(route('booking.clinic', $doctor));

        $response->assertRedirect(route('booking.schedule', $doctor));
    }

    public function test_stale_clinic_selection_from_an_earlier_session_does_not_bypass_clinic_selection(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        DoctorClinicAffiliation::factory()->for($doctor)->count(2)->create();

        // Simulate a leftover/invalid clinic_id lingering in the session from an
        // abandoned booking or a since-ended affiliation — this must not silently
        // bypass clinic selection on a fresh visit to the schedule step.
        session(["booking.{$doctor->id}.clinic" => ['clinic_id' => 999999]]);

        $response = $this->actingAs($patient)->get(route('booking.schedule', $doctor));

        $response->assertRedirect(route('booking.clinic', $doctor));
    }

    public function test_booking_is_blocked_at_checkout_when_doctor_has_no_price_set(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create(['consultation_fee' => null]);
        DoctorClinicAffiliation::factory()->for($doctor)->create();

        $this->completeBookingStepsUpToAssessment($patient, $doctor);

        $response = $this->actingAs($patient)->get(route('booking.review', $doctor));

        $response->assertRedirect(route('booking.schedule', $doctor));
        $this->assertSame(0, Appointment::query()->count());

        $this->actingAs($patient)->post(route('booking.confirm', $doctor));
        $this->assertSame(0, Appointment::query()->count());
    }

    public function test_booking_is_blocked_at_checkout_when_clinic_has_no_price_set(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create(['consultation_fee' => 4000]);
        DoctorClinicAffiliation::factory()->for($doctor)->create(['clinic_id' => MedicalCenter::factory()->approved()->create(['facility_fee' => null])->id]);

        $this->completeBookingStepsUpToAssessment($patient, $doctor);

        $response = $this->actingAs($patient)->get(route('booking.review', $doctor));

        $response->assertRedirect(route('booking.schedule', $doctor));
        $this->assertSame(0, Appointment::query()->count());
    }

    /**
     * Drive the wizard from the schedule step through the end of the voice
     * assessment step (skipped), leaving the session ready to load `review`.
     */
    private function completeBookingStepsUpToAssessment(User $patient, Doctor $doctor): void
    {
        $date = now()->addDay()->toDateString();

        $this->actingAs($patient)->post(route('booking.schedule', $doctor), [
            'appointment_date' => $date,
            'appointment_time' => '10:30',
            'mode' => 'in_person',
        ]);

        $this->actingAs($patient)->post(route('booking.details', $doctor), [
            'patient_name' => 'Jane Doe',
            'patient_phone' => '0771234567',
        ]);

        $this->actingAs($patient)->post(route('booking.assessment', $doctor), ['skipped' => true]);
    }

    private function fakeSuccessfulStripeCheckout(): void
    {
        Http::fake(function (Request $request) {
            if ($request->method() === 'POST') {
                return Http::response([
                    'id' => 'cs_test_booking_flow',
                    'url' => 'https://checkout.stripe.com/c/pay/booking-flow',
                    'expires_at' => now()->addMinutes(30)->timestamp,
                ]);
            }

            $payment = Payment::query()->sole();

            return Http::response([
                'id' => 'cs_test_booking_flow',
                'payment_status' => 'paid',
                'status' => 'complete',
                'amount_total' => (int) round(((float) $payment->amount) * 100),
                'currency' => $payment->currency,
                'metadata' => ['appointment_id' => (string) $payment->appointment_id],
                'payment_intent' => ['id' => 'pi_test_booking_flow'],
            ]);
        });
    }
}
