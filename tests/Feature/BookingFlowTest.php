<?php

namespace Tests\Feature;

use App\Http\Controllers\BookingController;
use App\Models\Appointment;
use App\Models\Doctor;
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

    public function test_patient_can_complete_the_full_booking_flow(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create(['consultation_fee' => 4000]);

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
                'question' => $question['question'],
                'answer' => $question['key'] === 'notes' ? '' : 'No',
            ])
            ->all();

        $this->actingAs($patient)
            ->post(route('booking.assessment', $doctor), [
                'mood_rating' => 4,
                'answers' => $answers,
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
            'medical_center_id' => $doctor->medical_center_id,
            'patient_name' => 'Jane Doe',
            'appointment_date' => $date,
            'status' => 'confirmed',
        ]);

        $appointment = $patient->appointments()->first();
        $confirmResponse->assertRedirect(route('booking.confirmed', $appointment));

        $this->actingAs($patient)
            ->get(route('booking.confirmed', $appointment))
            ->assertOk();
    }

    public function test_booking_step_cannot_be_skipped(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();

        $response = $this->actingAs($patient)->get(route('booking.review', $doctor));

        $response->assertRedirect(route('booking.schedule', $doctor));
    }

    public function test_cannot_book_an_already_taken_slot(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        $date = now()->addDay()->toDateString();

        Appointment::factory()->for($doctor)->create([
            'medical_center_id' => $doctor->medical_center_id,
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
}
