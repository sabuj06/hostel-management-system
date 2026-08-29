<?php

namespace App\Services;

use App\Models\MealMenu;
use App\Models\Notice;
use App\Models\Student;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Student-facing Hostel Assistant.
 *
 * The assistant can answer:
 * - Student's room / bed
 * - Fees / dues / invoices
 * - Today's meal menu
 * - Individual meals
 * - Recent notices
 * - Basic hostel assistant questions
 * - Software information
 * - Development team information
 *
 * Gemini is used when configured.
 * Keyword fallback works without Gemini.
 */
class HostelChatbotService
{
    public function ask(Student $student, string $question): string
    {
        $question = trim($question);

        if ($question === '') {
            return "Please type a question and I'll try to help you.";
        }

        $context = $this->buildContext($student);

        /*
        |--------------------------------------------------------------------------
        | Software / General Questions
        |--------------------------------------------------------------------------
        |
        | These answers do not require Gemini or student database information.
        |
        */

        $generalAnswer = $this->answerGeneralQuestion($question);

        if ($generalAnswer !== null) {
            return $generalAnswer;
        }

        /*
        |--------------------------------------------------------------------------
        | Gemini
        |--------------------------------------------------------------------------
        */

        if (config('services.gemini.key')) {
            $answer = $this->askGemini($question, $context);

            if ($answer !== null) {
                return $answer;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Keyword Fallback
        |--------------------------------------------------------------------------
        */

        return $this->answerWithKeywords($question, $context);
    }

    /**
     * Build only the data this student is allowed to see.
     */
    private function buildContext(Student $student): array
    {
        $allocation = $student->currentAllocation;

        $hostelId = $allocation?->room?->floor?->block?->hostel_id;

        $unpaidInvoices = $student->invoices()
            ->whereIn('status', [
                'unpaid',
                'partial',
                'overdue'
            ])
            ->get();

        $totalDue =
            $unpaidInvoices->sum('amount')
            - $unpaidInvoices->sum('paid_amount');

        $today = strtolower(now()->format('l'));

        $todayMenu = MealMenu::where(function ($q) use ($hostelId) {
                $q->where('hostel_id', $hostelId)
                    ->orWhereNull('hostel_id');
            })
            ->where('day_of_week', $today)
            ->get()
            ->keyBy('meal_type');

        $notices = Notice::published()
            ->where(function ($q) {
                $q->where('audience', 'all')
                    ->orWhere('audience', 'students');
            })
            ->latest('publish_date')
            ->limit(5)
            ->get([
                'title',
                'body',
                'publish_date'
            ]);

        return [
            'student_name' => $student->name,

            'room' => $allocation?->room?->room_number,

            'bed' => $allocation?->bed?->bed_number,

            'total_due' => round($totalDue, 2),

            'unpaid_invoice_count' => $unpaidInvoices->count(),

            'today_breakfast' =>
                $todayMenu['breakfast']->items ?? 'Not set',

            'today_lunch' =>
                $todayMenu['lunch']->items ?? 'Not set',

            'today_dinner' =>
                $todayMenu['dinner']->items ?? 'Not set',

            'notices' => $notices
                ->map(function ($notice) {
                    return "{$notice->title}: "
                        . Str::limit($notice->body, 150);
                })
                ->toArray(),
        ];
    }

    /**
     * General questions which can be answered without student data.
     */
    private function answerGeneralQuestion(string $question): ?string
    {
        $q = mb_strtolower($question);

        /*
        |--------------------------------------------------------------------------
        | Greeting
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'hello') ||
            str_contains($q, 'hi') ||
            str_contains($q, 'hey') ||
            str_contains($q, 'হাই') ||
            str_contains($q, 'হ্যালো')
        ) {
            return "Hello! 👋 I'm your Hostel Assistant. How can I help you today?";
        }

        /*
        |--------------------------------------------------------------------------
        | Who are you?
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'who are you') ||
            str_contains($q, 'about yourself') ||
            str_contains($q, 'yourself') ||
            str_contains($q, 'what are you') ||
            str_contains($q, 'তুমি কে') ||
            str_contains($q, 'তোমার সম্পর্কে')
        ) {
            return "I'm the Hostel Assistant, an AI assistant inside this Hostel Management Software. I can help you with hostel-related information such as your room, fees, mess menu, invoices, notices, and other available student information.";
        }

        /*
        |--------------------------------------------------------------------------
        | What can you do?
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'what can you do') ||
            str_contains($q, 'what information') ||
            str_contains($q, 'what do you know') ||
            str_contains($q, 'help me') ||
            str_contains($q, 'কি করতে পারো') ||
            str_contains($q, 'কী করতে পারো')
        ) {
            return "I can help with your room and bed, fee dues, invoices, today's breakfast/lunch/dinner, recent notices, and information about this Hostel Management Software.";
        }

        /*
        |--------------------------------------------------------------------------
        | Who made the software?
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'who made this software') ||
            str_contains($q, 'who created this software') ||
            str_contains($q, 'who developed this software') ||
            str_contains($q, 'who built this software') ||
            str_contains($q, 'who made this') ||
            str_contains($q, 'software made by') ||
            str_contains($q, 'software created by') ||
            str_contains($q, 'software developer') ||
            str_contains($q, 'কে সফটওয়্যার বানিয়েছে') ||
            str_contains($q, 'কে এই সফটওয়্যার বানিয়েছে')
        ) {
            return "This Hostel Management Software was developed by:\n\nTeam Lead: Sabuj Adak\n\nTeam Members:\n1. Soumen Maity\n2. Priyanka Bhowmick\n3. Jishu Barik";
        }

        /*
        |--------------------------------------------------------------------------
        | Team Lead
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'team lead') ||
            str_contains($q, 'project lead') ||
            str_contains($q, 'lead developer') ||
            str_contains($q, 'team leader')
        ) {
            return "The Team Lead of this Hostel Management Software is Sabuj Adak.";
        }

        /*
        |--------------------------------------------------------------------------
        | Team Members
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'team members') ||
            str_contains($q, 'members of the team') ||
            str_contains($q, 'development team') ||
            str_contains($q, 'developers')
        ) {
            return "The development team consists of:\n1. Sabuj Adak — Team Lead\n2. Soumen Maity — Team Member\n3. Priyanka Bhowmick — Team Member\n4. Jishu Barik — Team Member";
        }

        /*
        |--------------------------------------------------------------------------
        | Ask about specific team member
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'sabuj adak') ||
            str_contains($q, 'soumen maity') ||
            str_contains($q, 'priyanka bhowmick') ||
            str_contains($q, 'jishu barik')
        ) {
            return "The Hostel Management Software development team includes Sabuj Adak as Team Lead, with Soumen Maity, Priyanka Bhowmick, and Jishu Barik as team members.";
        }

        /*
        |--------------------------------------------------------------------------
        | Software name
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'software name') ||
            str_contains($q, 'name of this software') ||
            str_contains($q, 'what software is this') ||
            str_contains($q, 'what is this software')
        ) {
            return "This is a Hostel Management Software designed to manage student, hostel, room, bed, fee, invoice, mess, complaint, notice, visitor, attendance, and other hostel-related operations.";
        }

        /*
        |--------------------------------------------------------------------------
        | Thanks
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'thank you') ||
            str_contains($q, 'thanks') ||
            str_contains($q, 'ধন্যবাদ')
        ) {
            return "You're welcome! 😊 I'm always happy to help.";
        }

        /*
        |--------------------------------------------------------------------------
        | Goodbye
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'bye') ||
            str_contains($q, 'goodbye')
        ) {
            return "Goodbye! 👋 Have a great day.";
        }

        /*
        |--------------------------------------------------------------------------
        | Cricket / General knowledge
        |--------------------------------------------------------------------------
        |
        | We intentionally don't pretend to be a general internet assistant.
        |
        */

        if (
            str_contains($q, 'cricket') ||
            str_contains($q, 'football') ||
            str_contains($q, 'google')
        ) {
            return "I can answer basic general questions when AI support is available, but my main purpose is to help you with this Hostel Management Software and your hostel-related information.";
        }

        return null;
    }

    /**
     * Gemini-powered answer.
     */
    private function askGemini(
        string $question,
        array $context
    ): ?string {
        try {
            $model = config('services.gemini.model');

            $apiKey = config('services.gemini.key');

            $contextText =
                "Student name: {$context['student_name']}\n"

                . 'Room: '
                . (
                    $context['room']
                        ? "Room {$context['room']}, Bed {$context['bed']}"
                        : 'Not allocated yet'
                )
                . "\n"

                . "Total due: ₹{$context['total_due']} "
                . "across {$context['unpaid_invoice_count']} invoice(s)\n"

                . "Today's breakfast: {$context['today_breakfast']}\n"

                . "Today's lunch: {$context['today_lunch']}\n"

                . "Today's dinner: {$context['today_dinner']}\n"

                . "Recent notices:\n- "
                . implode("\n- ", $context['notices']);

            $prompt =
                "You are a helpful Hostel Assistant inside a Hostel Management Software.\n\n"

                . "Your main purpose is to help students with hostel-related information.\n"

                . "You may answer questions about:\n"
                . "- Room and bed\n"
                . "- Fees and dues\n"
                . "- Invoices\n"
                . "- Today's breakfast, lunch and dinner\n"
                . "- Recent notices\n"
                . "- Basic information about this software\n"
                . "- The software development team\n\n"

                . "IMPORTANT RULES:\n"
                . "1. Never invent student-specific data.\n"
                . "2. Never reveal another student's information.\n"
                . "3. Never invent room numbers, bed numbers, fees or menus.\n"
                . "4. If student-specific information is not present, say that it is unavailable.\n"
                . "5. Keep the answer concise and friendly.\n"
                . "6. Reply in the same language used by the student.\n"
                . "7. Plain text only. Do not use markdown.\n\n"

                . "SOFTWARE DEVELOPMENT TEAM:\n"
                . "Team Lead: Sabuj Adak\n"
                . "Team Member 1: Soumen Maity\n"
                . "Team Member 2: Priyanka Bhowmick\n"
                . "Team Member 3: Jishu Barik\n\n"

                . "STUDENT DATA:\n"
                . $contextText
                . "\n\nSTUDENT QUESTION:\n"
                . $question;

            $response = Http::timeout(10)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt
                                ]
                            ]
                        ]
                    ],

                    'generationConfig' => [
                        'temperature' => 0.3,
                        'maxOutputTokens' => 300,
                    ],
                ]
            );

            if (! $response->successful()) {
                return null;
            }

            $text = trim(
                $response->json(
                    'candidates.0.content.parts.0.text'
                ) ?? ''
            );

            $text = $this->stripMarkdown($text);

            return $text !== '' ? $text : null;

        } catch (\Throwable $e) {

            report($e);

            return null;
        }
    }

    /**
     * Deterministic fallback when Gemini is unavailable.
     */
    private function answerWithKeywords(
        string $question,
        array $context
    ): string {
        $q = mb_strtolower($question);

        /*
        |--------------------------------------------------------------------------
        | Student name
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'my name') ||
            str_contains($q, 'what is my name') ||
            str_contains($q, 'আমার নাম')
        ) {
            return "Your name is {$context['student_name']}.";
        }

        /*
        |--------------------------------------------------------------------------
        | Room / Bed
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'room') ||
            str_contains($q, 'bed') ||
            str_contains($q, 'রুম') ||
            str_contains($q, 'বেড')
        ) {
            if ($context['room']) {
                return "You're currently allocated to Room {$context['room']}, Bed {$context['bed']}.";
            }

            return "You don't have a room allocated yet. Please contact the hostel office.";
        }

        /*
        |--------------------------------------------------------------------------
        | Fee / Due / Invoice
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'fee') ||
            str_contains($q, 'fees') ||
            str_contains($q, 'due') ||
            str_contains($q, 'dues') ||
            str_contains($q, 'payment') ||
            str_contains($q, 'invoice') ||
            str_contains($q, 'বকেয়া') ||
            str_contains($q, 'বকেয়া') ||
            str_contains($q, 'ফি') ||
            str_contains($q, 'টাকা')
        ) {
            if ($context['total_due'] > 0) {
                return "You have ₹{$context['total_due']} due across {$context['unpaid_invoice_count']} invoice(s). Please check My Invoices for details.";
            }

            return "You have no outstanding dues right now. 🎉";
        }

        /*
        |--------------------------------------------------------------------------
        | Breakfast
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'breakfast') ||
            str_contains($q, 'morning food') ||
            str_contains($q, 'সকালের খাবার') ||
            str_contains($q, 'সকাল')
        ) {
            return "Today's breakfast is: {$context['today_breakfast']}.";
        }

        /*
        |--------------------------------------------------------------------------
        | Lunch
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'lunch') ||
            str_contains($q, 'দুপুরের খাবার') ||
            str_contains($q, 'দুপুর')
        ) {
            return "Today's lunch is: {$context['today_lunch']}.";
        }

        /*
        |--------------------------------------------------------------------------
        | Dinner
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'dinner') ||
            str_contains($q, 'night food') ||
            str_contains($q, 'রাতের খাবার') ||
            str_contains($q, 'রাত')
        ) {
            return "Today's dinner is: {$context['today_dinner']}.";
        }

        /*
        |--------------------------------------------------------------------------
        | Mess / Food / Menu
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'mess') ||
            str_contains($q, 'menu') ||
            str_contains($q, 'food') ||
            str_contains($q, 'meal') ||
            str_contains($q, 'খাবার')
        ) {
            return "Today's menu — Breakfast: {$context['today_breakfast']}; Lunch: {$context['today_lunch']}; Dinner: {$context['today_dinner']}.";
        }

        /*
        |--------------------------------------------------------------------------
        | Notices
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'notice') ||
            str_contains($q, 'notices') ||
            str_contains($q, 'announcement') ||
            str_contains($q, 'নোটিশ') ||
            str_contains($q, 'ঘোষণা')
        ) {
            if (count($context['notices']) > 0) {
                return "Latest notices:\n- "
                    . implode("\n- ", $context['notices']);
            }

            return "There are no recent notices.";
        }

        /*
        |--------------------------------------------------------------------------
        | Today's information
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'today') ||
            str_contains($q, "today's") ||
            str_contains($q, 'আজ')
        ) {
            return "For today, I can provide your mess menu and recent hostel notices. You can ask me specifically about breakfast, lunch, dinner or notices.";
        }

        /*
        |--------------------------------------------------------------------------
        | Help
        |--------------------------------------------------------------------------
        */

        if (
            str_contains($q, 'help') ||
            str_contains($q, 'information') ||
            str_contains($q, 'কি জানো') ||
            str_contains($q, 'কী জানো')
        ) {
            return "I can help with your room, bed, fees, invoices, breakfast, lunch, dinner, mess menu, recent notices, and information about this Hostel Management Software.";
        }

        /*
        |--------------------------------------------------------------------------
        | Default
        |--------------------------------------------------------------------------
        */

        return "I can help with your room, bed, fees, invoices, today's breakfast, lunch, dinner, mess menu, recent notices, and information about this Hostel Management Software. Please ask me a hostel-related question.";
    }

    /**
     * Remove markdown characters from Gemini output.
     */
    private function stripMarkdown(string $text): string
    {
        $text = preg_replace('/\*\*(.*?)\*\*/s', '$1', $text);

        $text = preg_replace('/\*(.*?)\*/s', '$1', $text);

        $text = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $text);

        $text = preg_replace('/^#{1,6}\s*/m', '', $text);

        $text = preg_replace('/^[-*]\s+/m', '', $text);

        return trim($text);
    }
}