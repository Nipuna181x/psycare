<?php

namespace App\Console\Commands;

use App\Services\GeminiTextToSpeech;
use App\Services\GoogleTextToSpeech;
use App\Services\ScreenerAnalyzer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('screener:generate-audio {--force : Replace existing question audio files}')]
#[Description('Generate and locally cache Google TTS audio for the PHQ-9 and GAD-7 questions')]
class GenerateScreenerAudio extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(GoogleTextToSpeech $textToSpeech, GeminiTextToSpeech $sinhalaTextToSpeech): int
    {
        $directory = public_path('audio/screener');
        File::ensureDirectoryExists($directory);

        foreach (ScreenerAnalyzer::QUESTIONS as $question) {
            $path = $directory.'/'.$question['key'].'.mp3';

            if (File::exists($path) && ! $this->option('force')) {
                $this->components->twoColumnDetail($question['key'], '<fg=yellow>already exists</>');

                continue;
            }

            $spokenQuestion = 'Over the last two weeks, how often have you been bothered by '.$question['question'].'?';
            File::put($path, $textToSpeech->synthesize($spokenQuestion));
            $this->components->twoColumnDetail($question['key'], '<fg=green>generated</>');
        }

        $sinhalaDirectory = $directory.'/si';
        File::ensureDirectoryExists($sinhalaDirectory);

        foreach (ScreenerAnalyzer::QUESTIONS as $question) {
            $path = $sinhalaDirectory.'/'.$question['key'].'.wav';

            if (File::exists($path) && ! $this->option('force')) {
                $this->components->twoColumnDetail('si/'.$question['key'], '<fg=yellow>already exists</>');

                continue;
            }

            $spokenQuestion = 'පසුගිය සති දෙක තුළ, කොපමණ වාරයක් ඔබට පහත තත්ත්වය බලපෑවාද? '.$question['question_si'].'?';
            File::put($path, $sinhalaTextToSpeech->synthesize($spokenQuestion));
            $this->components->twoColumnDetail('si/'.$question['key'], '<fg=green>generated</>');
        }

        $clarificationPath = $directory.'/clarification.mp3';

        if (! File::exists($clarificationPath) || $this->option('force')) {
            File::put($clarificationPath, $textToSpeech->synthesize('I could not determine how often that happened. Please answer again and tell me whether it was not at all, several days, more than half the days, or nearly every day.'));
            $this->components->twoColumnDetail('clarification', '<fg=green>generated</>');
        }

        $sinhalaClarificationPath = $sinhalaDirectory.'/clarification.wav';

        if (! File::exists($sinhalaClarificationPath) || $this->option('force')) {
            File::put($sinhalaClarificationPath, $sinhalaTextToSpeech->synthesize('එය කොපමණ වාරයක් සිදු වූවාදැයි මට තේරුම් ගැනීමට නොහැකි විය. කිසිසේත් නැත, දින කිහිපයක්, දිනවලින් අඩකට වඩා, හෝ සෑම දිනකම පාහේ යනුවෙන් නැවත පිළිතුරු දෙන්න.'));
            $this->components->twoColumnDetail('si/clarification', '<fg=green>generated</>');
        }

        $this->components->info('Screener audio is ready.');

        return self::SUCCESS;
    }
}
