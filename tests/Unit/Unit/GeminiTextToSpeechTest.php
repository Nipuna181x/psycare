<?php

namespace Tests\Unit\Unit;

use App\Services\GeminiTextToSpeech;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiTextToSpeechTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_it_generates_browser_playable_wave_audio(): void
    {
        config([
            'services.gemini.api_key' => 'test-key',
            'services.gemini.tts_model' => 'gemini-2.5-flash-preview-tts',
            'services.gemini.tts_voice' => 'Kore',
        ]);
        Http::preventStrayRequests();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['inlineData' => ['data' => base64_encode('pcm-audio')]]]]]],
        ])]);

        $audio = (new GeminiTextToSpeech)->synthesize('ඔබට කොහොමද?');

        $this->assertStringStartsWith('RIFF', $audio);
        $this->assertStringContainsString('WAVE', $audio);
        $this->assertStringEndsWith('pcm-audio', $audio);
        Http::assertSent(fn ($request): bool => $request['generationConfig']['responseModalities'] === ['AUDIO']
            && $request['generationConfig']['speechConfig']['voiceConfig']['prebuiltVoiceConfig']['voiceName'] === 'Kore');
    }
}
