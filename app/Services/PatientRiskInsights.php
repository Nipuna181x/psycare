<?php

namespace App\Services;

use App\Models\NlpClassificationResult;
use App\Models\PatientNlpReport;
use Illuminate\Database\Eloquent\Collection;

class PatientRiskInsights
{
    /**
     * Ordinal ranking of risk levels, lowest to highest severity, used to derive the
     * day-by-day risk progression.
     *
     * @var array<string, int>
     */
    private const RISK_LEVEL_ORDER = ['low' => 0, 'minimal' => 0, 'moderate' => 1, 'mild' => 1, 'high' => 2, 'urgent' => 3, 'severe' => 3];

    /**
     * Compare each day's risk level against the one before it to describe whether the
     * patient's risk is trending up, down, or holding steady over time.
     *
     * @param  Collection<int, PatientNlpReport>  $reports  ordered oldest first
     * @return array{trend: string|null, points: array<int, array{date: string, level: string, rank: int|null}>}
     */
    public function riskProgression(Collection $reports): array
    {
        $points = $reports->map(fn (PatientNlpReport $report): array => [
            'date' => $report->generated_at->format('j M Y'),
            'level' => $report->report['risk']['level'] ?? 'unknown',
            'rank' => self::RISK_LEVEL_ORDER[$report->report['risk']['level'] ?? ''] ?? null,
        ])->values()->all();

        $ranked = collect($points)->filter(fn (array $point): bool => $point['rank'] !== null);
        $earliest = $ranked->first();
        $latest = $ranked->last();

        $trend = null;
        if ($earliest && $latest && $earliest !== $latest) {
            $trend = match (true) {
                $latest['rank'] > $earliest['rank'] => 'increasing',
                $latest['rank'] < $earliest['rank'] => 'decreasing',
                default => 'stable',
            };
        }

        return ['trend' => $trend, 'points' => $points];
    }

    /**
     * Shape the classification history into arrays Chart.js can consume directly.
     *
     * @param  Collection<int, NlpClassificationResult>  $classifications
     * @return array<string, mixed>
     */
    public function chartData(Collection $classifications): array
    {
        $severityScore = fn (?string $severity): ?int => match ($severity) {
            'minimal' => 0, 'mild' => 1, 'moderate' => 2, 'moderately_severe' => 3, 'severe' => 4,
            default => null,
        };

        return [
            'labels' => $classifications->map(fn (NlpClassificationResult $result): string => $result->entry_date->format('j M Y'))->values()->all(),
            'phq9' => $classifications->map(fn (NlpClassificationResult $result): ?int => $severityScore($result->phq9_severity))->values()->all(),
            'gad7' => $classifications->map(fn (NlpClassificationResult $result): ?int => $severityScore($result->gad7_severity))->values()->all(),
            'symptomCounts' => $classifications
                ->flatMap(fn (NlpClassificationResult $result): array => $result->symptoms ?? [])
                ->countBy()
                ->sortDesc()
                ->take(8)
                ->all(),
        ];
    }
}
