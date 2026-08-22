<?php

namespace App\Services;

use InvalidArgumentException;

class ScreenerAnalyzer
{
    public const QUESTIONS = [
        ['key' => 'phq_1', 'instrument' => 'phq9', 'question' => 'Little interest or pleasure in doing things', 'question_si' => 'දේවල් කිරීමට ඇති උනන්දුව හෝ සතුට අඩු වීම'],
        ['key' => 'phq_2', 'instrument' => 'phq9', 'question' => 'Feeling down, depressed, or hopeless', 'question_si' => 'දුකෙන්, මානසිකව වැටුණු බවක් හෝ බලාපොරොත්තු රහිත බවක් දැනීම'],
        ['key' => 'phq_3', 'instrument' => 'phq9', 'question' => 'Trouble falling or staying asleep, or sleeping too much', 'question_si' => 'නින්දට යාමට හෝ දිගටම නිදා සිටීමට අපහසු වීම, නැතහොත් ඕනෑවට වඩා නිදා ගැනීම'],
        ['key' => 'phq_4', 'instrument' => 'phq9', 'question' => 'Feeling tired or having little energy', 'question_si' => 'වෙහෙසට පත් බවක් හෝ ශක්තිය අඩු බවක් දැනීම'],
        ['key' => 'phq_5', 'instrument' => 'phq9', 'question' => 'Poor appetite or overeating', 'question_si' => 'ආහාර රුචිය අඩු වීම හෝ ඕනෑවට වඩා ආහාර ගැනීම'],
        ['key' => 'phq_6', 'instrument' => 'phq9', 'question' => 'Feeling bad about yourself — or that you are a failure or have let yourself or your family down', 'question_si' => 'තමන් ගැන නරක හැඟීමක් ඇති වීම, තමන් අසාර්ථක බවක් හෝ තමන් හෝ පවුල අසරණ කළ බවක් දැනීම'],
        ['key' => 'phq_7', 'instrument' => 'phq9', 'question' => 'Trouble concentrating on things, such as reading or watching television', 'question_si' => 'කියවීම හෝ රූපවාහිනිය නැරඹීම වැනි දේවලට අවධානය යොමු කිරීමට අපහසු වීම'],
        ['key' => 'phq_8', 'instrument' => 'phq9', 'question' => 'Moving or speaking noticeably slowly, or being so fidgety or restless that you move around more than usual', 'question_si' => 'අන් අයට පෙනෙන තරම් සෙමින් ගමන් කිරීම හෝ කතා කිරීම, නැතහොත් සාමාන්‍යයට වඩා වැඩිපුර එහා මෙහා යන තරම් නොසන්සුන් වීම'],
        ['key' => 'phq_9', 'instrument' => 'phq9', 'question' => 'Thoughts that you would be better off dead, or of hurting yourself in some way', 'question_si' => 'මිය ගියා නම් වඩා හොඳයි කියා හෝ කිසියම් ආකාරයකින් තමන්ට හානි කරගැනීම ගැන සිතුවිලි ඇති වීම'],
        ['key' => 'gad_1', 'instrument' => 'gad7', 'question' => 'Feeling nervous, anxious, or on edge', 'question_si' => 'චංචල, කනස්සල්ලෙන් හෝ දැඩි ආතතියකින් සිටින බවක් දැනීම'],
        ['key' => 'gad_2', 'instrument' => 'gad7', 'question' => 'Not being able to stop or control worrying', 'question_si' => 'කනස්සල්ල නතර කිරීමට හෝ පාලනය කිරීමට නොහැකි වීම'],
        ['key' => 'gad_3', 'instrument' => 'gad7', 'question' => 'Worrying too much about different things', 'question_si' => 'විවිධ දේ ගැන ඕනෑවට වඩා කනස්සලු වීම'],
        ['key' => 'gad_4', 'instrument' => 'gad7', 'question' => 'Trouble relaxing', 'question_si' => 'සැහැල්ලුවෙන් සිටීමට අපහසු වීම'],
        ['key' => 'gad_5', 'instrument' => 'gad7', 'question' => 'Being so restless that it is hard to sit still', 'question_si' => 'නිශ්චලව වාඩි වී සිටීමට අපහසු තරම් නොසන්සුන් වීම'],
        ['key' => 'gad_6', 'instrument' => 'gad7', 'question' => 'Becoming easily annoyed or irritable', 'question_si' => 'පහසුවෙන් කෝපයට හෝ නොරිස්සුමට පත් වීම'],
        ['key' => 'gad_7', 'instrument' => 'gad7', 'question' => 'Feeling afraid as if something awful might happen', 'question_si' => 'භයානක දෙයක් සිදු විය හැකි සේ බියක් දැනීම'],
    ];

    public const SCALE = [0 => 'Not at all', 1 => 'Several days', 2 => 'More than half the days', 3 => 'Nearly every day'];

    /**
     * @param  array<int, array{key: string, score: int}>  $answers
     * @return array{phq9: array{total: int, severity: string, self_harm_flag: bool}, gad7: array{total: int, severity: string}, requires_immediate_escalation: bool}
     */
    public function analyze(array $answers): array
    {
        $scores = collect($answers)->mapWithKeys(fn (array $answer): array => [$answer['key'] => $answer['score']]);

        foreach (self::QUESTIONS as $question) {
            $score = $scores->get($question['key']);

            if (! is_int($score) || $score < 0 || $score > 3) {
                throw new InvalidArgumentException("Missing or invalid score for {$question['key']}.");
            }
        }

        $phqTotal = collect(range(1, 9))->sum(fn (int $item): int => $scores->get("phq_{$item}"));
        $gadTotal = collect(range(1, 7))->sum(fn (int $item): int => $scores->get("gad_{$item}"));
        $selfHarmFlag = $scores->get('phq_9') >= 1;

        return [
            'phq9' => [
                'total' => $phqTotal,
                'severity' => match (true) {
                    $phqTotal <= 4 => 'minimal', $phqTotal <= 9 => 'mild', $phqTotal <= 14 => 'moderate',
                    $phqTotal <= 19 => 'moderately_severe', default => 'severe',
                },
                'self_harm_flag' => $selfHarmFlag,
            ],
            'gad7' => [
                'total' => $gadTotal,
                'severity' => match (true) {
                    $gadTotal <= 4 => 'minimal', $gadTotal <= 9 => 'mild', $gadTotal <= 14 => 'moderate', default => 'severe',
                },
            ],
            'requires_immediate_escalation' => $selfHarmFlag,
        ];
    }
}
