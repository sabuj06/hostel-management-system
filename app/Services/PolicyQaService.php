<?php

namespace App\Services;

use App\Models\PolicyChunk;
use Illuminate\Support\Facades\Http;

/**
 * Retrieval-Augmented Generation for hostel policy questions.
 *
 * Retrieval: MySQL FULLTEXT search over policy_chunks (no vector DB needed —
 * relevance ranking is handled natively by MySQL's MATCH...AGAINST).
 *
 * Generation: the top matching chunks are handed to Gemini as the ONLY
 * source of truth, with an instruction to answer strictly from them and
 * cite which document each fact came from. Without a Gemini key, the raw
 * matched excerpts are returned directly — still useful, just not phrased
 * into a sentence.
 */
class PolicyQaService
{
    public function answer(string $question): array
    {
        $chunks = PolicyChunk::search($question)->with('document')->get();

        if ($chunks->isEmpty()) {
            return [
                'answer' => "I couldn't find anything about that in the uploaded hostel policy documents. Please contact the hostel office directly, or ask about a different topic.",
                'sources' => [],
            ];
        }

        if (config('services.gemini.key')) {
            $generated = $this->generateWithGemini($question, $chunks);
            if ($generated !== null) {
                return [
                    'answer' => $generated,
                    'sources' => $chunks->pluck('document.title')->unique()->values()->toArray(),
                ];
            }
        }

        // Fallback: return the raw matched excerpts, clearly labeled by source
        $excerpts = $chunks->map(fn ($c) => "From \"{$c->document->title}\":\n" . \Illuminate\Support\Str::limit($c->content, 300))->implode("\n\n");

        return [
            'answer' => "Here's what I found in the hostel policy documents:\n\n{$excerpts}",
            'sources' => $chunks->pluck('document.title')->unique()->values()->toArray(),
        ];
    }

    private function generateWithGemini(string $question, $chunks): ?string
    {
        try {
            $model = config('services.gemini.model');
            $apiKey = config('services.gemini.key');

            $context = $chunks->map(fn ($c) => "[Source: {$c->document->title}]\n{$c->content}")->implode("\n\n---\n\n");

            $prompt = "You are a hostel policy assistant. Answer the student's question using ONLY the policy excerpts below — never guess or add rules that aren't stated. "
                . "If the excerpts don't fully answer the question, say what you found and suggest contacting the hostel office for the rest. "
                . "Mention which document(s) your answer is based on. Reply in the same language as the question. Keep it under 5 sentences.\n\n"
                . "POLICY EXCERPTS:\n{$context}\n\nQUESTION: {$question}";

            $response = Http::timeout(15)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['temperature' => 0.2, 'maxOutputTokens' => 350],
                ]);

            if (! $response->successful()) {
                return null;
            }

            $text = trim($response->json('candidates.0.content.parts.0.text') ?? '');

            return $text !== '' ? $text : null;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    // Splits raw extracted text into overlap-free, roughly-sized chunks —
    // paragraph-aware so it doesn't cut sentences in half where avoidable.
    public function chunkText(string $text, int $targetLength = 700): array
    {
        $paragraphs = preg_split('/\n\s*\n/', trim($text));
        $chunks = [];
        $current = '';

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if ($paragraph === '') {
                continue;
            }

            if (mb_strlen($current) + mb_strlen($paragraph) > $targetLength && $current !== '') {
                $chunks[] = trim($current);
                $current = '';
            }

            $current .= ($current === '' ? '' : "\n\n") . $paragraph;

            // A single paragraph longer than the target gets its own chunk immediately
            if (mb_strlen($current) > $targetLength) {
                $chunks[] = trim($current);
                $current = '';
            }
        }

        if (trim($current) !== '') {
            $chunks[] = trim($current);
        }

        return $chunks;
    }
}