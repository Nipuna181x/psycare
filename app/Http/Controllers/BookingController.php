<?php

namespace App\Http\Controllers;

use App\Http\Requests\Booking\InterpretScreenerAnswerRequest;
use App\Http\Requests\Booking\StoreAssessmentRequest;
use App\Http\Requests\Booking\StoreClinicRequest;
use App\Http\Requests\Booking\StoreDetailsRequest;
use App\Http\Requests\Booking\StoreScheduleRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorAvailabilitySlot;
use App\Models\MedicalCenter;
use App\Models\ScreenerDraft;
use App\Services\AppointmentPaymentService;
use App\Services\ScreenerAnalyzer;
use App\Services\ScreenerAnswerInterpreter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

/**
 * Checkout uses PsyCare's single Stripe account. Clinic and doctor amounts are
 * internal ledger allocations only; Stripe Connect and fund transfers are out
 * of scope, and payment is verified by retrieving Checkout Sessions directly.
 */
class BookingController extends Controller
{
    /**
     * Ordered list of pre-assessment questions asked by the voice assistant.
     *
     * @var array<int, array{key: string, instrument: string, question: string}>
     */
    public const ASSESSMENT_QUESTIONS = ScreenerAnalyzer::QUESTIONS;

    /**
     * Optional step: choose which clinic to book at, when the doctor has more
     * than one active affiliation. Doctors with exactly one active affiliation
     * skip this step entirely — it is auto-selected when the schedule step loads.
     */
    public function clinic(Doctor $doctor): View|RedirectResponse
    {
        $this->ensureBookable($doctor);

        $affiliations = $doctor->bookableAffiliations()->with('clinic')->get();

        if ($affiliations->count() <= 1) {
            return redirect()->route('booking.schedule', $doctor);
        }

        return view('booking.clinic', [
            'doctor' => $doctor,
            'affiliations' => $affiliations,
            'saved' => $this->stepData($doctor, 'clinic'),
        ]);
    }

    public function storeClinic(StoreClinicRequest $request, Doctor $doctor): RedirectResponse
    {
        $this->ensureBookable($doctor);

        $clinicId = (int) $request->validated('clinic_id');

        abort_unless($doctor->bookableAffiliations()->where('clinic_id', $clinicId)->exists(), 422);

        $this->selectClinic($doctor, $clinicId);

        return redirect()->route('booking.schedule', $doctor);
    }

    /**
     * Step 1: choose a date, time slot and consultation mode.
     */
    public function schedule(Doctor $doctor): View|RedirectResponse
    {
        $this->ensureBookable($doctor);

        if ($redirect = $this->ensureClinicSelected($doctor)) {
            return $redirect;
        }

        $clinicId = (int) $this->stepData($doctor, 'clinic')['clinic_id'];

        return view('booking.schedule', [
            'doctor' => $doctor,
            'clinic' => MedicalCenter::findOrFail($clinicId),
            'canChangeClinic' => $doctor->bookableAffiliations()->where('clinic_id', '!=', $clinicId)->exists(),
            'saved' => $this->stepData($doctor, 'schedule'),
        ]);
    }

    public function storeSchedule(StoreScheduleRequest $request, Doctor $doctor): RedirectResponse
    {
        $this->ensureBookable($doctor);

        if ($redirect = $this->ensureClinicSelected($doctor)) {
            return $redirect;
        }

        $data = [...$request->validated(), 'mode' => 'in_person'];
        $clinicId = $this->stepData($doctor, 'clinic')['clinic_id'];

        if (! $this->isSlotAvailable($doctor, $clinicId, $data['appointment_date'], $data['appointment_time'])) {
            return back()->withErrors(['appointment_time' => 'That time is no longer available at this clinic. Please choose another.'])->withInput();
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

        $clinicId = $this->stepData($doctor, 'clinic')['clinic_id'] ?? null;

        if (! $clinicId || ! $doctor->bookableAffiliations()->where('clinic_id', $clinicId)->exists()) {
            return response()->json(['slots' => []]);
        }

        return response()->json([
            'slots' => $this->availableSlots($doctor, $clinicId, $request->string('date')->toString()),
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
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'We could not interpret that answer. Please record or type it again.', 'needs_clarification' => true], 503);
        }
    }

    public function review(Doctor $doctor, ScreenerAnalyzer $analyzer): View|RedirectResponse
    {
        $this->ensureBookable($doctor);

        if ($redirect = $this->ensureClinicSelected($doctor)) {
            return $redirect;
        }

        if ($redirect = $this->ensureStepComplete($doctor, ['schedule', 'details', 'assessment'])) {
            return $redirect;
        }

        $booking = session("booking.{$doctor->id}");
        $clinic = MedicalCenter::find($booking['clinic']['clinic_id']);

        if ($redirect = $this->ensurePriced($doctor, $clinic)) {
            return $redirect;
        }

        $skipped = (bool) ($booking['assessment']['skipped'] ?? false);
        $analysis = $skipped ? null : $analyzer->analyze($booking['assessment']['answers']);

        return view('booking.review', [
            'doctor' => $doctor,
            'clinic' => $clinic,
            'doctorFee' => $doctor->consultation_fee,
            'clinicFee' => $clinic->facility_fee,
            'totalFee' => $doctor->consultation_fee + $clinic->facility_fee,
            'schedule' => $booking['schedule'],
            'details' => $booking['details'],
            'assessment' => $booking['assessment'],
            'analysis' => $analysis,
        ]);
    }

    /**
     * Step 5: reserve the appointment and redirect to Stripe Checkout.
     */
    public function confirm(Doctor $doctor, ScreenerAnalyzer $analyzer, AppointmentPaymentService $payments): RedirectResponse
    {
        $this->ensureBookable($doctor);

        if ($redirect = $this->ensureClinicSelected($doctor)) {
            return $redirect;
        }

        if ($redirect = $this->ensureStepComplete($doctor, ['schedule', 'details', 'assessment'])) {
            return $redirect;
        }

        $booking = session("booking.{$doctor->id}");
        $clinicId = $booking['clinic']['clinic_id'];
        $clinic = MedicalCenter::find($clinicId);

        if ($redirect = $this->ensurePriced($doctor, $clinic)) {
            return $redirect;
        }

        if (! $this->isSlotAvailable($doctor, $clinicId, $booking['schedule']['appointment_date'], $booking['schedule']['appointment_time'])) {
            return redirect()->route('booking.schedule', $doctor)
                ->withErrors(['appointment_time' => 'That time is no longer available at this clinic. Please choose another.']);
        }

        $skipped = (bool) ($booking['assessment']['skipped'] ?? false);
        $analysis = $skipped ? null : $analyzer->analyze($booking['assessment']['answers']);

        $appointment = DB::transaction(function () use ($doctor, $clinic, $clinicId, $booking, $skipped, $analysis): ?Appointment {
            $slot = DoctorAvailabilitySlot::query()
                ->where('doctor_id', $doctor->id)
                ->where('clinic_id', $clinicId)
                ->whereDate('date', $booking['schedule']['appointment_date'])
                ->where('start_time', $booking['schedule']['appointment_time'])
                ->lockForUpdate()
                ->first();

            if ($slot?->is_booked || $this->isSlotTaken($doctor, $clinicId, $booking['schedule']['appointment_date'], $booking['schedule']['appointment_time'])) {
                return null;
            }

            $appointment = Appointment::query()->create([
                'user_id' => Auth::id(),
                'doctor_id' => $doctor->id,
                'medical_center_id' => $clinicId,
                'doctor_availability_slot_id' => $slot?->id,
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
                'doctor_fee_charged' => $doctor->consultation_fee,
                'clinic_fee_charged' => $clinic->facility_fee,
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
                'status' => 'pending_payment',
            ]);

            $slot?->update(['is_booked' => true, 'appointment_id' => $appointment->id]);

            return $appointment;
        }, attempts: 3);

        if (! $appointment) {
            return redirect()->route('booking.schedule', $doctor)
                ->withErrors(['appointment_time' => 'That time slot was just reserved. Please choose another.']);
        }

        try {
            $checkout = $payments->start($appointment);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('booking.review', $doctor)
                ->with('status', 'We could not start secure payment. Your slot was released; please try again.');
        }

        session()->forget("booking.{$doctor->id}");
        ScreenerDraft::query()->whereBelongsTo(Auth::user())->whereBelongsTo($doctor)->delete();

        return redirect()->away($checkout['checkout_url']);
    }

    public function confirmed(Appointment $appointment): View
    {
        abort_unless($appointment->user_id === Auth::id(), 403);
        abort_unless($appointment->status === 'confirmed', 404);
        abort_if($appointment->payment && $appointment->payment->status !== 'succeeded', 404);

        return view('booking.confirmed', [
            'appointment' => $appointment->load('doctor', 'medicalCenter', 'payment'),
        ]);
    }

    private function ensureBookable(Doctor $doctor): void
    {
        abort_unless($doctor->isBookable() && $doctor->hasBookableAffiliation(), 404);
    }

    /**
     * Guard checkout/confirmation against a doctor or clinic that hasn't set
     * their pricing yet — booking must never complete with a missing or
     * zero price.
     */
    private function ensurePriced(Doctor $doctor, ?MedicalCenter $clinic): ?RedirectResponse
    {
        if ($doctor->isPriced() && $clinic?->isPriced()) {
            return null;
        }

        return redirect()->route('booking.schedule', $doctor)
            ->with('status', "This doctor or clinic hasn't set their pricing yet — booking is temporarily unavailable.");
    }

    /**
     * Ensure a clinic has been selected for this booking session. Doctors with
     * exactly one active affiliation get it auto-selected transparently; doctors
     * with more than one are sent to the clinic-select step first.
     */
    private function ensureClinicSelected(Doctor $doctor): ?RedirectResponse
    {
        $affiliations = $doctor->bookableAffiliations()->get();

        $selectedClinicId = $this->stepData($doctor, 'clinic')['clinic_id'] ?? null;

        if ($selectedClinicId && $affiliations->contains('clinic_id', $selectedClinicId)) {
            return null;
        }

        if ($affiliations->count() === 1) {
            $this->selectClinic($doctor, $affiliations->first()->clinic_id);

            return null;
        }

        return redirect()->route('booking.clinic', $doctor);
    }

    private function selectClinic(Doctor $doctor, int $clinicId): void
    {
        $previousClinicId = $this->stepData($doctor, 'clinic')['clinic_id'] ?? null;

        if ($previousClinicId !== null && (int) $previousClinicId !== $clinicId) {
            session()->forget([
                "booking.{$doctor->id}.schedule",
                "booking.{$doctor->id}.details",
                "booking.{$doctor->id}.assessment",
            ]);
        }

        $this->saveStep($doctor, 'clinic', ['clinic_id' => $clinicId]);
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

    private function isSlotTaken(Doctor $doctor, int $clinicId, string $date, string $time): bool
    {
        return Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->where('medical_center_id', $clinicId)
            ->whereIn('status', ['pending_payment', 'confirmed'])
            ->whereDate('appointment_date', $date)
            ->where('appointment_time', $time)
            ->exists();
    }

    private function isSlotAvailable(Doctor $doctor, int $clinicId, string $date, string $time): bool
    {
        return collect($this->availableSlots($doctor, $clinicId, $date))
            ->contains(fn (array $slot): bool => $slot['time'] === $time && ! $slot['disabled']);
    }

    /**
     * Clinic-scoped time slots for the given date. The current clinic operating
     * hours always define the visible 30-minute grid, so a settings change takes
     * effect immediately. Stored availability rows and existing appointments are
     * then overlaid to disable times that are already reserved or booked.
     *
     * @return array<int, array{time: string, label: string, disabled: bool}>
     */
    private function availableSlots(Doctor $doctor, ?int $clinicId, string $date): array
    {
        [$opens, $closes] = $this->clinicHoursFor($clinicId, $date);

        if ($opens === null) {
            return [];
        }

        $publishedSlots = DoctorAvailabilitySlot::query()
            ->where('doctor_id', $doctor->id)
            ->when($clinicId, fn ($query) => $query->where('clinic_id', $clinicId))
            ->whereDate('date', $date)
            ->get()
            ->keyBy(fn (DoctorAvailabilitySlot $slot): string => Carbon::parse($slot->start_time)->format('H:i'));

        $taken = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->when($clinicId, fn ($query) => $query->where('medical_center_id', $clinicId))
            ->whereIn('status', ['pending_payment', 'confirmed'])
            ->whereDate('appointment_date', $date)
            ->pluck('appointment_time')
            ->map(fn (string $time): string => Carbon::parse($time)->format('H:i'))
            ->all();

        $isToday = Carbon::parse($date)->isToday();
        $now = Carbon::now();

        $slots = [];
        $cursor = Carbon::parse($date.' '.$opens);
        $end = Carbon::parse($date.' '.$closes);

        while ($cursor->lt($end)) {
            $time = $cursor->format('H:i');

            if (! $isToday || $cursor->gt($now)) {
                $slots[] = [
                    'time' => $time,
                    'label' => $cursor->format('g:i A'),
                    'disabled' => ($publishedSlots->get($time)?->is_booked ?? false) || in_array($time, $taken, true),
                ];
            }

            $cursor->addMinutes(30);
        }

        return $slots;
    }

    /**
     * The clinic's opening/closing time for the given date's day of the week, as
     * ['H:i', 'H:i']. Falls back to a default 9AM-5PM window when the clinic has
     * not set operating hours at all. Returns [null, null] when the clinic has
     * set hours and marked that day closed.
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function clinicHoursFor(?int $clinicId, string $date): array
    {
        $hours = $clinicId ? MedicalCenter::find($clinicId)?->operating_hours : null;

        if (! $hours) {
            return ['09:00', '17:00'];
        }

        $dayName = Carbon::parse($date)->format('l');
        $row = collect($hours)->firstWhere('day', $dayName);

        if (! $row || ($row['closed'] ?? false) || empty($row['opens']) || empty($row['closes'])) {
            return [null, null];
        }

        return [$row['opens'], $row['closes']];
    }
}
