<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\MessCut;
use App\Models\MessRate;
use App\Models\Student;
use Illuminate\Http\Request;

class MessCutController extends Controller
{
    public function index(Request $request)
    {
        $messCuts = MessCut::with('student', 'markedBy')
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('student', fn ($q2) => $q2->where('name', 'like', "%{$request->search}%"));
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

        MessCut::create([...$data, 'marked_by' => $request->user()->id]);

        return redirect()->route('mess-cuts.index')->with('status', 'Mess cut recorded successfully.');
    }

    public function destroy(MessCut $messCut)
    {
        $messCut->delete();

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
            MessRate::updateOrCreate(['hostel_id' => $hostelId], ['rate_per_day' => $rate]);
        }

        return back()->with('status', 'Mess rates updated successfully.');
    }
}