<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiTextToSpeech
{
    public function synthesize(string $text, string $language): string
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.tts_model');
        $voice = config('services.gemini.tts_voice');

        if (! is_string($apiKey) || $apiKey === '' || ! is_string($model) || $model === '' || ! is_string($voice) || $voice === '') {
            throw new RuntimeException('Gemini Text-to-Speech is not fully configured.');
        }

        $languageName = $language === 'si' ? 'Sinhala' : 'English';
        $response = Http::withHeader('x-goog-api-key', $apiKey)->acceptJson()
            ->connectTimeout(5)->timeout(60)->retry([500, 1000])
            ->post('https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($model).':generateContent', [
                'contents' => [['parts' => [['text' => "Speak the following {$languageName} response exactly as written. Use a warm, calm, natural Sri Lankan voice and a gentle conversational pace. Do not add or remove words: {$text}"]]]],
                'generationConfig' => [
                    'responseModalities' => ['AUDIO'],
                    'speechConfig' => ['voiceConfig' => ['prebuiltVoiceConfig' => ['voiceName' => $voice]]],
                ],
            ])->throw();

        $encodedAudio = $response->json('candidates.0.content.parts.0.inlineData.data');
        $pcmAudio = is_string($encodedAudio) ? base64_decode($encodedAudio, true) : false;

        if ($pcmAudio === false) {
            throw new RuntimeException('Gemini Text-to-Speech returned invalid audio data.');
        }

        return $this->toWave($pcmAudio);
    }

    private function toWave(string $pcmAudio): string
    {
        $dataLength = strlen($pcmAudio);

        return 'RIFF'.pack('V', 36 + $dataLength).'WAVEfmt '.pack('VvvVVvv', 16, 1, 1, 24000, 48000, 2, 16).'data'.pack('V', $dataLength).$pcmAudio;
    }
}
