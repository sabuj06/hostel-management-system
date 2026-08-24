<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ReportInsightService
{
    public function summarize(array $summary, array $trendInsights): string
    {
        if (config('services.gemini.key')) {
            $result = $this->summarizeWithGemini($summary, $trendInsights);
            if ($result !== null) {
                return $result;
            }
        }

        return $this->summarizeWithTemplate($summary, $trendInsights);
    }

    private function summarizeWithGemini(array $summary, array $trendInsights): ?string
    {
        try {
            $data = "Active students: {$summary['total_students']}\n"
                . "Occupancy rate: {$summary['occupancy_rate']}%\n"
                . "Total revenue collected: ₹{$summary['total_revenue']}\n"
                . "Outstanding dues: ₹{$summary['outstanding_dues']}\n"
                . "Open complaints: {$summary['open_complaints']}\n"
                . "Visitors today: {$summary['visitors_today']}\n\n"
                . 'Trends: ' . implode(' ', array_column($trendInsights, 'message'));

            $prompt = "You are writing a short weekly progress summary for a hostel admin, based on this dashboard data. "
                . "Write 3-5 sentences, professional tone, highlighting anything that needs attention. Do not invent numbers not given. "
                . "IMPORTANT: reply in PLAIN TEXT only — no markdown, no asterisks, no bold/italic markers, no bullet points, no links.\n\nDATA:\n{$data}";

            $response = Http::timeout(12)->post(
                'https://generativelanguage.googleapis.com/v1beta/models/' . config('services.gemini.model') . ':generateContent?key=' . config('services.gemini.key'),
                [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['temperature' => 0.3, 'maxOutputTokens' => 300],
                ]
            );

            if (! $response->successful()) {
                return null;
            }

            $text = trim($response->json('candidates.0.content.parts.0.text') ?? '');
            $text = $this->stripMarkdown($text);

            return $text !== '' ? $text : null;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    // Safety net: even with the "plain text only" instruction, models
    // occasionally slip in markdown syntax. Strip it so raw ** or [text](url)
    // never leaks into the UI.
    private function stripMarkdown(string $text): string
    {
        // Strip **bold** pairs first (legitimate matched pairs)
        $text = preg_replace('/\*\*(.*?)\*\*/s', '$1', $text);
        // Any remaining asterisks are stray artifacts, not real *italic* pairs —
        // deleting them outright avoids swallowing large chunks of text between
        // two unrelated single asterisks (this was truncating output before).
        $text = str_replace('*', '', $text);
        $text = preg_replace('/\[(.*?)\]\(.*?\)/', '$1', $text); // [text](link)
        $text = preg_replace('/^#{1,6}\s*/m', '', $text);        // # headings
        $text = preg_replace('/^-\s+/m', '', $text);             // bullet markers

        return trim($text);
    }

    private function summarizeWithTemplate(array $summary, array $trendInsights): string
    {
        $lines = [];
        $lines[] = "This hostel currently has {$summary['total_students']} active students with an occupancy rate of {$summary['occupancy_rate']}%.";
        $lines[] = "Total revenue collected so far is ₹" . number_format($summary['total_revenue']) . ", with ₹" . number_format($summary['outstanding_dues']) . ' still outstanding.';

        if ($summary['open_complaints'] > 0) {
            $lines[] = "There are currently {$summary['open_complaints']} open complaint(s) requiring attention.";
        } else {
            $lines[] = 'There are no open complaints at the moment.';
        }

        foreach ($trendInsights as $insight) {
            $lines[] = $insight['message'];
        }

        return implode(' ', $lines);
    }
}