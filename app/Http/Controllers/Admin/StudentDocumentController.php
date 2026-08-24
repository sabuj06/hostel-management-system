<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentDocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = StudentDocument::with('student', 'reviewedBy')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('student', fn ($q2) => $q2->where('name', 'like', "%{$request->search}%")
                    ->orWhere('student_uid', 'like', "%{$request->search}%"));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $pendingCount = StudentDocument::where('status', 'pending')->count();

        return view('student-documents.index', compact('documents', 'pendingCount'));
    }

    public function download(StudentDocument $studentDocument)
    {
        if (! Storage::exists($studentDocument->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::download($studentDocument->file_path, $studentDocument->original_filename ?? $studentDocument->title);
    }

    // AJAX: approve or reject a document
    public function review(Request $request, StudentDocument $studentDocument)
    {
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'review_note' => ['nullable', 'string', 'max:500'],
        ]);

        $studentDocument->update([
            'status' => $data['status'],
            'review_note' => $data['review_note'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'status' => $studentDocument->status,
            'reviewed_by' => $request->user()->name,
        ]);
    }

    public function destroy(StudentDocument $studentDocument)
    {
        Storage::delete($studentDocument->file_path);
        $studentDocument->delete();

        return back()->with('status', 'Document removed.');
    }
}