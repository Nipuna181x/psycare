<?php

namespace App\Http\Controllers;

use App\Http\Requests\AiCompanionMessageRequest;
use App\Http\Requests\FinishAiCompanionRequest;
use App\Http\Requests\StartAiCompanionRequest;
use App\Models\AiCompanionSession;
use App\Models\Appointment;
use App\Models\PatientNlpReport;
use App\Services\AiCompanion;
use App\Services\GeminiTextToSpeech;
use App\Services\GoogleTextToSpeech;
use App\Services\PatientNlpClassifier;
use App\Services\PatientNlpReportGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class AiCompanionController extends Controller
{
    public function show(): View
    {
        return view('ai-companion.show');
    }

    public function start(StartAiCompanionRequest $request, GoogleTextToSpeech $textToSpeech, GeminiTextToSpeech $sinhalaTextToSpeech): JsonResponse
    {
        $language = $request->validated('language');

        $greeting = $language === 'si'
            ? 'හායි, මම ලුමී. හැඟීම් ප්‍රකාශ කිරීමට මිතුරෙක්.'
            : "Hi, I'm Lumi, a friend to express how you feel.";

        $spokenResponse = $this->spokenResponse($greeting, $language, $textToSpeech, $sinhalaTextToSpeech);

        if ($spokenResponse->isServerError()) {
            return $spokenResponse;
        }

        $session = AiCompanionSession::create([
            'public_id' => Str::uuid(),
            'user_id' => $request->user()->id,
            'language' => $language,
            'consented_at' => now(),
        ]);

        return $spokenResponse->setData([...$spokenResponse->getData(true), 'session_id' => $session->public_id]);
    }

    public function respond(AiCompanionMessageRequest $request, AiCompanion $companion, GoogleTextToSpeech $textToSpeech, GeminiTextToSpeech $sinhalaTextToSpeech): JsonResponse
    {
        $validated = $request->validated();
        $session = $this->patientSession($validated['session_id'], $request->user()->id);
        abort_if($session->ended_at !== null || $session->language !== $validated['language'], 409);

        try {
            $history = $session->turns()->latest('sequence')->limit(12)->get()->reverse()->map(fn ($turn): array => [
                'role' => $turn->role,
                'text' => $turn->content,
            ])->values()->all();
            $response = $companion->respond($validated['message'], $validated['language'], $history);
            $spokenResponse = $this->spokenResponse($response, $validated['language'], $textToSpeech, $sinhalaTextToSpeech);

            if ($spokenResponse->isServerError()) {
                return $spokenResponse;
            }

            DB::transaction(function () use ($session, $validated, $response): void {
                $lockedSession = AiCompanionSession::query()->lockForUpdate()->findOrFail($session->id);
                $nextSequence = ((int) $lockedSession->turns()->max('sequence')) + 1;
                $lockedSession->turns()->createMany([
                    ['role' => 'user', 'sequence' => $nextSequence, 'content' => $validated['message']],
                    ['role' => 'model', 'sequence' => $nextSequence + 1, 'content' => $response],
                ]);
            });

            return $spokenResponse;
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'The companion is temporarily unavailable. Please try again.'], 503);
        }
    }

    public function finish(FinishAiCompanionRequest $request, PatientNlpReportGenerator $generator, PatientNlpClassifier $classifier): JsonResponse
    {
        $session = $this->patientSession($request->validated('session_id'), $request->user()->id);
        $session->update(['ended_at' => $session->ended_at ?? now()]);

        if (! $session->turns()->where('role', 'user')->exists()) {
            return response()->json(['report_id' => null, 'status' => 'no_conversation']);
        }

        if ($session->report !== null) {
            return response()->json(['report_id' => $session->report->id, 'status' => $session->report->status]);
        }

        $appointment = Appointment::query()->whereBelongsTo($request->user())->latest('appointment_date')->first();

        try {
            $classifier->classify($session, $appointment);
        } catch (Throwable $exception) {
            report($exception);
        }

        try {
            $reportData = $generator->generate($session, $appointment);
            $report = PatientNlpReport::query()->updateOrCreate(
                ['ai_companion_session_id' => $session->id],
                [
                    'user_id' => $request->user()->id,
                    'appointment_id' => $appointment?->id,
                    'report' => $reportData,
                    'generated_at' => now(),
                    'status' => 'generated',
                ],
            );

            return response()->json(['report_id' => $report->id, 'status' => $report->status]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'Your conversation was saved, but the report could not be generated yet.'], 503);
        }
    }

    private function spokenResponse(string $response, string $language, GoogleTextToSpeech $textToSpeech, GeminiTextToSpeech $sinhalaTextToSpeech): JsonResponse
    {
        try {
            if ($language === 'si') {
                $audio = $sinhalaTextToSpeech->synthesize($response, $language);
                $audioType = 'audio/wav';
            } else {
                $audio = $textToSpeech->synthesize($response);
                $audioType = 'audio/mpeg';
            }

            return response()->json([
                'response' => $response,
                'audio' => base64_encode($audio),
                'audio_type' => $audioType,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'Lumi is temporarily unavailable. Please try again.'], 503);
        }
    }

    private function patientSession(string $publicId, int $userId): AiCompanionSession
    {
        return AiCompanionSession::query()->where('public_id', $publicId)->where('user_id', $userId)->firstOrFail();
    }
}
