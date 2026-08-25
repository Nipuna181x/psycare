<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPatientAccess;
use App\Models\AiCompanionSession;
use App\Models\User;
use Illuminate\View\View;

class PatientConversationController extends Controller
{
    use AuthorizesPatientAccess;

    /**
     * List the patient's AI companion conversations, grouped day by day, newest first.
     */
    public function index(User $patient): View
    {
        $guard = $this->authorizeAccess($patient);

        $sessions = $patient->aiCompanionSessions()
            ->withCount('turns')
            ->with('classificationResult')
            ->orderByDesc('created_at')
            ->get();

        return view("{$guard}.patients.conversations.index", [
            'patient' => $patient,
            'sessionsByDay' => $sessions->groupBy(fn (AiCompanionSession $session): string => $session->created_at->toDateString()),
        ]);
    }

    /**
     * Display a single conversation's full transcript alongside the risk classification
     * derived from it, so a clinician can see exactly what "moment" a given risk level reflects.
     */
    public function show(User $patient, AiCompanionSession $session): View
    {
        $guard = $this->authorizeAccess($patient);

        abort_unless($session->user_id === $patient->id, 404);

        return view("{$guard}.patients.conversations.show", [
            'patient' => $patient,
            'session' => $session->load('classificationResult'),
            'turns' => $session->turns()->orderBy('sequence')->get(),
        ]);
    }
}
