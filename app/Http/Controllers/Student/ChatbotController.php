<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Student;
use App\Services\HostelChatbotService;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    private function currentStudent(Request $request): Student
    {
        $student = Student::where('user_id', $request->user()->id)->first();

        if (! $student) {
            abort(403, 'Your account is not linked to a student profile yet.');
        }

        return $student;
    }

    // Loads recent chat history when the widget first opens
    public function history(Request $request)
    {
        $student = $this->currentStudent($request);

        $messages = ChatMessage::where('student_id', $student->id)
            ->latest()
            ->limit(20)
            ->get()
            ->sortBy('id')
            ->values();

        return response()->json(['messages' => $messages]);
    }

    // AJAX: send a question, get an answer, both get saved to history
    public function ask(Request $request, HostelChatbotService $chatbot)
    {
        $student = $this->currentStudent($request);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        ChatMessage::create([
            'student_id' => $student->id,
            'role' => 'user',
            'message' => $data['message'],
        ]);

        $answer = $chatbot->ask($student, $data['message']);

        $assistantMessage = ChatMessage::create([
            'student_id' => $student->id,
            'role' => 'assistant',
            'message' => $answer,
        ]);

        return response()->json(['message' => $assistantMessage]);
    }
}