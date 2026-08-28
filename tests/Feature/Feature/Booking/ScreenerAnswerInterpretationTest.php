<?php

namespace Tests\Feature\Feature\Booking;

use App\Models\Doctor;
use App\Models\DoctorClinicAffiliation;
use App\Models\ScreenerDraft;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ScreenerAnswerInterpretationTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_patient_can_interpret_a_natural_language_answer(): void
    {
        config(['services.gemini.api_key' => 'test-key', 'services.gemini.model' => 'gemini-3.5-flash']);
        Http::preventStrayRequests();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => json_encode([
                'score' => 3, 'confidence' => 'high', 'needs_clarification' => false,
                'reason' => 'The patient said every day.', 'extracted_context' => 'Attributes tiredness to work stress.',
            ])]]]]],
        ])]);
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        DoctorClinicAffiliation::factory()->for($doctor)->create();

        $response = $this->actingAs($patient)->postJson(route('booking.assessment.interpret', $doctor), [
            'key' => 'phq_4',
            'answer' => 'Every single day, work has left me exhausted.',
            'language' => 'si',
        ]);

        $response->assertOk()->assertJson(['score' => 3, 'needs_clarification' => false]);
        $draft = ScreenerDraft::query()->where('user_id', $patient->id)->where('doctor_id', $doctor->id)->firstOrFail();
        $this->assertSame(4, $draft->current_question);
        $this->assertSame('phq_4', $draft->answers[0]['key']);
        $this->assertSame('si', $draft->language);
        $this->assertSame('Attributes tiredness to work stress.', $draft->answers[0]['extracted_context']);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent'
            && $request->hasHeader('x-goog-api-key', 'test-key')
            && $request['generationConfig']['responseMimeType'] === 'application/json'
            && $request['generationConfig']['responseJsonSchema']['type'] === 'object');

        $this->withSession([
            "booking.{$doctor->id}.schedule" => ['appointment_date' => now()->addDay()->toDateString()],
            "booking.{$doctor->id}.details" => ['patient_name' => $patient->name],
        ])->get(route('booking.assessment', $doctor))
            ->assertOk()
            ->assertSee('let index = Math.min(4, questions.length - 1);', false)
            ->assertSee('Every single day, work has left me exhausted.');
    }

    public function test_interpretation_fails_closed_when_service_is_not_configured(): void
    {
        config(['services.gemini.api_key' => null]);
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        DoctorClinicAffiliation::factory()->for($doctor)->create();

        $this->actingAs($patient)->postJson(route('booking.assessment.interpret', $doctor), [
            'key' => 'gad_1',
            'answer' => 'I am not sure.',
            'language' => 'en',
        ])->assertStatus(503)->assertJson(['needs_clarification' => true]);
    }
}
