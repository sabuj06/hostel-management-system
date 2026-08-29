<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StudentDocumentController extends Controller
{
    /**
     * Show student's documents.
     */
    public function index()
    {
        $student = Auth::user()->student;

        if (! $student) {
            abort(403, 'Student profile not found.');
        }

        $documents = StudentDocument::where('student_id', $student->id)
            ->latest()
            ->paginate(10);

        return view(
            'student-portal.documents.index',
            compact('documents')
        );
    }

    /**
     * Upload a new document.
     */
    public function store(Request $request)
    {
        $student = Auth::user()->student;

        if (! $student) {
            abort(403, 'Student profile not found.');
        }

        $data = $request->validate([
            'document_type' => [
                'required',
                'string',
                'max:100',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'document' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
        ]);

        $file = $request->file('document');

        $path = $file->store(
            'student-documents',
            'public'
        );

        StudentDocument::create([
            'student_id' => $student->id,
            'document_type' => $data['document_type'],
            'title' => $data['title'],
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'status' => 'pending',
        ]);

        return back()->with(
            'status',
            'Document uploaded successfully. It is waiting for admin verification.'
        );
    }

    /**
     * Delete student's own document.
     */
    public function destroy(StudentDocument $document)
    {
        $student = Auth::user()->student;

        if (! $student) {
            abort(403, 'Student profile not found.');
        }

        // Security: student can delete only their own document.
        if ($document->student_id !== $student->id) {
            abort(403, 'You are not allowed to delete this document.');
        }

        if ($document->file_path) {
            Storage::disk('public')->delete(
                $document->file_path
            );
        }

        $document->delete();

        return back()->with(
            'status',
            'Document deleted successfully.'
        );
    }
}