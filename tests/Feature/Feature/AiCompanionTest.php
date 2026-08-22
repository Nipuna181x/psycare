<?php

namespace Tests\Feature\Feature;

use App\Models\AiCompanionSession;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiCompanionTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_from_companion(): void
    {
        $this->get(route('ai-companion.show'))->assertRedirect(route('login'));
    }

    public function test_patient_can_open_voice_companion(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('ai-companion.show'))
            ->assertOk()
            ->assertSee('Asha')
            ->assertDontSee('<textarea', false)
            ->assertDontSee('type="text"', false);
    }

    public function test_patient_receives_text_and_audio_response(): void
    {
        config([
            'services.gemini.api_key' => 'test-key',
            'services.gemini.model' => 'gemini-test',
            'services.gemini.tts_model' => 'gemini-tts-test',
            'services.gemini.tts_voice' => 'Kore',
        ]);
        Http::preventStrayRequests();
        Http::fakeSequence('generativelanguage.googleapis.com/*')
            ->push(['candidates' => [['content' => ['parts' => [['text' => 'That sounds difficult. What feels hardest right now?']]]]]])
            ->push(['candidates' => [['content' => ['parts' => [['inlineData' => ['data' => base64_encode('audio')]]]]]]]);

        $patient = User::factory()->create();
        $session = AiCompanionSession::factory()->for($patient)->create();

        $this->actingAs($patient)
            ->postJson(route('ai-companion.respond'), [
                'message' => 'I have had a difficult day.',
                'language' => 'en',
                'session_id' => $session->public_id,
            ])->assertOk()->assertJson([
                'response' => 'That sounds difficult. What feels hardest right now?',
                'audio_type' => 'audio/wav',
            ])->assertJsonPath('audio', fn (string $audio): bool => str_starts_with(base64_decode($audio), 'RIFF'));

        Http::assertSentCount(2);
        $this->assertSame(['user', 'model'], $session->turns()->pluck('role')->all());
        $this->assertNotSame('I have had a difficult day.', $session->turns()->first()->getRawOriginal('content'));
    }

    public function test_asha_introduces_herself_when_session_starts(): void
    {
        config([
            'services.gemini.api_key' => 'test-key',
            'services.gemini.tts_model' => 'gemini-tts-test',
            'services.gemini.tts_voice' => 'Kore',
        ]);
        Http::preventStrayRequests();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['inlineData' => ['data' => base64_encode('greeting-audio')]]]]]],
        ])]);

        $patient = User::factory()->create();
        $this->actingAs($patient)
            ->postJson(route('ai-companion.start'), ['language' => 'en', 'consent' => true])
            ->assertOk()
            ->assertJsonPath('response', "Hey, my name is Asha. I'm here to help you. Tell me what's on your mind.")
            ->assertJsonPath('audio_type', 'audio/wav')
            ->assertJsonStructure(['session_id']);

        $this->assertModelExists($patient->aiCompanionSessions()->first());
    }
}
