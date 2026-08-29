<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Floor;
use Illuminate\Http\Request;
use App\ActivityLogger;

class FloorController extends Controller
{
    public function index(Request $request)
    {
        $floors = Floor::with('block.hostel')->withCount('rooms')
            ->when(
                $request->block_id,
                fn ($q) => $q->where('block_id', $request->block_id)
            )
            ->orderBy('floor_number')
            ->paginate(10)
            ->withQueryString();

        $blocks = Block::where('status', 'active')->get();

        return view(
            'floors.index',
            compact('floors', 'blocks')
        );
    }

    public function create()
    {
        $blocks = Block::where('status', 'active')->get();

        return view(
            'floors.create',
            compact('blocks')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create Floor
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $data = $request->validate([
            'block_id' => ['required', 'exists:blocks,id'],
            'name' => ['required', 'string', 'max:255'],
            'floor_number' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $floor = Floor::create($data);

        ActivityLogger::log(
            action: 'created',
            module: 'floors',
            description: "Floor created: {$floor->name}",
            subject: $floor,
            newValues: $floor->fresh()->toArray()
        );

        return redirect()
            ->route('floors.index')
            ->with(
                'status',
                'Floor created successfully.'
            );
    }

    public function edit(Floor $floor)
    {
        $blocks = Block::where('status', 'active')->get();

        return view(
            'floors.edit',
            compact('floor', 'blocks')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Floor
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Floor $floor)
    {
        $data = $request->validate([
            'block_id' => ['required', 'exists:blocks,id'],
            'name' => ['required', 'string', 'max:255'],
            'floor_number' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $oldValues = $floor->toArray();

        $floor->update($data);

        $floor->refresh();

        ActivityLogger::log(
            action: 'updated',
            module: 'floors',
            description: "Floor updated: {$floor->name}",
            subject: $floor,
            oldValues: $oldValues,
            newValues: $floor->toArray()
        );

        return redirect()
            ->route('floors.index')
            ->with(
                'status',
                'Floor updated successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Floor
    |--------------------------------------------------------------------------
    */
    public function destroy(Floor $floor)
    {
        $oldValues = $floor->toArray();

        $floorName = $floor->name;

        $floor->delete();

        ActivityLogger::log(
            action: 'deleted',
            module: 'floors',
            description: "Floor deleted: {$floorName}",
            subject: $floor,
            oldValues: $oldValues
        );

        return back()->with(
            'status',
            'Floor deleted successfully.'
        );
    }
}