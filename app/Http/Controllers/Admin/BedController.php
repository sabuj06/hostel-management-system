<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Room;
use Illuminate\Http\Request;

class BedController extends Controller
{
    public function index(Request $request)
    {
        $beds = Bed::with('room.floor.block.hostel')
            ->when($request->room_id, fn ($q) => $q->where('room_id', $request->room_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $rooms = Room::where('status', 'active')->get();

        return view('beds.index', compact('beds', 'rooms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'bed_number' => ['required', 'string', 'max:20'],
        ]);

        Bed::create($data);

        return back()->with('status', 'Bed added successfully.');
    }

    // AJAX endpoint: quick status change from the bed grid (jQuery driven)
    public function updateStatus(Request $request, Bed $bed)
    {
        $request->validate([
            'status' => ['required', 'in:available,occupied,maintenance'],
        ]);

        $bed->update(['status' => $request->status]);

        return response()->json(['success' => true, 'status' => $bed->status]);
    }

    public function destroy(Bed $bed)
    {
        $bed->delete();

        return back()->with('status', 'Bed removed successfully.');
    }
}