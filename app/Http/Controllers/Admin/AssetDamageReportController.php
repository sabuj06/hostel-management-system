<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetDamageReport;
use App\Models\Room;
use Illuminate\Http\Request;

class AssetDamageReportController extends Controller
{
    /**
     * Display all asset damage reports.
     */
    public function index(Request $request)
    {
        $query = AssetDamageReport::with([
            'asset',
            'room',
            'reportedBy',
        ])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->paginate(10)->withQueryString();

        return view('asset-damage-reports.index', compact('reports'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $assets = Asset::orderBy('name')->get();

        $rooms = Room::orderBy('room_number')->get();

        return view(
            'asset-damage-reports.create',
            compact('assets', 'rooms')
        );
    }

    /**
     * Store damage report.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => [
                'required',
                'exists:assets,id',
            ],

            'room_id' => [
                'nullable',
                'exists:rooms,id',
            ],

            'description' => [
                'required',
                'string',
            ],
        ]);

        $validated['reported_by'] = auth()->id();
        $validated['status'] = 'reported';

        AssetDamageReport::create($validated);

        return redirect()
            ->route('asset-damage-reports.index')
            ->with(
                'status',
                'Asset damage report submitted successfully.'
            );
    }

    /**
     * Show edit form.
     */
    public function edit(AssetDamageReport $assetDamageReport)
    {
        $assets = Asset::orderBy('name')->get();

        $rooms = Room::orderBy('room_number')->get();

        return view(
            'asset-damage-reports.edit',
            compact(
                'assetDamageReport',
                'assets',
                'rooms'
            )
        );
    }

    /**
     * Update damage report.
     */
    public function update(
        Request $request,
        AssetDamageReport $assetDamageReport
    ) {
        $validated = $request->validate([
            'asset_id' => [
                'required',
                'exists:assets,id',
            ],

            'room_id' => [
                'nullable',
                'exists:rooms,id',
            ],

            'description' => [
                'required',
                'string',
            ],
        ]);

        $assetDamageReport->update($validated);

        return redirect()
            ->route('asset-damage-reports.index')
            ->with(
                'status',
                'Asset damage report updated successfully.'
            );
    }

    /**
     * Update damage report status and repair cost.
     */
    public function updateStatus(
        Request $request,
        AssetDamageReport $assetDamageReport
    ) {
        $validated = $request->validate([
            'status' => [
                'required',
                'in:reported,under_repair,repaired,written_off',
            ],

            'repair_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);

        $data = [
            'status' => $validated['status'],
            'repair_cost' => $validated['repair_cost'] ?? null,
        ];

        if (
            in_array(
                $validated['status'],
                ['repaired', 'written_off'],
                true
            )
        ) {
            $data['resolved_at'] = now();
        } else {
            $data['resolved_at'] = null;
        }

        $assetDamageReport->update($data);

        return back()->with(
            'status',
            'Damage report status updated successfully.'
        );
    }

    /**
     * Delete damage report.
     */
    public function destroy(
        AssetDamageReport $assetDamageReport
    ) {
        $assetDamageReport->delete();

        return redirect()
            ->route('asset-damage-reports.index')
            ->with(
                'status',
                'Asset damage report deleted successfully.'
            );
    }
}