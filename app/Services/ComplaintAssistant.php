<?php

namespace App\Services;

use App\Models\ComplaintCategory;

/**
 * Lightweight rule-based "AI" classifier for complaints.
 *
 * Analyzes the complaint title + description and suggests:
 *  - the most likely category (mapped to existing ComplaintCategory rows by name)
 *  - a priority level (low/medium/high/urgent)
 *  - a short human-readable reason, so staff can see WHY the suggestion was made
 *
 * This is intentionally dependency-free (no external API call) so it works
 * out of the box. Swap analyze() internals for a real LLM/API call later
 * (e.g. OpenAI/Claude) without changing the controller or the frontend
 * contract — the return shape stays the same.
 */
class ComplaintAssistant
{
    // category_keyword => [keywords...]
    private const CATEGORY_KEYWORDS = [
        'Electrical' => ['light', 'bulb', 'switch', 'socket', 'wiring', 'fan', 'electric', 'power', 'short circuit', 'current', 'plug'],
        'Plumbing' => ['water', 'tap', 'leak', 'pipe', 'toilet', 'bathroom', 'flush', 'drain', 'sink', 'basin', 'washroom'],
        'Furniture' => ['bed', 'chair', 'table', 'almirah', 'wardrobe', 'door', 'window', 'lock', 'broken', 'furniture', 'shelf'],
        'Cleanliness' => ['dirty', 'clean', 'garbage', 'trash', 'smell', 'dust', 'mosquito', 'pest', 'cockroach', 'unclean'],
        'Internet' => ['wifi', 'internet', 'network', 'router', 'connection', 'signal', 'lan'],
        'Security' => ['theft', 'stolen', 'lost', 'security', 'unsafe', 'stranger', 'cctv', 'guard', 'harassment', 'threat'],
    ];

    // Words that push priority up, with an associated weight
    private const URGENT_WORDS = ['fire', 'smoke', 'spark', 'shock', 'flooding', 'flood', 'collapsed', 'emergency', 'urgent', 'threat', 'harassment', 'assault'];
    private const HIGH_WORDS = ['leak', 'broken', 'not working', 'stolen', 'theft', 'unsafe', 'no water', 'no light', 'no power', 'security'];
    private const LOW_WORDS = ['minor', 'small', 'suggestion', 'request', 'cosmetic', 'when possible', 'whenever'];

    public function analyze(string $title, string $description): array
    {
        // If a Gemini API key is configured, try the real AI call first.
        if (config('services.gemini.key')) {
            $aiResult = $this->analyzeWithGemini($title, $description);
            if ($aiResult !== null) {
                return $aiResult;
            }
        }

        // Fallback: built-in keyword-based logic (always works, no API needed)
        return $this->analyzeWithKeywords($title, $description);
    }

    /**
     * Calls the Google Gemini API to classify the complaint.
     * Returns null on any failure so analyze() can fall back safely.
     */
    private function analyzeWithGemini(string $title, string $description): ?array
    {
        try {
            $categories = ComplaintCategory::pluck('name')->push('Other')->implode(', ');
            $model = config('services.gemini.model');
            $apiKey = config('services.gemini.key');

            $prompt = "Classify this hostel maintenance complaint. Categories: {$categories}. Priority options: low, medium, high, urgent.\n\nTitle: {$title}\nDescription: {$description}\n\nRespond ONLY with raw JSON, no markdown, no code fences: {\"category\": \"...\", \"priority\": \"...\", \"reason\": \"one short sentence\"}";

            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [[
                        'parts' => [['text' => $prompt]],
                    ]],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'maxOutputTokens' => 200,
                    ],
                ]);

            if (! $response->successful()) {
                return null;
            }

            $text = $response->json('candidates.0.content.parts.0.text');

            // Gemini sometimes wraps JSON in ```json ... ``` fences — strip them if present
            $text = trim(preg_replace('/^```(?:json)?|```$/m', '', trim($text)));

            $parsed = json_decode($text, true);

            if (! is_array($parsed) || empty($parsed['category']) || empty($parsed['priority'])) {
                return null;
            }

            $categoryModel = ComplaintCategory::where('name', $parsed['category'])->first();

            return [
                'suggested_category' => $parsed['category'],
                'suggested_category_id' => $categoryModel?->id,
                'category_confidence' => 90, // API-based suggestions are shown as high confidence
                'suggested_priority' => in_array($parsed['priority'], ['low', 'medium', 'high', 'urgent']) ? $parsed['priority'] : 'medium',
                'priority_reason' => $parsed['reason'] ?? 'Classified by AI.',
            ];
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    private function analyzeWithKeywords(string $title, string $description): array
    {
        $text = mb_strtolower($title . ' ' . $description);

        $category = $this->detectCategory($text);
        [$priority, $reason] = $this->detectPriority($text);

        return [
            'suggested_category' => $category['name'],
            'suggested_category_id' => $category['id'],
            'category_confidence' => $category['confidence'],
            'suggested_priority' => $priority,
            'priority_reason' => $reason,
        ];
    }

    private function detectCategory(string $text): array
    {
        $scores = [];

        foreach (self::CATEGORY_KEYWORDS as $categoryName => $keywords) {
            $hits = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    $hits++;
                }
            }
            if ($hits > 0) {
                $scores[$categoryName] = $hits;
            }
        }

        if (empty($scores)) {
            return ['name' => 'Other', 'id' => null, 'confidence' => 0];
        }

        arsort($scores);
        $topCategory = array_key_first($scores);
        $topHits = $scores[$topCategory];

        // Confidence: simple heuristic based on keyword hit count (capped at 95%)
        $confidence = min(95, 40 + $topHits * 20);

        $categoryModel = ComplaintCategory::where('name', $topCategory)->first();

        return [
            'name' => $topCategory,
            'id' => $categoryModel?->id,
            'confidence' => $confidence,
        ];
    }

    private function detectPriority(string $text): array
    {
        foreach (self::URGENT_WORDS as $word) {
            if (str_contains($text, $word)) {
                return ['urgent', "Contains urgent-risk keyword: \"{$word}\""];
            }
        }

        $highHits = [];
        foreach (self::HIGH_WORDS as $word) {
            if (str_contains($text, $word)) {
                $highHits[] = $word;
            }
        }
        if (count($highHits) >= 1) {
            return ['high', 'Mentions: ' . implode(', ', array_slice($highHits, 0, 3))];
        }

        foreach (self::LOW_WORDS as $word) {
            if (str_contains($text, $word)) {
                return ['low', "Sounds non-critical: \"{$word}\""];
            }
        }

        return ['medium', 'No strong urgency signals detected — defaulting to medium.'];
    }
}