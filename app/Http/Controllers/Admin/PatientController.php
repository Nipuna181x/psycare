<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array($request->string('status')->toString(), ['all', 'active', 'banned'], true)
            ? $request->string('status')->toString()
            : 'all';
        $search = trim($request->string('search')->toString());

        $patients = User::query()
            ->withCount(['appointments', 'moodEntries'])
            ->when($status === 'active', fn ($query) => $query->where('is_banned', false))
            ->when($status === 'banned', fn ($query) => $query->where('is_banned', true))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.patients.index', [
            'patients' => $patients,
            'search' => $search,
            'status' => $status,
            'activeCount' => User::where('is_banned', false)->count(),
            'bannedCount' => User::where('is_banned', true)->count(),
        ]);
    }

    public function show(User $patient): View
    {
        $patient->loadCount(['appointments', 'moodEntries', 'prescriptions', 'therapyRoomParticipations']);

        return view('admin.patients.show', [
            'patient' => $patient,
            'recentAppointments' => $patient->appointments()
                ->with(['doctor:id,name', 'medicalCenter:id,name'])
                ->visibleToCareTeam()
                ->latest('appointment_date')
                ->latest('appointment_time')
                ->take(10)
                ->get(),
            'latestMoodEntries' => $patient->moodEntries()->latest('entry_date')->take(7)->get(),
        ]);
    }

    public function ban(User $patient): RedirectResponse
    {
        $patient->update([
            'is_banned' => true,
            'banned_at' => now(),
        ]);

        return back()->with('status', "{$patient->name}'s account has been suspended.");
    }

    public function restore(User $patient): RedirectResponse
    {
        $patient->update([
            'is_banned' => false,
            'banned_at' => null,
        ]);

        return back()->with('status', "{$patient->name}'s account has been restored.");
    }
}
