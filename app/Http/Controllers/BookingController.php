<?php

namespace App\Http\Controllers;

use App\Http\Requests\Booking\StoreAssessmentRequest;
use App\Http\Requests\Booking\StoreDetailsRequest;
use App\Http\Requests\Booking\StoreScheduleRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\PreAssessmentAnalyzer;
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
     * @var array<int, array{key: string, question: string}>
     */
    public const ASSESSMENT_QUESTIONS = [
        ['key' => 'reason', 'question' => "What's the main reason you're booking this appointment today?"],
        ['key' => 'duration', 'question' => 'How long have you been feeling this way?'],























        ['key' => 'severity', 'question' => 'How often does this affect you, and how severe would you say it is — mild, moderate, or severe?'],
        ['key' => 'triggers', 'question' => 'Is there anything that seems to bring this on or make it worse?'],
        ['key' => 'onset', 'question' => 'Did this start after a particular event or change in your life?'],
        ['key' => 'daily_impact', 'question' => 'How is this affecting your work, studies, or daily routine?'],
        ['key' => 'relationships', 'question' => 'Has this affected your relationships with family, friends, or colleagues?'],
        ['key' => 'sleep', 'question' => 'How has your sleep been recently — any trouble falling or staying asleep?'],
        ['key' => 'appetite', 'question' => 'Have you noticed any changes in your appetite or eating habits?'],
        ['key' => 'mood', 'question' => 'How would you describe your mood most days — and does it change suddenly?'],
        ['key' => 'history', 'question' => 'Have you been diagnosed with or treated for a mental health condition before?'],
        ['key' => 'medication', 'question' => 'Are you currently taking any medication, including for this or anything else?'],
        ['key' => 'support', 'question' => 'Do you have people you can turn to for support right now — family, friends, or others?'],
        ['key' => 'safety', 'question' => 'Have you had any thoughts of harming yourself or others recently?'],
        ['key' => 'notes', 'question' => "Is there anything else you'd like your doctor to know before the appointment?"],
    ];

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

        return view('booking.assessment', [
            'doctor' => $doctor,
            'questions' => self::ASSESSMENT_QUESTIONS,
            'saved' => $this->stepData($doctor, 'assessment'),
        ]);
    }

    public function storeAssessment(StoreAssessmentRequest $request, Doctor $doctor): RedirectResponse
    {
        $this->ensureBookable($doctor);

        if ($redirect = $this->ensureStepComplete($doctor, ['schedule', 'details'])) {
            return $redirect;
        }

        $this->saveStep($doctor, 'assessment', $request->validated());

        return redirect()->route('booking.review', $doctor);
    }

    /**
     * Step 4: review everything before confirming.
     */
    public function review(Doctor $doctor): View|RedirectResponse
    {
        $this->ensureBookable($doctor);

        if ($redirect = $this->ensureStepComplete($doctor, ['schedule', 'details', 'assessment'])) {
            return $redirect;
        }

        $booking = session("booking.{$doctor->id}");
        $analysis = app(PreAssessmentAnalyzer::class)->analyze(
            $booking['assessment']['answers'],
            $booking['assessment']['mood_rating']
        );

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
    public function confirm(Doctor $doctor): RedirectResponse
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

        $analysis = app(PreAssessmentAnalyzer::class)->analyze(
            $booking['assessment']['answers'],
            $booking['assessment']['mood_rating']
        );

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
            'pre_assessment' => $booking['assessment']['answers'],
            'pre_assessment_mood_rating' => $booking['assessment']['mood_rating'],
            'pre_assessment_summary' => $analysis['summary'],
            'pre_assessment_risk_level' => $analysis['risk_level'],
            'status' => 'confirmed',
        ]);

        session()->forget("booking.{$doctor->id}");

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
