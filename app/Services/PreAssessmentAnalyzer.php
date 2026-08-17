<?php

namespace App\Services;

use Illuminate\Support\Collection;

class PreAssessmentAnalyzer
{
    /**
     * Harm-related phrases that immediately flag an elevated risk level.
     *
     * @var array<int, string>
     */
    private const HARM_KEYWORDS = [
        'suicide', 'suicidal', 'kill myself', 'end my life', 'end it all',
        'self-harm', 'self harm', 'hurt myself', 'harm myself', 'harm others', 'hurt others',
    ];

    /**
     * Words that signal a "no" answer even though they contain a harm keyword's neighbourhood.
     *
     * @var array<int, string>
     */
    private const NEGATIONS = ['no', 'not', 'never', 'none', 'nope', "don't", 'dont'];

    /**
     * Run a deterministic, rule-based triage over the patient's pre-assessment answers.
     *
     * This is an automated safety net, not a clinical diagnosis — it exists so doctors
     * can prioritise their queue, and the risk level is always shown to them alongside
     * the raw answers so they can form their own judgement.
     *
     * @param  array<int, array{question: string, answer: string}>  $answers
     * @return array{risk_level: string, summary: string}
     */
    public function analyze(array $answers, ?int $moodRating): array
    {
        $lookup = collect($answers)->keyBy(fn (array $entry): string => $entry['key'] ?? $entry['question']);

        $safetyAnswer = mb_strtolower($lookup->get('safety')['answer'] ?? '');
        $sleepAnswer = mb_strtolower($lookup->get('sleep')['answer'] ?? '');
        $durationAnswer = mb_strtolower($lookup->get('duration')['answer'] ?? '');
        $severityAnswer = mb_strtolower($lookup->get('severity')['answer'] ?? '');
        $dailyImpactAnswer = mb_strtolower($lookup->get('daily_impact')['answer'] ?? '');

        $riskLevel = match (true) {
            $this->indicatesHarm($safetyAnswer) => 'elevated',
            $moodRating !== null && $moodRating <= 3 => 'moderate',
            $moodRating !== null && $moodRating <= 6 => 'moderate',
            $this->containsAny($severityAnswer, ['severe', 'constant', 'constantly', 'all the time', 'every day']) => 'moderate',
            $this->containsAny($sleepAnswer, ['insomnia', "can't sleep", 'cant sleep', 'no sleep']) => 'moderate',
            $this->containsAny($durationAnswer, ['months', 'years', 'weeks']) => 'moderate',
            $this->containsAny($dailyImpactAnswer, ['cannot', "can't", 'unable', 'stopped working', 'stopped going']) => 'moderate',
            default => 'low',
        };

        return [
            'risk_level' => $riskLevel,
            'summary' => $this->summarize($lookup, $moodRating, $riskLevel),
        ];
    }

    private function indicatesHarm(string $answer): bool
    {
        if ($answer === '') {
            return false;
        }

        if ($this->containsAny($answer, self::HARM_KEYWORDS)) {
            return ! $this->startsWithNegation($answer);
        }

        $firstWord = strtok(trim($answer), " \t\n,.");

        return in_array($firstWord, ['yes', 'sometimes', 'occasionally'], true);
    }

    private function startsWithNegation(string $answer): bool
    {
        $firstWord = strtok(trim($answer), " \t\n,.");

        return in_array($firstWord, self::NEGATIONS, true);
    }

    /**
     * @param  array<int, string>  $needles
     */
    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($haystack !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  Collection<string, array{question: string, answer: string}>  $lookup
     */
    private function summarize($lookup, ?int $moodRating, string $riskLevel): string
    {
        $answer = fn (string $key): string => $lookup->get($key)['answer'] ?? 'not specified';
        $notes = $lookup->get('notes')['answer'] ?? null;

        $sentences = [
            'Patient rates their mood '.($moodRating !== null ? $moodRating.'/10' : 'unrated').' over the past week.',
            'Main reason for booking: '.$answer('reason').'.',
            'Duration of symptoms: '.$answer('duration').'.',
            'Severity and frequency: '.$answer('severity').'.',
            'Triggers: '.$answer('triggers').'.',
            'Onset: '.$answer('onset').'.',
            'Impact on daily life: '.$answer('daily_impact').'.',
            'Impact on relationships: '.$answer('relationships').'.',
            'Sleep: '.$answer('sleep').'.',
            'Appetite: '.$answer('appetite').'.',
            'Mood pattern: '.$answer('mood').'.',
            'Mental health history: '.$answer('history').'.',
            'Current medication: '.$answer('medication').'.',
            'Support system: '.$answer('support').'.',
            'Safety check response: '.$answer('safety').'.',
        ];

        if ($notes) {
            $sentences[] = 'Additional notes from patient: '.$notes.'.';
        }

        $sentences[] = match ($riskLevel) {
            'elevated' => 'Automated triage flagged this as ELEVATED risk — please review before or at the start of the consultation.',
            'moderate' => 'Automated triage flagged this as MODERATE risk.',
            default => 'Automated triage flagged this as LOW risk.',
        };

        return implode(' ', $sentences);
    }
}
