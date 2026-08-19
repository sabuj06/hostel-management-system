<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::with('student', 'feeStructure')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('student', fn ($q2) => $q2->where('name', 'like', "%{$request->search}%")
                    ->orWhere('student_uid', 'like', "%{$request->search}%"));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Mark past-due unpaid/partial invoices as overdue for accurate display
        Invoice::whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);

        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $students = Student::where('status', 'active')->get();
        $feeStructures = FeeStructure::active()->get();

        return view('invoices.create', compact('students', 'feeStructures'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['exists:students,id'],
            'fee_structure_id' => ['required', 'exists:fee_structures,id'],
            'period' => ['nullable', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data, $request) {
            foreach ($data['student_ids'] as $studentId) {
                Invoice::create([
                    'invoice_no' => $this->generateInvoiceNo(),
                    'student_id' => $studentId,
                    'fee_structure_id' => $data['fee_structure_id'],
                    'period' => $data['period'] ?? null,
                    'amount' => $data['amount'],
                    'due_date' => $data['due_date'],
                    'status' => 'unpaid',
                    'generated_by' => $request->user()->id,
                    'remarks' => $data['remarks'] ?? null,
                ]);
            }
        });

        $count = count($data['student_ids']);

        return redirect()->route('invoices.index')->with('status', "{$count} invoice(s) generated successfully.");
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('student', 'feeStructure', 'payments.receivedBy');

        return view('invoices.show', compact('invoice'));
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->payments()->exists()) {
            return back()->with('status', 'Cannot delete an invoice that already has payments recorded.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')->with('status', 'Invoice deleted successfully.');
    }

    private function generateInvoiceNo(): string
    {
        do {
            $no = 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
        } while (Invoice::where('invoice_no', $no)->exists());

        return $no;
    }
}