<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Room;
use App\Models\RoomAllocation;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomAllocationController extends Controller
{
    public function index(Request $request)
    {
        $allocations = RoomAllocation::with('student', 'room', 'bed')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('student', fn ($q2) => $q2->where('name', 'like', "%{$request->search}%")
                    ->orWhere('student_uid', 'like', "%{$request->search}%"));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('room-allocations.index', compact('allocations'));
    }

    public function create()
    {
        $students = Student::unallocated()->where('status', 'active')->get();
        $rooms = Room::where('status', 'active')->withCount(['beds as available_beds_count' => fn ($q) => $q->where('status', 'available')])->get();

        return view('room-allocations.create', compact('students', 'rooms'));
    }

    // AJAX: return available beds for a selected room
    public function availableBeds(Room $room)
    {
        $beds = $room->beds()->where('status', 'available')->get(['id', 'bed_number']);

        return response()->json($beds);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'bed_id' => ['required', 'exists:beds,id'],
            'allocated_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string'],
        ]);

        $bed = Bed::findOrFail($data['bed_id']);

        if ($bed->status !== 'available') {
            return back()->withInput()->withErrors(['bed_id' => 'Selected bed is no longer available.']);
        }

        DB::transaction(function () use ($data, $bed, $request) {
            RoomAllocation::create([
                ...$data,
                'allocated_by' => $request->user()->id,
                'status' => 'active',
            ]);

            $bed->update(['status' => 'occupied']);
        });

        return redirect()->route('room-allocations.index')->with('status', 'Room allocated successfully.');
    }

    // Move a student from their current bed to a new bed/room
    public function transfer(Request $request, RoomAllocation $allocation)
    {
        $data = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'bed_id' => ['required', 'exists:beds,id'],
            'remarks' => ['nullable', 'string'],
        ]);

        $newBed = Bed::findOrFail($data['bed_id']);

        if ($newBed->status !== 'available') {
            return back()->withErrors(['bed_id' => 'Selected bed is no longer available.']);
        }

        DB::transaction(function () use ($allocation, $data, $newBed, $request) {
            // Close current allocation
            $allocation->update([
                'status' => 'transferred',
                'vacated_date' => now(),
            ]);
            $allocation->bed->update(['status' => 'available']);

            // Create new allocation
            RoomAllocation::create([
                'student_id' => $allocation->student_id,
                'room_id' => $data['room_id'],
                'bed_id' => $data['bed_id'],
                'allocated_by' => $request->user()->id,
                'allocated_date' => now(),
                'status' => 'active',
                'remarks' => $data['remarks'] ?? null,
            ]);

            $newBed->update(['status' => 'occupied']);
        });

        return redirect()->route('room-allocations.index')->with('status', 'Student transferred successfully.');
    }

    // End a student's stay in their current bed
    public function checkout(RoomAllocation $allocation)
    {
        if ($allocation->status !== 'active') {
            return back()->with('status', 'This allocation is already closed.');
        }

        DB::transaction(function () use ($allocation) {
            $allocation->update([
                'status' => 'checked_out',
                'vacated_date' => now(),
            ]);
            $allocation->bed->update(['status' => 'available']);
        });

        return back()->with('status', 'Student checked out successfully.');
    }
}