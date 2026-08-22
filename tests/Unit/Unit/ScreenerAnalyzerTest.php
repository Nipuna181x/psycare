<?php

namespace Tests\Unit\Unit;

use App\Services\ScreenerAnalyzer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ScreenerAnalyzerTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_it_scores_phq9_and_gad7_severity_bands(): void
    {
        $answers = collect(ScreenerAnalyzer::QUESTIONS)->map(fn (array $question): array => [
            'key' => $question['key'],
            'score' => $question['instrument'] === 'phq9' ? 2 : 1,
        ])->all();

        $result = (new ScreenerAnalyzer)->analyze($answers);

        $this->assertSame(18, $result['phq9']['total']);
        $this->assertSame('moderately_severe', $result['phq9']['severity']);
        $this->assertSame(7, $result['gad7']['total']);
        $this->assertSame('mild', $result['gad7']['severity']);
    }

    public function test_positive_item_nine_always_requires_immediate_escalation(): void
    {
        $answers = collect(ScreenerAnalyzer::QUESTIONS)->map(fn (array $question): array => [
            'key' => $question['key'],
            'score' => $question['key'] === 'phq_9' ? 1 : 0,
        ])->all();

        $result = (new ScreenerAnalyzer)->analyze($answers);

        $this->assertSame(1, $result['phq9']['total']);
        $this->assertSame('minimal', $result['phq9']['severity']);
        $this->assertTrue($result['phq9']['self_harm_flag']);
        $this->assertTrue($result['requires_immediate_escalation']);
    }

    public function test_it_rejects_an_incomplete_screener(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ScreenerAnalyzer)->analyze([]);
    }
}
