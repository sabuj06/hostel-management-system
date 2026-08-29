<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\MessCut;
use App\Models\MessRate;
use App\Models\Student;
use Illuminate\Http\Request;
use App\ActivityLogger;

class MessCutController extends Controller
{
    public function index(Request $request)
    {
        $messCuts = MessCut::with('student', 'markedBy')
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('student', fn ($q2) =>
                    $q2->where('name', 'like', "%{$request->search}%")
                );
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('mess-cuts.index', compact('messCuts'));
    }

    public function create()
    {
        $students = Student::where('status', 'active')->get();

        return view('mess-cuts.create', compact('students'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $messCut = MessCut::create([
            ...$data,
            'marked_by' => $request->user()->id,
        ]);

        ActivityLogger::log(
            'mess_cut_created',
            "Mess cut recorded for student ID {$messCut->student_id} from {$messCut->from_date} to {$messCut->to_date}"
        );

        return redirect()
            ->route('mess-cuts.index')
            ->with('status', 'Mess cut recorded successfully.');
    }

    public function destroy(MessCut $messCut)
    {
        $studentId = $messCut->student_id;
        $fromDate = $messCut->from_date;
        $toDate = $messCut->to_date;

        $messCut->delete();

        ActivityLogger::log(
            'mess_cut_deleted',
            "Mess cut removed for student ID {$studentId} from {$fromDate} to {$toDate}"
        );

        return back()->with('status', 'Mess cut removed successfully.');
    }

    public function rates()
    {
        $hostels = Hostel::active()->get();
        $rates = MessRate::pluck('rate_per_day', 'hostel_id');

        return view('mess-cuts.rates', compact('hostels', 'rates'));
    }

    public function saveRates(Request $request)
    {
        $data = $request->validate([
            'rates' => ['required', 'array'],
            'rates.*' => ['required', 'numeric', 'min:0'],
        ]);

        foreach ($data['rates'] as $hostelId => $rate) {
            MessRate::updateOrCreate(
                ['hostel_id' => $hostelId],
                ['rate_per_day' => $rate]
            );

            ActivityLogger::log(
                'mess_rate_updated',
                "Mess rate updated for hostel ID {$hostelId}: ₹{$rate} per day"
            );
        }

        return back()->with('status', 'Mess rates updated successfully.');
    }
}