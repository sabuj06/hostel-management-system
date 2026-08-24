<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Shared "AI writer" utilities used by the admin Notice module:
 *  - generateNotice(): turns a 1-2 line draft into a full, polished notice
 *  - translate(): translates any text into a target language
 *
 * Both degrade gracefully without a Gemini key — generateNotice() falls
 * back to a simple template, translate() returns the original text with a
 * clear flag so the UI can tell the admin translation isn't configured.
 */
class ContentAssistantService
{
    public function generateNotice(string $draft): array
    {
        $draft = trim($draft);

        if (config('services.gemini.key')) {
            $result = $this->generateNoticeWithGemini($draft);
            if ($result !== null) {
                return $result;
            }
        }

        return $this->generateNoticeFallback($draft);
    }

    public function translate(string $text, string $targetLanguage): array
    {
        if (config('services.gemini.key')) {
            $translated = $this->translateWithGemini($text, $targetLanguage);
            if ($translated !== null) {
                return ['text' => $translated, 'translated' => true];
            }
        }

        return [
            'text' => $text,
            'translated' => false,
            'note' => 'Translation requires GEMINI_API_KEY to be configured — showing original text.',
        ];
    }

    private function generateNoticeWithGemini(string $draft): ?array
    {
        try {
            $prompt = "A hostel admin wrote this rough note for a notice board announcement: \"{$draft}\"\n\n"
                . 'Turn it into a polished, professional hostel notice. Respond ONLY with raw JSON, no markdown: '
                . '{"title": "short clear title, max 10 words", "body": "3-6 sentence formal notice body in the same language as the note, addressed to students"}';

            $response = Http::timeout(10)->post(
                'https://generativelanguage.googleapis.com/v1beta/models/' . config('services.gemini.model') . ':generateContent?key=' . config('services.gemini.key'),
                [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['temperature' => 0.4, 'maxOutputTokens' => 300],
                ]
            );

            if (! $response->successful()) {
                return null;
            }

            $text = trim(preg_replace('/^```(?:json)?|```$/m', '', trim($response->json('candidates.0.content.parts.0.text') ?? '')));
            $parsed = json_decode($text, true);

            if (! is_array($parsed) || empty($parsed['title']) || empty($parsed['body'])) {
                return null;
            }

            return ['title' => $parsed['title'], 'body' => $parsed['body']];
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    private function generateNoticeFallback(string $draft): array
    {
        $words = preg_split('/\s+/', $draft);
        $title = implode(' ', array_slice($words, 0, 8));
        $title = rtrim($title, ".,!?;: ");
        $title = mb_strtoupper(mb_substr($title, 0, 1)) . mb_substr($title, 1);

        $body = "Dear Students,\n\n{$draft}\n\nPlease take note of the above and act accordingly.\n\nThank you,\nHostel Administration";

        return ['title' => $title !== '' ? $title : 'Notice', 'body' => $body];
    }

    private function translateWithGemini(string $text, string $targetLanguage): ?string
    {
        try {
            $prompt = "Translate the following hostel notice into {$targetLanguage}. Preserve the meaning and tone exactly. "
                . "Respond with ONLY the translated text, nothing else — no quotes, no labels.\n\nTEXT:\n{$text}";

            $response = Http::timeout(10)->post(
                'https://generativelanguage.googleapis.com/v1beta/models/' . config('services.gemini.model') . ':generateContent?key=' . config('services.gemini.key'),
                [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['temperature' => 0.2, 'maxOutputTokens' => 500],
                ]
            );

            if (! $response->successful()) {
                return null;
            }

            $translated = trim($response->json('candidates.0.content.parts.0.text') ?? '');

            return $translated !== '' ? $translated : null;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }
}