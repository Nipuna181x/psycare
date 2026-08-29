<?php

namespace App\Services;

use App\Models\User;

class MoodTrend
{
    /** @return array{labels: array<int, string>, scores: array<int, int>} */
    public function forPatient(User $patient): array
    {
        $entries = $patient->moodEntries()
            ->whereDate('entry_date', '>=', today()->subDays(29))
            ->orderBy('entry_date')
            ->get(['mood_score', 'entry_date']);

        return [
            'labels' => $entries->map(fn ($entry): string => $entry->entry_date->format('j M'))->all(),
            'scores' => $entries->pluck('mood_score')->all(),
        ];
    }
}
