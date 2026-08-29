<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\ActivityLogger;

class StudentDocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = StudentDocument::with('student', 'reviewedBy')
            ->when($request->status, fn ($q) =>
                $q->where('status', $request->status)
            )
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('student', function ($q2) use ($request) {
                    $q2->where('name', 'like', "%{$request->search}%")
                       ->orWhere('student_uid', 'like', "%{$request->search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $pendingCount = StudentDocument::where('status', 'pending')->count();

        return view(
            'student-documents.index',
            compact('documents', 'pendingCount')
        );
    }

    public function download(StudentDocument $studentDocument)
    {
        if (! Storage::disk('public')->exists($studentDocument->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download(
            $studentDocument->file_path,
            $studentDocument->original_filename ?? $studentDocument->title
        );
    }

    public function review(
        Request $request,
        StudentDocument $studentDocument
    ) {
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'review_note' => ['nullable', 'string', 'max:500'],
        ]);

        $oldValues = $studentDocument->toArray();

        $studentDocument->update([
            'status' => $data['status'],
            'review_note' => $data['review_note'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $studentDocument->refresh();

        ActivityLogger::log(
            action: 'reviewed',
            module: 'student_documents',
            description: "Student document '{$studentDocument->title}' was {$data['status']}",
            subject: $studentDocument,
            oldValues: $oldValues,
            newValues: $studentDocument->toArray()
        );

        return response()->json([
            'success' => true,
            'status' => $studentDocument->status,
            'reviewed_by' => $request->user()->name,
        ]);
    }

    public function destroy(StudentDocument $studentDocument)
    {
        $oldValues = $studentDocument->toArray();
        $documentTitle = $studentDocument->title;
        $studentId = $studentDocument->student_id;

        Storage::disk('public')->delete(
            $studentDocument->file_path
        );

        $studentDocument->delete();

        ActivityLogger::log(
            action: 'deleted',
            module: 'student_documents',
            description: "Student document '{$documentTitle}' deleted for student ID {$studentId}",
            subject: $studentDocument,
            oldValues: $oldValues
        );

        return back()->with(
            'status',
            'Document removed.'
        );
    }
}