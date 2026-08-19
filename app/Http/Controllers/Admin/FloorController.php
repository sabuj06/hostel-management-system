<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Floor;
use Illuminate\Http\Request;

class FloorController extends Controller
{
    public function index(Request $request)
    {
        $floors = Floor::with('block.hostel')->withCount('rooms')
            ->when($request->block_id, fn ($q) => $q->where('block_id', $request->block_id))
            ->orderBy('floor_number')
            ->paginate(10)
            ->withQueryString();

        $blocks = Block::where('status', 'active')->get();

        return view('floors.index', compact('floors', 'blocks'));
    }

    public function create()
    {
        $blocks = Block::where('status', 'active')->get();

        return view('floors.create', compact('blocks'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'block_id' => ['required', 'exists:blocks,id'],
            'name' => ['required', 'string', 'max:255'],
            'floor_number' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        Floor::create($data);

        return redirect()->route('floors.index')->with('status', 'Floor created successfully.');
    }

    public function edit(Floor $floor)
    {
        $blocks = Block::where('status', 'active')->get();

        return view('floors.edit', compact('floor', 'blocks'));
    }

    public function update(Request $request, Floor $floor)
    {
        $data = $request->validate([
            'block_id' => ['required', 'exists:blocks,id'],
            'name' => ['required', 'string', 'max:255'],
            'floor_number' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $floor->update($data);

        return redirect()->route('floors.index')->with('status', 'Floor updated successfully.');
    }

    public function destroy(Floor $floor)
    {
        $floor->delete();

        return back()->with('status', 'Floor deleted successfully.');
    }
}