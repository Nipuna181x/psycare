<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMoodEntryRequest;
use App\Services\MoodTrend;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MoodTrackerController extends Controller
{
    public function index(MoodTrend $moodTrend): View
    {
        $patient = Auth::user();

        return view('mood-tracker.index', [
            'todayEntry' => $patient->moodEntries()->whereDate('entry_date', today())->first(),
            'entries' => $patient->moodEntries()->latest('entry_date')->paginate(30),
            'moodChartData' => $moodTrend->forPatient($patient),
        ]);
    }

    public function store(StoreMoodEntryRequest $request): RedirectResponse
    {
        $request->user()->moodEntries()->updateOrCreate(
            ['entry_date' => today()],
            $request->validated(),
        );

        return back()->with('status', 'Thanks for checking in today. Your entry has been saved.');
    }
}
