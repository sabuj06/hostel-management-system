<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\PolicyQaService;
use Illuminate\Http\Request;

class PolicyQaController extends Controller
{
    public function index()
    {
        return view('student-portal.policy-qa');
    }

    // AJAX: ask a question, get an answer grounded in uploaded policy documents
    public function ask(Request $request, PolicyQaService $qaService)
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:500'],
        ]);

        $result = $qaService->answer($data['question']);

        return response()->json($result);
    }
}