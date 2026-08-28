<?php

namespace App\Http\Controllers\Doctor;

use App\Events\TherapyRoomEnded;
use App\Events\TherapyRoomParticipantKicked;
use App\Events\TherapyRoomSignal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\AddTherapyRoomParticipantRequest;
use App\Http\Requests\Doctor\StoreTherapyRoomRequest;
use App\Http\Requests\Doctor\UpdateTherapyRoomRequest;
use App\Http\Requests\TherapyRoomSignalRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\TherapyRoom;
use App\Models\TherapyRoomParticipant;
use App\Models\User;
use App\Notifications\TherapyRoomParticipantRemoved;
use App\Notifications\TherapyRoomScheduled;
use App\Services\DoctorClinicContext;
use App\Services\TherapyParticipantAssigner;
use App\Services\TherapyParticipantRemover;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TherapyRoomController extends Controller
{
    public function __construct(
        private readonly Gate $gate,
    ) {}

    /**
     * Display the authenticated doctor's therapy rooms.
     */
    public function index(DoctorClinicContext $clinicContext): View
    {
        $doctor = Auth::guard('doctor')->user();
        $clinicId = $clinicContext->current($doctor);

        $rooms = $doctor->therapyRooms()
            ->when($clinicId, fn ($query) => $query->where('medical_center_id', $clinicId))
            ->withCount('activeParticipants')
            ->orderByDesc('scheduled_at')
            ->get();

        return view('doctor.therapy-rooms.index', [
            'upcoming' => $rooms->whereIn('status', ['scheduled', 'live']),
            'history' => $rooms->whereIn('status', ['completed', 'cancelled']),
        ]);
    }

    /**
     * Show the form for creating a new therapy room.
     */
    public function create(): View
    {
        $doctor = Auth::guard('doctor')->user();

        return view('doctor.therapy-rooms.create', [
            'patients' => $this->eligiblePatients($doctor),
            'maxParticipants' => TherapyRoom::MAX_PARTICIPANTS,
        ]);
    }

    /**
     * Store a newly created therapy room and assign the selected patients.
     */
    public function store(StoreTherapyRoomRequest $request, TherapyParticipantAssigner $assigner, DoctorClinicContext $clinicContext): RedirectResponse
    {
        $doctor = Auth::guard('doctor')->user();

        $eligiblePatientIds = $this->eligiblePatients($doctor)->pluck('id');
        $selectedPatientIds = collect($request->validated('patient_ids'));

        abort_unless($selectedPatientIds->diff($eligiblePatientIds)->isEmpty(), 403);

        $room = $doctor->therapyRooms()->create([
            'medical_center_id' => $clinicContext->current($doctor),
            'title' => $request->validated('title'),
            'topic' => $request->validated('topic'),
            'scheduled_at' => $request->validated('scheduled_at'),
            'duration_minutes' => $request->validated('duration_minutes'),
            'status' => 'scheduled',
        ]);

        $patients = User::whereIn('id', $selectedPatientIds)->get()->keyBy('id');

        foreach ($selectedPatientIds as $patientId) {
            $participant = $assigner->handle($room, $patients[$patientId]);
            $participant->patient->notify(new TherapyRoomScheduled($participant));
        }

        return redirect()->route('doctor.therapy-rooms.show', $room)
            ->with('status', 'Therapy room scheduled and patients notified.');
    }

    /**
     * Display a single therapy room with real patient identities and their assigned labels.
     */
    public function show(TherapyRoom $therapyRoom): View
    {
        $this->gate->forUser(Auth::guard('doctor')->user())->authorize('view', $therapyRoom);

        return view('doctor.therapy-rooms.show', [
            'therapyRoom' => $therapyRoom->load(['activeParticipants.patient']),
        ]);
    }

    /**
     * Show the form for editing the given therapy room.
     */
    public function edit(TherapyRoom $therapyRoom): View
    {
        $doctor = Auth::guard('doctor')->user();
        $this->gate->forUser($doctor)->authorize('update', $therapyRoom);

        return view('doctor.therapy-rooms.edit', [
            'therapyRoom' => $therapyRoom->load('activeParticipants'),
            'patients' => $this->eligiblePatients($doctor),
        ]);
    }

    /**
     * Update the given therapy room's schedule details.
     */
    public function update(UpdateTherapyRoomRequest $request, TherapyRoom $therapyRoom): RedirectResponse
    {
        $this->gate->forUser(Auth::guard('doctor')->user())->authorize('update', $therapyRoom);

        $therapyRoom->update($request->validated());

        return redirect()->route('doctor.therapy-rooms.show', $therapyRoom)
            ->with('status', 'Therapy room updated.');
    }

    /**
     * Add a patient to the room, assigning them the next anonymous label.
     */
    public function addParticipant(AddTherapyRoomParticipantRequest $request, TherapyRoom $therapyRoom, TherapyParticipantAssigner $assigner): RedirectResponse
    {
        $doctor = Auth::guard('doctor')->user();
        $this->gate->forUser($doctor)->authorize('manageParticipants', $therapyRoom);

        abort_if($therapyRoom->activeParticipants()->count() >= TherapyRoom::MAX_PARTICIPANTS, 422, 'This room already has the maximum number of participants.');

        $patient = $this->eligiblePatients($doctor)->firstWhere('id', $request->validated('patient_id'));
        abort_unless($patient, 403);
        abort_if($therapyRoom->activeParticipants()->where('patient_id', $patient->id)->exists(), 422, 'This patient is already in the room.');

        $participant = $assigner->handle($therapyRoom, $patient);
        $participant->patient->notify(new TherapyRoomScheduled($participant));

        return redirect()->route('doctor.therapy-rooms.show', $therapyRoom)
            ->with('status', 'Patient added and notified.');
    }

    /**
     * Remove a patient from the room before it goes live.
     */
    public function removeParticipant(TherapyRoom $therapyRoom, TherapyRoomParticipant $participant, TherapyParticipantRemover $remover): RedirectResponse
    {
        $this->gate->forUser(Auth::guard('doctor')->user())->authorize('manageParticipants', $therapyRoom);
        abort_unless($participant->therapy_room_id === $therapyRoom->id, 404);

        $remover->handle($participant);
        $participant->patient->notify(new TherapyRoomParticipantRemoved($participant));

        return redirect()->route('doctor.therapy-rooms.show', $therapyRoom)
            ->with('status', 'Patient removed from the room.');
    }

    /**
     * Start the room, making it live and joinable.
     */
    public function start(TherapyRoom $therapyRoom): RedirectResponse
    {
        $this->gate->forUser(Auth::guard('doctor')->user())->authorize('start', $therapyRoom);

        $therapyRoom->update(['status' => 'live', 'started_at' => now()]);

        return redirect()->route('doctor.therapy-rooms.session', $therapyRoom);
    }

    /**
     * End the room for everyone and disconnect all peer connections.
     */
    public function end(TherapyRoom $therapyRoom): RedirectResponse
    {
        $this->gate->forUser(Auth::guard('doctor')->user())->authorize('end', $therapyRoom);

        $therapyRoom->update(['status' => 'completed', 'ended_at' => now()]);

        broadcast(new TherapyRoomEnded($therapyRoom->id));

        return redirect()->route('doctor.therapy-rooms.show', $therapyRoom)
            ->with('status', 'Session ended for all participants.');
    }

    /**
     * Remove a participant from the live call without altering their room membership record.
     */
    public function kickParticipant(TherapyRoom $therapyRoom, TherapyRoomParticipant $participant): RedirectResponse
    {
        $this->gate->forUser(Auth::guard('doctor')->user())->authorize('view', $therapyRoom);
        abort_unless($participant->therapy_room_id === $therapyRoom->id, 404);
        abort_unless($therapyRoom->status === 'live', 422);

        broadcast(new TherapyRoomParticipantKicked($therapyRoom->id, 'patient-'.$participant->patient_id));

        return back();
    }

    /**
     * Render the live call UI for the doctor.
     */
    public function session(TherapyRoom $therapyRoom): View
    {
        $this->gate->forUser(Auth::guard('doctor')->user())->authorize('view', $therapyRoom);
        abort_unless($therapyRoom->status === 'live', 404);

        return view('therapy-rooms.session', [
            'therapyRoom' => $therapyRoom,
            'role' => 'doctor',
            'myId' => 'doctor',
            'myLabel' => 'Doctor',
        ]);
    }

    /**
     * Roster of real patient identities alongside their anonymous labels, for the doctor's
     * call UI only. Never reachable by the patient guard.
     */
    public function roster(TherapyRoom $therapyRoom): JsonResponse
    {
        $this->gate->forUser(Auth::guard('doctor')->user())->authorize('view', $therapyRoom);

        return response()->json(
            $therapyRoom->activeParticipants()->with('patient')->get()->map(fn (TherapyRoomParticipant $participant) => [
                'id' => 'patient-'.$participant->patient_id,
                'anonymous_label' => $participant->anonymous_label,
                'patient_name' => $participant->patient->name,
            ])
        );
    }

    /**
     * Relay a single SDP/ICE signaling message to another verified participant of this room.
     * Re-checks authorization on every call — channel subscribe-time auth alone is not enough.
     */
    public function signal(TherapyRoomSignalRequest $request, TherapyRoom $therapyRoom): JsonResponse
    {
        $this->gate->forUser(Auth::guard('doctor')->user())->authorize('view', $therapyRoom);
        abort_unless($therapyRoom->status === 'live', 422);

        broadcast(new TherapyRoomSignal(
            therapyRoomId: $therapyRoom->id,
            from: 'doctor',
            to: $request->validated('to'),
            type: $request->validated('type'),
            payload: $request->validated('payload'),
        ));

        return response()->json(['sent' => true]);
    }

    /**
     * Patients this doctor has treated (has an appointment with) and may therefore add to a room.
     *
     * @return Collection<int, User>
     */
    private function eligiblePatients(Doctor $doctor): Collection
    {
        return User::whereIn('id', Appointment::where('doctor_id', $doctor->id)->pluck('user_id')->unique())->get();
    }
}
