<?php

namespace Tests\Feature;

use App\Http\Controllers\BookingController;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorClinicAffiliation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertSee('Jane Doe');

        $confirmResponse = $this->actingAs($patient)->post(route('booking.confirm', $doctor));

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
        ]);

        $appointment = $patient->appointments()->first();
        $confirmResponse->assertRedirect(route('booking.confirmed', $appointment));

        $this->actingAs($patient)
            ->get(route('booking.confirmed', $appointment))
            ->assertOk();
    }

    public function test_patient_can_skip_the_voice_screening_step(): void
    {
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

        $confirmResponse = $this->actingAs($patient)->post(route('booking.confirm', $doctor));

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

        $appointment = $patient->appointments()->first();
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
}
