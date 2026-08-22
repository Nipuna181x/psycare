<?php

namespace App\Http\Controllers;

use App\Http\Requests\AiCompanionMessageRequest;
use App\Http\Requests\StartAiCompanionRequest;
use App\Services\AiCompanion;
use App\Services\GeminiTextToSpeech;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Throwable;

class AiCompanionController extends Controller
{
    public function show(): View
    {
        return view('ai-companion.show');
    }

    public function start(StartAiCompanionRequest $request, GeminiTextToSpeech $textToSpeech): JsonResponse
    {
        $language = $request->validated('language');

        $greeting = $language === 'si'
            ? 'හේයි, මගේ නම ආශා. මම ඔබට උදව් කිරීමට මෙහි සිටිනවා. ඔබේ හිතේ තියෙන දේ මට කියන්න.'
            : "Hey, my name is Asha. I'm here to help you. Tell me what's on your mind.";

        return $this->spokenResponse($greeting, $language, $textToSpeech);
    }

    public function respond(AiCompanionMessageRequest $request, AiCompanion $companion, GeminiTextToSpeech $textToSpeech): JsonResponse
    {
        $validated = $request->validated();

        try {
            $response = $companion->respond($validated['message'], $validated['language'], $validated['history']);

            return $this->spokenResponse($response, $validated['language'], $textToSpeech);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'The companion is temporarily unavailable. Please try again.'], 503);
        }
    }

    private function spokenResponse(string $response, string $language, GeminiTextToSpeech $textToSpeech): JsonResponse
    {
        try {
            $audio = $textToSpeech->synthesize($response, $language);

            return response()->json([
                'response' => $response,
                'audio' => base64_encode($audio),
                'audio_type' => 'audio/wav',
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'Asha is temporarily unavailable. Please try again.'], 503);
        }
    }
}
