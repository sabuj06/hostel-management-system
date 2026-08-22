<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetDamageReport;
use App\Models\AssetRoomAssignment;
use App\Models\Room;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $assets = Asset::with('category')
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->category_id, fn ($q) => $q->where('asset_category_id', $request->category_id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = AssetCategory::all();

        $lowStockAssets = Asset::where('low_stock_threshold', '>', 0)
            ->whereColumn('quantity_available', '<=', 'low_stock_threshold')
            ->get();

        return view('assets.index', compact('assets', 'categories', 'lowStockAssets'));
    }

    public function create()
    {
        $categories = AssetCategory::all();

        return view('assets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'asset_category_id' => ['required', 'exists:asset_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:assets,sku'],
            'quantity_total' => ['required', 'integer', 'min:1'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'purchase_date' => ['nullable', 'date'],
            'warranty_expiry' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['quantity_available'] = $data['quantity_total'];
        $data['low_stock_threshold'] = $data['low_stock_threshold'] ?? 0;

        Asset::create($data);

        return redirect()->route('assets.index')->with('status', 'Asset added successfully.');
    }

    public function assignForm(Asset $asset)
    {
        $rooms = Room::orderBy('room_number')->get();

        return view('assets.assign', compact('asset', 'rooms'));
    }

    public function assignStore(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:' . $asset->quantity_available],
            'assigned_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        AssetRoomAssignment::create([
            'asset_id' => $asset->id,
            'room_id' => $data['room_id'],
            'quantity' => $data['quantity'],
            'assigned_date' => $data['assigned_date'] ?? now(),
            'notes' => $data['notes'] ?? null,
        ]);

        $asset->decrement('quantity_available', $data['quantity']);

        return redirect()->route('assets.index')->with('status', 'Asset assigned to room successfully.');
    }

    // Mark an asset (or a room-assigned unit) as write-off — removes it from usable stock permanently
    public function writeOff(Asset $asset)
    {
        $asset->update(['status' => 'written_off']);

        return back()->with('status', 'Asset marked as written off.');
    }

    public function damageReports(Request $request)
    {
        $reports = AssetDamageReport::with('asset', 'room', 'reportedBy')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('asset-damage-reports.index', compact('reports'));
    }

    public function createDamageReport()
    {
        $assets = Asset::orderBy('name')->get();
        $rooms = Room::orderBy('room_number')->get();

        return view('asset-damage-reports.create', compact('assets', 'rooms'));
    }

    public function storeDamageReport(Request $request)
    {
        $data = $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'description' => ['required', 'string'],
        ]);

        AssetDamageReport::create([
            ...$data,
            'reported_by' => $request->user()->id,
            'status' => 'reported',
        ]);

        return redirect()->route('asset-damage-reports.index')->with('status', 'Damage reported successfully.');
    }

    public function updateDamageStatus(Request $request, AssetDamageReport $assetDamageReport)
    {
        $data = $request->validate([
            'status' => ['required', 'in:reported,under_repair,repaired,written_off'],
            'repair_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $assetDamageReport->update([
            'status' => $data['status'],
            'repair_cost' => $data['repair_cost'] ?? $assetDamageReport->repair_cost,
            'resolved_at' => in_array($data['status'], ['repaired', 'written_off']) ? now() : null,
        ]);

        if ($data['status'] === 'written_off') {
            $assetDamageReport->asset->update(['status' => 'written_off']);
        }

        return back()->with('status', 'Damage report updated.');
    }
}