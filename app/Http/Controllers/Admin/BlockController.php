<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Hostel;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    public function index(Request $request)
    {
        $blocks = Block::with('hostel')->withCount('floors')
            ->when($request->hostel_id, fn ($q) => $q->where('hostel_id', $request->hostel_id))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $hostels = Hostel::active()->get();

        return view('blocks.index', compact('blocks', 'hostels'));
    }

    public function create()
    {
        $hostels = Hostel::active()->get();

        return view('blocks.create', compact('hostels'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'hostel_id' => ['required', 'exists:hostels,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        Block::create($data);

        return redirect()->route('blocks.index')->with('status', 'Block created successfully.');
    }

    public function edit(Block $block)
    {
        $hostels = Hostel::active()->get();

        return view('blocks.edit', compact('block', 'hostels'));
    }

    public function update(Request $request, Block $block)
    {
        $data = $request->validate([
            'hostel_id' => ['required', 'exists:hostels,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $block->update($data);

        return redirect()->route('blocks.index')->with('status', 'Block updated successfully.');
    }

    public function destroy(Block $block)
    {
        $block->delete();

        return back()->with('status', 'Block deleted successfully.');
    }
}