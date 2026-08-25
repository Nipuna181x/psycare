<?php

namespace Tests\Unit\Unit;

use App\Services\AiCompanion;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiCompanionTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_it_sends_conversation_context_to_gemini(): void
    {
        config(['services.gemini.api_key' => 'test-key', 'services.gemini.model' => 'gemini-test']);
        Http::preventStrayRequests();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'I hear you. What would help tonight?']]]]],
        ])]);

        $response = (new AiCompanion)->respond('I still feel worried.', 'en', [
            ['role' => 'user', 'text' => 'I feel worried.'],
            ['role' => 'model', 'text' => 'That sounds tiring.'],
        ]);

        $this->assertSame('I hear you. What would help tonight?', $response);
        Http::assertSent(fn ($request): bool => count($request['contents']) === 3
            && str_contains($request['systemInstruction']['parts'][0]['text'], '2 to 3 short sentences')
            && str_contains($request['systemInstruction']['parts'][0]['text'], 'specific details')
            && str_contains($request['systemInstruction']['parts'][0]['text'], 'You are Lumi')
            && $request['generationConfig']['maxOutputTokens'] === 160);
    }

    public function test_it_returns_immediate_safety_guidance_without_calling_gemini(): void
    {
        Http::preventStrayRequests();

        $response = (new AiCompanion)->respond('I want to kill myself', 'en', []);

        $this->assertStringContainsString('1926', $response);
        Http::assertNothingSent();
    }
}
