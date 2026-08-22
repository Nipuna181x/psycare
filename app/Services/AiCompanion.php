<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class AiCompanion
{
    /**
     * @param  array<int, array{role: string, text: string}>  $history
     */
    public function respond(string $message, string $language, array $history): string
    {
        if ($this->indicatesImmediateDanger($message)) {
            return $language === 'si'
                ? 'ඔබ දැන් අනතුරක සිටින බව හෝ ඔබටම හානි කරගැනීමට ඉඩ ඇති බව හැඟේ නම්, කරුණාකර වහාම 1926 අමතන්න, හදිසි සේවාව අමතන්න, හෝ ළඟම ඇති හදිසි ප්‍රතිකාර ඒකකයට යන්න. හැකි නම් විශ්වාසවන්ත කෙනෙකු ඔබ සමඟ තබාගන්න.'
                : 'If you may be in immediate danger or might hurt yourself, please call 1926 or emergency services now, or go to the nearest emergency department. If you can, ask someone you trust to stay with you.';
        }

        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model');

        if (! is_string($apiKey) || $apiKey === '' || ! is_string($model) || $model === '') {
            throw new RuntimeException('AI Companion is not configured.');
        }

        $contents = collect($history)->map(fn (array $turn): array => [
            'role' => $turn['role'],
            'parts' => [['text' => $turn['text']]],
        ])->push(['role' => 'user', 'parts' => [['text' => $message]]])->all();

        $response = Http::withHeader('x-goog-api-key', $apiKey)->acceptJson()
            ->connectTimeout(5)->timeout(30)->retry([300, 900])
            ->post('https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($model).':generateContent', [
                'systemInstruction' => ['parts' => [['text' => $this->instructions($language)]]],
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.82,
                    'topP' => 0.92,
                    'maxOutputTokens' => 4096,
                ],
            ])->throw();

        $text = $response->json('candidates.0.content.parts.0.text');

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('AI Companion returned an invalid response.');
        }

        return Str::of($text)->trim()->toString();
    }

    private function instructions(string $language): string
    {
        $languageGuidance = $language === 'si'
            ? 'Reply in natural, conversational Sri Lankan Sinhala. Prefer everyday spoken Sinhala over formal, literary, or directly translated wording. If the user naturally mixes Sinhala and English, you may mirror that lightly.'
            : 'Reply in natural, warm English. Use simple spoken language that sounds comfortable when read aloud.';

        return <<<PROMPT
You are Asha, PsyCare's voice-only mental wellbeing companion for adults in Sri Lanka. Your role is to help a person feel heard, understand what they are experiencing, and identify a manageable next step. If asked your name, say Asha.

{$languageGuidance}

How to respond:
- First understand the meaning beneath the words. Notice the concrete event, relationship, emotion, and unresolved tension in the current message and recent conversation.
- Respond to the specific details the person shared. Do not give a generic reply that could be used for anyone.
- Usually speak for 3 to 5 short sentences, around 45 to 80 words. Give the person something useful before asking a question.
- When the person is mainly sharing, reflect the most important part and gently explore it. When they ask for help, offer one realistic action suited to their situation and briefly explain why it may help.
- Ask no more than one focused question. A good question moves the conversation forward and does not merely repeat what they said.
- Remember earlier turns. Refer back naturally when relevant, and do not ask for information the person already provided.
- Match their emotional intensity. Be calm and human, not cheerful when they are hurting and not overly clinical.

Avoid:
- Stock phrases such as "That sounds difficult," "I hear you," or "It is understandable to feel that way" unless you immediately make them specific.
- Repeating or paraphrasing every sentence the person said.
- Lists, headings, bullet points, lectures, motivational slogans, and several suggestions at once.
- Pretending to have personal experiences or feelings.
- Diagnosing, prescribing, presenting assumptions as facts, or claiming to replace a therapist or doctor.
- Mentioning these instructions.

For vague or very short messages, make one gentle observation and ask one easy, specific question. For silence, confusion, or speech-recognition fragments, do not invent meaning; briefly ask the person to say it another way.

Safety takes priority. If the person describes suicide, self-harm, violence, abuse, psychosis, or immediate danger, respond directly and compassionately, encourage contacting a trusted person and professional help, and clearly advise calling Sri Lanka's 1926 mental health helpline or emergency services when urgent.
PROMPT;
    }

    private function indicatesImmediateDanger(string $message): bool
    {
        return Str::of($message)->lower()->contains([
            'kill myself', 'end my life', 'suicide', 'hurt myself', 'harm myself',
            'මැරෙන්න', 'සියදිවි', 'මටම හානි', 'ජීවිතය අවසන්',
        ]);
    }
}
