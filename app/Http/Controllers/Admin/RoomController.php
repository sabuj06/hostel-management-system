<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\ActivityLogger;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = Room::with('floor.block.hostel')
            ->withCount('beds')
            ->when(
                $request->floor_id,
                fn ($q) => $q->where('floor_id', $request->floor_id)
            )
            ->when(
                $request->search,
                fn ($q) => $q->where(
                    'room_number',
                    'like',
                    "%{$request->search}%"
                )
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $floors = Floor::where('status', 'active')->get();

        return view(
            'rooms.index',
            compact('rooms', 'floors')
        );
    }

    public function create()
    {
        $floors = Floor::where('status', 'active')->get();

        return view(
            'rooms.create',
            compact('floors')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create Room
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $data = $request->validate([
            'floor_id' => [
                'required',
                'exists:floors,id'
            ],
            'room_number' => [
                'required',
                'string',
                'max:50'
            ],
            'room_type' => [
                'required',
                'in:single,double,triple,dormitory'
            ],
            'capacity' => [
                'required',
                'integer',
                'min:1',
                'max:20'
            ],
            'monthly_rent' => [
                'required',
                'numeric',
                'min:0'
            ],
            'status' => [
                'required',
                'in:active,maintenance,inactive'
            ],
        ]);

        $room = null;

        // Create room + beds inside transaction
        DB::transaction(function () use (
            $data,
            &$room
        ) {
            $room = Room::create($data);

            for (
                $i = 1;
                $i <= $data['capacity'];
                $i++
            ) {
                $room->beds()->create([
                    'bed_number' => (string) $i
                ]);
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        ActivityLogger::log(
            action: 'created',
            module: 'rooms',
            description: "Room created: {$room->room_number} with {$room->capacity} beds",
            subject: $room,
            newValues: $room->fresh()->toArray()
        );

        return redirect()
            ->route('rooms.index')
            ->with(
                'status',
                'Room created with beds successfully.'
            );
    }

    public function edit(Room $room)
    {
        $floors = Floor::where('status', 'active')->get();

        return view(
            'rooms.edit',
            compact('room', 'floors')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Room
    |--------------------------------------------------------------------------
    */
    public function update(
        Request $request,
        Room $room
    ) {
        $data = $request->validate([
            'floor_id' => [
                'required',
                'exists:floors,id'
            ],
            'room_number' => [
                'required',
                'string',
                'max:50'
            ],
            'room_type' => [
                'required',
                'in:single,double,triple,dormitory'
            ],
            'capacity' => [
                'required',
                'integer',
                'min:1',
                'max:20'
            ],
            'monthly_rent' => [
                'required',
                'numeric',
                'min:0'
            ],
            'status' => [
                'required',
                'in:active,maintenance,inactive'
            ],
        ]);

        // Old values before update
        $oldValues = $room->toArray();

        $room->update($data);

        $room->refresh();

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        ActivityLogger::log(
            action: 'updated',
            module: 'rooms',
            description: "Room updated: {$room->room_number}",
            subject: $room,
            oldValues: $oldValues,
            newValues: $room->toArray()
        );

        return redirect()
            ->route('rooms.index')
            ->with(
                'status',
                'Room updated successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Room
    |--------------------------------------------------------------------------
    */
    public function destroy(Room $room)
    {
        // Save old values before deletion
        $oldValues = $room->toArray();

        $roomNumber = $room->room_number;

        $room->delete();

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        ActivityLogger::log(
            action: 'deleted',
            module: 'rooms',
            description: "Room deleted: {$roomNumber}",
            subject: $room,
            oldValues: $oldValues
        );

        return back()->with(
            'status',
            'Room deleted successfully.'
        );
    }
}