<?php

namespace App\Http\Controllers;

use App\Http\Requests\AiCompanionMessageRequest;
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

    public function respond(AiCompanionMessageRequest $request, AiCompanion $companion, GeminiTextToSpeech $textToSpeech): JsonResponse
    {
        $validated = $request->validated();

        try {
            $response = $companion->respond($validated['message'], $validated['language'], $validated['history']);
            $audio = $textToSpeech->synthesize($response, $validated['language']);

            return response()->json([
                'response' => $response,
                'audio' => base64_encode($audio),
                'audio_type' => 'audio/wav',
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'The companion is temporarily unavailable. Please try again.'], 503);
        }
    }
}
