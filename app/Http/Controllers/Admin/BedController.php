<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Room;
use Illuminate\Http\Request;
use App\ActivityLogger;

class BedController extends Controller
{
    public function index(Request $request)
    {
        $beds = Bed::with('room.floor.block.hostel')
            ->when(
                $request->room_id,
                fn ($q) => $q->where('room_id', $request->room_id)
            )
            ->when(
                $request->status,
                fn ($q) => $q->where('status', $request->status)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $rooms = Room::where('status', 'active')->get();

        return view(
            'beds.index',
            compact('beds', 'rooms')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create Bed
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $data = $request->validate([
            'room_id' => [
                'required',
                'exists:rooms,id'
            ],
            'bed_number' => [
                'required',
                'string',
                'max:20'
            ],
        ]);

        $bed = Bed::create($data);

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        ActivityLogger::log(
            action: 'created',
            module: 'beds',
            description: "Bed created: {$bed->bed_number} in Room ID {$bed->room_id}",
            subject: $bed,
            newValues: $bed->fresh()->toArray()
        );

        return back()->with(
            'status',
            'Bed added successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Bed Status
    |--------------------------------------------------------------------------
    */
    public function updateStatus(
        Request $request,
        Bed $bed
    ) {
        $data = $request->validate([
            'status' => [
                'required',
                'in:available,occupied,maintenance'
            ],
        ]);

        // Old values before update
        $oldValues = $bed->toArray();

        $oldStatus = $bed->status;

        $bed->update([
            'status' => $data['status']
        ]);

        $bed->refresh();

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        ActivityLogger::log(
            action: 'updated',
            module: 'beds',
            description: "Bed {$bed->bed_number} status changed from {$oldStatus} to {$bed->status}",
            subject: $bed,
            oldValues: $oldValues,
            newValues: $bed->toArray()
        );

        return response()->json([
            'success' => true,
            'status' => $bed->status
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Bed
    |--------------------------------------------------------------------------
    */
    public function destroy(Bed $bed)
    {
        // Save old values before deletion
        $oldValues = $bed->toArray();

        $bedNumber = $bed->bed_number;

        $bed->delete();

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        ActivityLogger::log(
            action: 'deleted',
            module: 'beds',
            description: "Bed deleted: {$bedNumber}",
            subject: $bed,
            oldValues: $oldValues
        );

        return back()->with(
            'status',
            'Bed removed successfully.'
        );
    }
} 