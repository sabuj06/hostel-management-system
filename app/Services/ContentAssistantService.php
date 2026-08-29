<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
                return [
                    'text' => $translated,
                    'translated' => true,
                    'note' => null,
                ];
            }

            return [
                'text' => $text,
                'translated' => false,
                'note' => 'AI translation is temporarily unavailable because the Gemini API quota has been exceeded. Please try again later.',
            ];
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
                . "Turn it into a polished, professional hostel notice.\n"
                . "Respond ONLY with valid JSON, no markdown:\n"
                . '{"title":"short clear title, maximum 10 words","body":"3-6 sentence formal notice body in the same language as the note, addressed to students"}';

            $url = 'https://generativelanguage.googleapis.com/v1beta/models/'
                . config('services.gemini.model')
                . ':generateContent?key='
                . config('services.gemini.key');

            $response = Http::timeout(10)->post(
                $url,
                [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt,
                                ],
                            ],
                        ],
                    ],

                    'generationConfig' => [
                        'temperature' => 0.4,
                        'maxOutputTokens' => 300,
                    ],
                ]
            );

            if (! $response->successful()) {
                Log::error('Gemini Notice Generation Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $text = trim(
                $response->json(
                    'candidates.0.content.parts.0.text'
                ) ?? ''
            );

            // Remove markdown code fences if Gemini returns them
            $text = preg_replace(
                '/^```(?:json)?\s*|\s*```$/i',
                '',
                $text
            );

            $parsed = json_decode(trim($text), true);

            if (
                ! is_array($parsed) ||
                empty($parsed['title']) ||
                empty($parsed['body'])
            ) {
                return null;
            }

            return [
                'title' => $parsed['title'],
                'body' => $parsed['body'],
            ];

        } catch (\Throwable $e) {

            report($e);

            return null;
        }
    }

    private function generateNoticeFallback(string $draft): array
    {
        $words = preg_split('/\s+/', $draft);

        $title = implode(
            ' ',
            array_slice($words, 0, 8)
        );

        $title = rtrim(
            $title,
            ".,!?;: "
        );

        if ($title !== '') {
            $title =
                mb_strtoupper(
                    mb_substr($title, 0, 1)
                )
                . mb_substr($title, 1);
        }

        $body =
            "Dear Students,\n\n"
            . $draft
            . "\n\n"
            . "Please take note of the above and act accordingly.\n\n"
            . "Thank you,\n"
            . "Hostel Administration";

        return [
            'title' => $title !== ''
                ? $title
                : 'Notice',

            'body' => $body,
        ];
    }

    private function translateWithGemini(
        string $text,
        string $targetLanguage
    ): ?string {

        try {

            $prompt =
                "Translate the following hostel notice into {$targetLanguage}. "
                . "Preserve the meaning and tone exactly.\n\n"
                . "TEXT:\n{$text}\n\n"
                . "Respond in EXACTLY this format and nothing else "
                . "(no reasoning, no extra commentary before or after):\n"
                . "TRANSLATION: <the translated text here>";

            $url =
                'https://generativelanguage.googleapis.com/v1beta/models/'
                . config('services.gemini.model')
                . ':generateContent?key='
                . config('services.gemini.key');

            $response = Http::timeout(15)->post(
                $url,
                [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt,
                                ],
                            ],
                        ],
                    ],

                    'generationConfig' => [
                        'temperature' => 0.2,
                        'maxOutputTokens' => 1500,
                    ],
                ]
            );

            if (! $response->successful()) {
                Log::error('Gemini Translation Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $raw = trim(
                $response->json(
                    'candidates.0.content.parts.0.text'
                ) ?? ''
            );

            $translated = $this->extractTranslation($raw);

            return $translated !== ''
                ? $translated
                : null;

        } catch (\Throwable $e) {

            report($e);

            return null;
        }
    }

    // Pulls the actual translation out of Gemini's raw response.
    // Looks for the "TRANSLATION:" marker we asked for; if the model
    // ignored the format, falls back to cleaning up the whole response.
    private function extractTranslation(string $raw): string
    {
        if (preg_match('/TRANSLATION\s*:\s*(.+)/is', $raw, $matches)) {
            $text = trim($matches[1]);
        } else {
            // Model didn't use the marker — use the last non-empty line,
            // which is usually the final answer after any reasoning.
            $lines = array_values(array_filter(
                array_map('trim', explode("\n", $raw)),
                fn ($line) => $line !== ''
            ));

            $text = end($lines) ?: $raw;
        }

        // Remove stray leading/trailing quotes or markdown artifacts.
        $text = trim($text, "\"'“”‘’*` \t\n\r");

        return trim($text);
    }
}