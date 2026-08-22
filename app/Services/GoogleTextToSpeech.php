<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleTextToSpeech
{
    public function synthesize(string $text): string
    {
        $apiKey = config('services.google_tts.api_key');
        $voice = config('services.google_tts.voice');
        $language = config('services.google_tts.language');

        if (! is_string($apiKey) || $apiKey === '' || ! is_string($voice) || $voice === '' || ! is_string($language) || $language === '') {
            throw new RuntimeException('Google Text-to-Speech is not fully configured.');
        }

        $response = Http::withHeader('x-goog-api-key', $apiKey)->acceptJson()
            ->connectTimeout(5)->timeout(20)->retry([250, 750])
            ->post('https://texttospeech.googleapis.com/v1/text:synthesize', [
                'input' => ['text' => $text],
                'voice' => ['languageCode' => $language, 'name' => $voice],
                'audioConfig' => ['audioEncoding' => 'MP3', 'speakingRate' => 0.95],
            ])->throw();

        $audio = $response->json('audioContent');
        $decoded = is_string($audio) ? base64_decode($audio, true) : false;

        if ($decoded === false) {
            throw new RuntimeException('Google Text-to-Speech returned invalid audio data.');
        }

        return $decoded;
    }
}
