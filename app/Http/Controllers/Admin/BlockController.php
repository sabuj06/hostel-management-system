<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Hostel;
use Illuminate\Http\Request;
use App\ActivityLogger;

class BlockController extends Controller
{
    public function index(Request $request)
    {
        $blocks = Block::with('hostel')
            ->withCount('floors')
            ->when(
                $request->hostel_id,
                fn ($q) => $q->where('hostel_id', $request->hostel_id)
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $hostels = Hostel::active()->get();

        return view(
            'blocks.index',
            compact('blocks', 'hostels')
        );
    }

    public function create()
    {
        $hostels = Hostel::active()->get();

        return view(
            'blocks.create',
            compact('hostels')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create Block
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $data = $request->validate([
            'hostel_id' => ['required', 'exists:hostels,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $block = Block::create($data);

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        ActivityLogger::log(
            action: 'created',
            module: 'blocks',
            description: "Block created: {$block->name}",
            subject: $block,
            newValues: $block->fresh()->toArray()
        );

        return redirect()
            ->route('blocks.index')
            ->with(
                'status',
                'Block created successfully.'
            );
    }

    public function edit(Block $block)
    {
        $hostels = Hostel::active()->get();

        return view(
            'blocks.edit',
            compact('block', 'hostels')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Block
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Block $block)
    {
        $data = $request->validate([
            'hostel_id' => ['required', 'exists:hostels,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        // Old values
        $oldValues = $block->toArray();

        $block->update($data);

        $block->refresh();

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        ActivityLogger::log(
            action: 'updated',
            module: 'blocks',
            description: "Block updated: {$block->name}",
            subject: $block,
            oldValues: $oldValues,
            newValues: $block->toArray()
        );

        return redirect()
            ->route('blocks.index')
            ->with(
                'status',
                'Block updated successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Block
    |--------------------------------------------------------------------------
    */
    public function destroy(Block $block)
    {
        // Save values before deletion
        $oldValues = $block->toArray();

        $blockName = $block->name;

        $block->delete();

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        ActivityLogger::log(
            action: 'deleted',
            module: 'blocks',
            description: "Block deleted: {$blockName}",
            subject: $block,
            oldValues: $oldValues
        );

        return back()
            ->with(
                'status',
                'Block deleted successfully.'
            );
    }
}