<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ScreenerAnswerInterpreter
{
    /** @return array{score: int|null, confidence: string, needs_clarification: bool, reason: string, extracted_context: string} */
    public function interpret(string $question, string $answer, bool $isSelfHarmItem): array
    {
        $apiKey = config('services.gemini.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('Screener answer interpretation is not configured.');
        }

        $instructions = 'Convert the patient answer to 0=not at all, 1=several days, 2=more than half the days, or 3=nearly every day. Interpret indirect, hedged, story-shaped, or English/Sinhala/Tamil mixed-language answers from the full context instead of requiring literal scale words. A description of a sustained state during the two-week period, such as staying home alone and doing nothing, should receive the most reasonable frequency score with medium confidence; do not return null merely because an exact day count is absent. Use null only for a genuine deflection, refusal, off-topic response, or an answer with no usable frequency or duration signal. Capture clinically useful causes or details in extracted_context. ';

        if ($isSelfHarmItem) {
            $instructions .= 'This is the PHQ-9 self-harm item. Any direct or indirect indication of self-harm or suicidal thoughts must score at least 1 and must not be silently treated as unclear.';
        }

        $model = config('services.gemini.model');

        if (! is_string($model) || $model === '') {
            throw new RuntimeException('Screener answer interpretation model is not configured.');
        }

        $response = Http::withHeader('x-goog-api-key', $apiKey)->acceptJson()
            ->connectTimeout(5)->timeout(20)->retry([250, 750])
            ->post('https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($model).':generateContent', [
                'contents' => [['parts' => [['text' => $instructions."\n\nQuestion: {$question}\nPatient answer: {$answer}"]]]],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'responseJsonSchema' => $this->responseSchema(),
                ],
            ])->throw();

        $output = $response->json('candidates.0.content.parts.0.text');

        if (! is_string($output)) {
            throw new RuntimeException('The interpretation service returned an invalid response.');
        }

        /** @var array{score: int|null, confidence: string, needs_clarification: bool, reason: string, extracted_context: string} $result */
        $result = json_decode($output, true, flags: JSON_THROW_ON_ERROR);

        if ($isSelfHarmItem && $result['score'] === 0 && $this->mayIndicateSelfHarm($answer)) {
            $result['score'] = 1;
            $result['needs_clarification'] = false;
            $result['reason'] = 'Safety override: the answer may indicate self-harm thoughts.';
        }

        return $result;
    }

    /** @return array<string, mixed> */
    private function responseSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'score' => ['type' => ['integer', 'null'], 'minimum' => 0, 'maximum' => 3],
                'confidence' => ['type' => 'string', 'enum' => ['high', 'medium', 'low']],
                'needs_clarification' => ['type' => 'boolean'],
                'reason' => ['type' => 'string'],
                'extracted_context' => ['type' => 'string'],
            ],
            'required' => ['score', 'confidence', 'needs_clarification', 'reason', 'extracted_context'],
            'additionalProperties' => false,
        ];
    }

    private function mayIndicateSelfHarm(string $answer): bool
    {
        $answer = mb_strtolower($answer);

        if (preg_match('/\b(no|not|never|don.t|do not)\b.{0,20}\b(suicid|kill|hurt|harm|dead)/u', $answer) === 1) {
            return false;
        }

        foreach (['suicide', 'suicidal', 'kill myself', 'end my life', 'better off dead', 'hurt myself', 'harm myself', 'self-harm', 'self harm'] as $phrase) {
            if (str_contains($answer, $phrase)) {
                return true;
            }
        }

        return false;
    }
}
