<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PolicyChunk;
use App\Models\PolicyDocument;
use App\Services\PolicyQaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\ActivityLogger;

class PolicyDocumentController extends Controller
{
    public function index()
    {
        $documents = PolicyDocument::with('uploadedBy')
            ->latest()
            ->paginate(10);

        return view('policy-documents.index', compact('documents'));
    }

    public function create()
    {
        return view('policy-documents.create');
    }

    public function store(Request $request, PolicyQaService $qaService)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf,txt', 'max:10240'],
        ]);

        $file = $request->file('file');
        $path = $file->store('policy-documents');

        $document = PolicyDocument::create([
            'title' => $data['title'],
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'uploaded_by' => $request->user()->id,
            'status' => 'processing',
        ]);

        try {
            $text = $this->extractText(
                $file->getRealPath(),
                $file->getClientOriginalExtension()
            );

            if (trim($text) === '') {
                throw new \RuntimeException(
                    'No extractable text found in this file.'
                );
            }

            $chunks = $qaService->chunkText($text);

            DB::transaction(function () use ($document, $chunks) {
                foreach ($chunks as $index => $content) {
                    PolicyChunk::create([
                        'policy_document_id' => $document->id,
                        'chunk_index' => $index,
                        'content' => $content,
                    ]);
                }

                $document->update([
                    'status' => 'ready',
                    'chunk_count' => count($chunks),
                ]);
            });

            ActivityLogger::log(
                action: 'created',
                module: 'policy_documents',
                description: "Policy document '{$document->title}' uploaded and indexed successfully with " . count($chunks) . " section(s)",
                subject: $document,
                newValues: $document->fresh()->toArray()
            );

            $message = count($chunks)
                . ' section(s) indexed and ready for student Q&A.';
        } catch (\Throwable $e) {
            report($e);

            $document->update([
                'status' => 'failed',
            ]);

            ActivityLogger::log(
                action: 'upload_failed',
                module: 'policy_documents',
                description: "Policy document '{$document->title}' uploaded but text extraction/indexing failed",
                subject: $document,
                newValues: [
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ]
            );

            $message = 'Upload saved, but text extraction failed: '
                . $e->getMessage();
        }

        return redirect()
            ->route('policy-documents.index')
            ->with('status', $message);
    }

    public function destroy(PolicyDocument $policyDocument)
    {
        $oldValues = $policyDocument->toArray();
        $documentTitle = $policyDocument->title;

        if ($policyDocument->file_path) {
            Storage::delete($policyDocument->file_path);
        }

        $policyDocument->delete();

        ActivityLogger::log(
            action: 'deleted',
            module: 'policy_documents',
            description: "Policy document '{$documentTitle}' deleted",
            subject: $policyDocument,
            oldValues: $oldValues
        );

        return back()->with('status', 'Policy document removed.');
    }

    // Extracts plain text from an uploaded PDF or .txt file.
    // Requires `composer require smalot/pdfparser` for PDF support;
    // .txt files always work with zero dependencies.
    private function extractText(string $path, string $extension): string
    {
        if (strtolower($extension) === 'txt') {
            return file_get_contents($path);
        }

        if (! class_exists(\Smalot\PdfParser\Parser::class)) {
            throw new \RuntimeException(
                'PDF support requires the smalot/pdfparser package. Run: composer require smalot/pdfparser — or upload a .txt file instead.'
            );
        }

        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($path);

        return $pdf->getText();
    }
}