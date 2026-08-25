<?php

namespace App\Http\Controllers;

use App\Events\TherapyRoomSignal;
use App\Http\Requests\TherapyRoomSignalRequest;
use App\Models\TherapyRoom;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TherapyRoomController extends Controller
{
    public function __construct(
        private readonly Gate $gate,
    ) {}

    /**
     * Display the authenticated patient's therapy rooms. Each room is annotated with only
     * this patient's own anonymous label — no other participant's data is ever loaded.
     */
    public function index(): View
    {
        $patient = Auth::user();

        $participations = $patient->therapyRoomParticipations()
            ->whereNull('removed_at')
            ->with('therapyRoom')
            ->get();

        return view('therapy-rooms.index', [
            'participations' => $participations,
        ]);
    }

    /**
     * Display a single therapy room the patient is assigned to, showing only their own label.
     */
    public function show(TherapyRoom $therapyRoom): View
    {
        $patient = Auth::user();
        $this->gate->forUser($patient)->authorize('join', $therapyRoom);

        $participant = $therapyRoom->activeParticipants()->where('patient_id', $patient->id)->firstOrFail();

        return view('therapy-rooms.show', [
            'therapyRoom' => $therapyRoom,
            'participant' => $participant,
        ]);
    }

    /**
     * Render the live call UI for the patient.
     */
    public function join(TherapyRoom $therapyRoom): View
    {
        $patient = Auth::user();
        $this->gate->forUser($patient)->authorize('join', $therapyRoom);
        abort_unless($therapyRoom->status === 'live', 404);

        $participant = $therapyRoom->activeParticipants()->where('patient_id', $patient->id)->firstOrFail();

        return view('therapy-rooms.session', [
            'therapyRoom' => $therapyRoom,
            'role' => 'patient',
            'myId' => 'patient-'.$patient->id,
            'myLabel' => $participant->anonymous_label,
        ]);
    }

    /**
     * Relay a single SDP/ICE signaling message to another verified participant of this room.
     * Re-checks authorization on every call — channel subscribe-time auth alone is not enough.
     */
    public function signal(TherapyRoomSignalRequest $request, TherapyRoom $therapyRoom): JsonResponse
    {
        $patient = Auth::user();
        $this->gate->forUser($patient)->authorize('join', $therapyRoom);
        abort_unless($therapyRoom->status === 'live', 422);

        broadcast(new TherapyRoomSignal(
            therapyRoomId: $therapyRoom->id,
            from: 'patient-'.$patient->id,
            to: $request->validated('to'),
            type: $request->validated('type'),
            payload: $request->validated('payload'),
        ));

        return response()->json(['sent' => true]);
    }
}
