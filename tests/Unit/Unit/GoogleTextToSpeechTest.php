<?php

namespace Tests\Unit\Unit;

use App\Services\GoogleTextToSpeech;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleTextToSpeechTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_it_generates_mp3_audio_using_configured_google_voice(): void
    {
        config([
            'services.google_tts.api_key' => 'test-key',
            'services.google_tts.voice' => 'en-US-Neural2-F',
            'services.google_tts.language' => 'en-US',
        ]);
        Http::preventStrayRequests();
        Http::fake(['texttospeech.googleapis.com/*' => Http::response(['audioContent' => base64_encode('mp3-audio')])]);

        $audio = (new GoogleTextToSpeech)->synthesize('How are you feeling?');

        $this->assertSame('mp3-audio', $audio);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://texttospeech.googleapis.com/v1/text:synthesize'
            && $request->hasHeader('x-goog-api-key', 'test-key')
            && $request['voice']['name'] === 'en-US-Neural2-F'
            && $request['audioConfig']['audioEncoding'] === 'MP3');
    }
}
