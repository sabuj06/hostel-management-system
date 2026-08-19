<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = Room::with('floor.block.hostel')->withCount('beds')
            ->when($request->floor_id, fn ($q) => $q->where('floor_id', $request->floor_id))
            ->when($request->search, fn ($q) => $q->where('room_number', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $floors = Floor::where('status', 'active')->get();

        return view('rooms.index', compact('rooms', 'floors'));
    }

    public function create()
    {
        $floors = Floor::where('status', 'active')->get();

        return view('rooms.create', compact('floors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'floor_id' => ['required', 'exists:floors,id'],
            'room_number' => ['required', 'string', 'max:50'],
            'room_type' => ['required', 'in:single,double,triple,dormitory'],
            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,maintenance,inactive'],
        ]);

        // Auto-generate beds equal to capacity, inside a transaction so
        // a room is never created without its matching bed records.
        DB::transaction(function () use ($data) {
            $room = Room::create($data);

            for ($i = 1; $i <= $data['capacity']; $i++) {
                $room->beds()->create(['bed_number' => (string) $i]);
            }
        });

        return redirect()->route('rooms.index')->with('status', 'Room created with beds successfully.');
    }

    public function edit(Room $room)
    {
        $floors = Floor::where('status', 'active')->get();

        return view('rooms.edit', compact('room', 'floors'));
    }

    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'floor_id' => ['required', 'exists:floors,id'],
            'room_number' => ['required', 'string', 'max:50'],
            'room_type' => ['required', 'in:single,double,triple,dormitory'],
            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
            'monthly_rent' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,maintenance,inactive'],
        ]);

        $room->update($data);

        return redirect()->route('rooms.index')->with('status', 'Room updated successfully.');
    }

    public function destroy(Room $room)
    {
        $room->delete();

        return back()->with('status', 'Room deleted successfully.');
    }
}