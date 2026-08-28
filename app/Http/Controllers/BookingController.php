<?php

namespace App\Http\Controllers;

use App\Http\Requests\Booking\InterpretScreenerAnswerRequest;
use App\Http\Requests\Booking\StoreAssessmentRequest;
use App\Http\Requests\Booking\StoreDetailsRequest;
use App\Http\Requests\Booking\StoreScheduleRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\ScreenerDraft;
use App\Notifications\DoctorPortalNotification;
use App\Services\ScreenerAnalyzer;
use App\Services\ScreenerAnswerInterpreter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Ordered list of pre-assessment questions asked by the voice assistant.
     *
     * @var array<int, array{key: string, instrument: string, question: string}>
     */
    public const ASSESSMENT_QUESTIONS = ScreenerAnalyzer::QUESTIONS;

    /**
     * Step 1: choose a date, time slot and consultation mode.
     */
    public function schedule(Doctor $doctor): View
    {
        $this->ensureBookable($doctor);

        return view('booking.schedule', [
            'doctor' => $doctor,
            'saved' => $this->stepData($doctor, 'schedule'),
        ]);
    }

    public function storeSchedule(StoreScheduleRequest $request, Doctor $doctor): RedirectResponse
    {
        $this->ensureBookable($doctor);

        $data = $request->validated();

        if ($this->isSlotTaken($doctor, $data['appointment_date'], $data['appointment_time'])) {
            return back()->withErrors(['appointment_time' => 'That time slot was just booked. Please choose another.'])->withInput();
        }

        $this->saveStep($doctor, 'schedule', $data);

        return redirect()->route('booking.details', $doctor);
    }

    /**
     * JSON endpoint powering the time-slot picker on the schedule step.
     */
    public function slots(Request $request, Doctor $doctor): JsonResponse
    {
        $this->ensureBookable($doctor);

        $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        return response()->json([
            'slots' => $this->availableSlots($doctor, $request->string('date')->toString()),
        ]);
    }

    /**
     * Step 2: patient details.
     */
    public function details(Doctor $doctor): View|RedirectResponse
    {
        $this->ensureBookable($doctor);

        if (! $redirect = $this->ensureStepComplete($doctor, ['schedule'])) {
            $patient = Auth::user();

            return view('booking.details', [
                'doctor' => $doctor,
                'saved' => $this->stepData($doctor, 'details') ?? [
                    'patient_name' => $patient->name,
                    'patient_phone' => $patient->mobile,
                    'patient_email' => $patient->email,
                ],
            ]);
        }

        return $redirect;
    }

    public function storeDetails(StoreDetailsRequest $request, Doctor $doctor): RedirectResponse
    {
        $this->ensureBookable($doctor);

        if ($redirect = $this->ensureStepComplete($doctor, ['schedule'])) {
            return $redirect;
        }

        $this->saveStep($doctor, 'details', $request->validated());

        return redirect()->route('booking.assessment', $doctor);
    }

    /**
     * Step 3: AI voice assistant pre-assessment.
     */
    public function assessment(Doctor $doctor): View|RedirectResponse
    {
        $this->ensureBookable($doctor);

        if ($redirect = $this->ensureStepComplete($doctor, ['schedule', 'details'])) {
            return $redirect;
        }

        $draft = ScreenerDraft::query()
            ->whereBelongsTo(Auth::user())
            ->whereBelongsTo($doctor)
            ->first();

        return view('booking.assessment', [
            'doctor' => $doctor,
            'questions' => collect(self::ASSESSMENT_QUESTIONS)->map(fn (array $question): array => [
                ...$question,
                'audio_url' => asset("audio/screener/{$question['key']}.mp3"),
                'audio_url_si' => asset("audio/screener/si/{$question['key']}.wav"),
            ])->all(),
            'scale' => ScreenerAnalyzer::SCALE,
            'saved' => $this->stepData($doctor, 'assessment') ?? ['answers' => $draft?->answers ?? []],
            'currentQuestion' => $draft?->current_question ?? 0,
            'language' => $draft?->language,
            'clarificationAudio' => [
                'en' => asset('audio/screener/clarification.mp3'),
                'si' => asset('audio/screener/si/clarification.wav'),
            ],
        ]);
    }

    public function storeAssessment(StoreAssessmentRequest $request, Doctor $doctor): RedirectResponse
    {
        $this->ensureBookable($doctor);

        if ($redirect = $this->ensureStepComplete($doctor, ['schedule', 'details'])) {
            return $redirect;
        }

        $validated = $request->validated();
        $skipped = (bool) ($validated['skipped'] ?? false);

        $this->saveStep($doctor, 'assessment', [
            'skipped' => $skipped,
            'answers' => $skipped ? [] : $this->canonicalAnswers($validated['answers']),
            'open_notes' => $validated['open_notes'] ?? null,
        ]);

        if ($skipped) {
            ScreenerDraft::query()->whereBelongsTo(Auth::user())->whereBelongsTo($doctor)->delete();
        }

        return redirect()->route('booking.review', $doctor);
    }

    /**
     * Step 4: review everything before confirming.
     */
    public function interpretAnswer(InterpretScreenerAnswerRequest $request, Doctor $doctor, ScreenerAnswerInterpreter $interpreter): JsonResponse
    {
        $this->ensureBookable($doctor);
        $validated = $request->validated();
        $question = collect(self::ASSESSMENT_QUESTIONS)->firstWhere('key', $validated['key']);
        $language = $validated['language'];
        $localizedQuestion = $language === 'si' ? $question['question_si'] : $question['question'];

        try {
            $interpretation = $interpreter->interpret($localizedQuestion, $validated['answer'], $question['key'] === 'phq_9');

            if ($interpretation['score'] !== null && ! $interpretation['needs_clarification']) {
                $this->saveScreenerDraft($doctor, $question, $validated['answer'], $interpretation, $language);
            }

            return response()->json($interpretation);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'We could not interpret that answer. Please record or type it again.', 'needs_clarification' => true], 503);
        }
    }

    public function review(Doctor $doctor, ScreenerAnalyzer $analyzer): View|RedirectResponse
    {
        $this->ensureBookable($doctor);

        if ($redirect = $this->ensureStepComplete($doctor, ['schedule', 'details', 'assessment'])) {
            return $redirect;
        }

        $booking = session("booking.{$doctor->id}");
        $skipped = (bool) ($booking['assessment']['skipped'] ?? false);
        $analysis = $skipped ? null : $analyzer->analyze($booking['assessment']['answers']);

        return view('booking.review', [
            'doctor' => $doctor,
            'schedule' => $booking['schedule'],
            'details' => $booking['details'],
            'assessment' => $booking['assessment'],
            'analysis' => $analysis,
        ]);
    }

    /**
     * Step 5: confirm — creates the appointment and clears the wizard session.
     */
    public function confirm(Doctor $doctor, ScreenerAnalyzer $analyzer): RedirectResponse
    {
        $this->ensureBookable($doctor);

        if ($redirect = $this->ensureStepComplete($doctor, ['schedule', 'details', 'assessment'])) {
            return $redirect;
        }

        $booking = session("booking.{$doctor->id}");

        if ($this->isSlotTaken($doctor, $booking['schedule']['appointment_date'], $booking['schedule']['appointment_time'])) {
            return redirect()->route('booking.schedule', $doctor)
                ->withErrors(['appointment_time' => 'That time slot was just booked. Please choose another.']);
        }

        $skipped = (bool) ($booking['assessment']['skipped'] ?? false);
        $analysis = $skipped ? null : $analyzer->analyze($booking['assessment']['answers']);

        $appointment = Appointment::create([
            'user_id' => Auth::id(),
            'doctor_id' => $doctor->id,
            'medical_center_id' => $doctor->medical_center_id,
            'appointment_date' => $booking['schedule']['appointment_date'],
            'appointment_time' => $booking['schedule']['appointment_time'],
            'mode' => $booking['schedule']['mode'],
            'patient_name' => $booking['details']['patient_name'],
            'patient_age' => $booking['details']['patient_age'] ?? null,
            'patient_gender' => $booking['details']['patient_gender'] ?? null,
            'patient_phone' => $booking['details']['patient_phone'],
            'patient_email' => $booking['details']['patient_email'] ?? null,
            'reason' => $booking['details']['reason'] ?? null,
            'consultation_fee' => $doctor->consultation_fee,
            'pre_assessment' => $skipped ? null : $booking['assessment']['answers'],
            'pre_assessment_summary' => $skipped ? null : $this->screenerSummary($analysis),
            'pre_assessment_risk_level' => $skipped ? null : ($analysis['requires_immediate_escalation'] ? 'elevated' : ($analysis['phq9']['severity'] === 'minimal' && $analysis['gad7']['severity'] === 'minimal' ? 'low' : 'moderate')),
            'phq9_total' => $skipped ? null : $analysis['phq9']['total'],
            'phq9_severity' => $skipped ? null : $analysis['phq9']['severity'],
            'gad7_total' => $skipped ? null : $analysis['gad7']['total'],
            'gad7_severity' => $skipped ? null : $analysis['gad7']['severity'],
            'self_harm_flag' => $skipped ? false : $analysis['phq9']['self_harm_flag'],
            'requires_immediate_escalation' => $skipped ? false : $analysis['requires_immediate_escalation'],
            'screener_open_notes' => $booking['assessment']['open_notes'] ?? null,
            'screener_completed_at' => $skipped ? null : now(),
            'status' => 'confirmed',
        ]);

        $doctor->notify((new DoctorPortalNotification(
            type: 'new_booking',
            message: 'New booking from '.$appointment->patient_name.' for '.$appointment->appointment_date->format('j M Y').'.',
            link: route('doctor.appointments.show', $appointment, absolute: false),
        ))->afterCommit());

        if ($appointment->requiresCrisisEscalation()) {
            $doctor->notify((new DoctorPortalNotification(
                type: 'elevated_risk',
                message: 'Elevated-risk pre-assessment flagged for '.$appointment->patient_name.'.',
                link: route('doctor.appointments.show', $appointment, absolute: false),
            ))->afterCommit());
        }

        session()->forget("booking.{$doctor->id}");
        ScreenerDraft::query()->whereBelongsTo(Auth::user())->whereBelongsTo($doctor)->delete();

        return redirect()->route('booking.confirmed', $appointment);
    }

    public function confirmed(Appointment $appointment): View
    {
        abort_unless($appointment->user_id === Auth::id(), 403);

        return view('booking.confirmed', [
            'appointment' => $appointment->load('doctor.medicalCenter'),
        ]);
    }

    private function ensureBookable(Doctor $doctor): void
    {
        abort_unless($doctor->isBookable(), 404);
    }

    /**
     * @param  array{key: string, instrument: string, question: string}  $question
     * @param  array{score: int|null, confidence: string, needs_clarification: bool, reason: string, extracted_context: string}  $interpretation
     */
    private function saveScreenerDraft(Doctor $doctor, array $question, string $answer, array $interpretation, string $language): void
    {
        $draft = ScreenerDraft::query()->firstOrNew([
            'user_id' => Auth::id(),
            'doctor_id' => $doctor->id,
        ]);
        $answers = collect($draft->answers ?? [])->keyBy('key');
        $answers->put($question['key'], [
            ...$question,
            'score' => $interpretation['score'],
            'answer' => $answer,
            'confidence' => $interpretation['confidence'],
            'extracted_context' => $interpretation['extracted_context'],
        ]);
        $questionIndex = collect(self::ASSESSMENT_QUESTIONS)->search(fn (array $candidate): bool => $candidate['key'] === $question['key']);
        $draft->fill([
            'answers' => $answers->values()->all(),
            'current_question' => min(((int) $questionIndex) + 1, count(self::ASSESSMENT_QUESTIONS) - 1),
            'language' => $language,
        ])->save();
    }

    /**
     * @param  array<int, array<string, mixed>>  $submittedAnswers
     * @return array<int, array<string, mixed>>
     */
    private function canonicalAnswers(array $submittedAnswers): array
    {
        $submittedByKey = collect($submittedAnswers)->keyBy('key');

        return collect(self::ASSESSMENT_QUESTIONS)->map(function (array $question) use ($submittedByKey): array {
            $submitted = $submittedByKey->get($question['key']);

            return [
                ...$question,
                'score' => (int) $submitted['score'],
                'answer' => $submitted['answer'] ?? '',
                'confidence' => $submitted['confidence'] ?? 'manual',
                'extracted_context' => $submitted['extracted_context'] ?? '',
            ];
        })->all();
    }

    /** @param  array<string, mixed>  $analysis */
    private function screenerSummary(array $analysis): string
    {
        return "PHQ-9: {$analysis['phq9']['total']}/27 ({$analysis['phq9']['severity']}). "
            ."GAD-7: {$analysis['gad7']['total']}/21 ({$analysis['gad7']['severity']})."
            .($analysis['requires_immediate_escalation'] ? ' Immediate escalation required due to a positive PHQ-9 self-harm item.' : '');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function stepData(Doctor $doctor, string $step): ?array
    {
        return session("booking.{$doctor->id}.{$step}");
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveStep(Doctor $doctor, string $step, array $data): void
    {
        session(["booking.{$doctor->id}.{$step}" => $data]);
    }

    /**
     * @param  array<int, string>  $steps
     */
    private function ensureStepComplete(Doctor $doctor, array $steps): ?RedirectResponse
    {
        $booking = session("booking.{$doctor->id}", []);

        foreach ($steps as $step) {
            if (! isset($booking[$step])) {
                return redirect()->route('booking.schedule', $doctor)
                    ->with('status', 'Please complete each booking step in order.');
            }
        }

        return null;
    }

    private function isSlotTaken(Doctor $doctor, string $date, string $time): bool
    {
        return Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->where('status', 'confirmed')
            ->whereDate('appointment_date', $date)
            ->where('appointment_time', $time)
            ->exists();
    }

    /**
     * @return array<int, array{time: string, label: string}>
     */
    private function availableSlots(Doctor $doctor, string $date): array
    {
        $taken = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->where('status', 'confirmed')
            ->whereDate('appointment_date', $date)
            ->pluck('appointment_time')
            ->map(fn (string $time): string => Carbon::parse($time)->format('H:i'))
            ->all();

        $isToday = Carbon::parse($date)->isToday();
        $now = Carbon::now();

        $slots = [];
        $cursor = Carbon::parse($date)->setTime(9, 0);
        $end = Carbon::parse($date)->setTime(17, 0);

        while ($cursor->lt($end)) {
            $time = $cursor->format('H:i');

            if (! in_array($time, $taken, true) && (! $isToday || $cursor->gt($now))) {
                $slots[] = ['time' => $time, 'label' => $cursor->format('g:i A')];
            }

            $cursor->addMinutes(30);
        }

        return $slots;
    }
}
