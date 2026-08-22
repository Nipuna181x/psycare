<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiTextToSpeech
{
    public function synthesize(string $text): string
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.tts_model');
        $voice = config('services.gemini.tts_voice');

        if (! is_string($apiKey) || $apiKey === '' || ! is_string($model) || $model === '' || ! is_string($voice) || $voice === '') {
            throw new RuntimeException('Gemini Text-to-Speech is not fully configured.');
        }

        $response = Http::withHeader('x-goog-api-key', $apiKey)->acceptJson()
            ->connectTimeout(5)->timeout(60)->retry([500, 1000])
            ->post('https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($model).':generateContent', [
                'contents' => [['parts' => [['text' => 'Read this Sinhala health screening question exactly as written, in a warm, calm, clear Sri Lankan voice. Do not add or remove words: '.$text]]]],
                'generationConfig' => [
                    'responseModalities' => ['AUDIO'],
                    'speechConfig' => ['voiceConfig' => ['prebuiltVoiceConfig' => ['voiceName' => $voice]]],
                ],
            ])->throw();

        $audio = $response->json('candidates.0.content.parts.0.inlineData.data');
        $pcm = is_string($audio) ? base64_decode($audio, true) : false;

        if ($pcm === false) {
            throw new RuntimeException('Gemini Text-to-Speech returned invalid audio data.');
        }

        return $this->waveFile($pcm);
    }

    private function waveFile(string $pcm): string
    {
        $dataLength = strlen($pcm);

        return 'RIFF'.pack('V', 36 + $dataLength).'WAVEfmt '.pack('VvvVVvv', 16, 1, 1, 24000, 48000, 2, 16).'data'.pack('V', $dataLength).$pcm;
    }
}
