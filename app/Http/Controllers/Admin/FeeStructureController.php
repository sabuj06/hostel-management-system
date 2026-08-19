<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeStructure;
use App\Models\Hostel;
use Illuminate\Http\Request;

class FeeStructureController extends Controller
{
    public function index()
    {
        $feeStructures = FeeStructure::with('hostel')->latest()->paginate(10);

        return view('fee-structures.index', compact('feeStructures'));
    }

    public function create()
    {
        $hostels = Hostel::active()->get();

        return view('fee-structures.create', compact('hostels'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        FeeStructure::create($data);

        return redirect()->route('fee-structures.index')->with('status', 'Fee structure created successfully.');
    }

    public function edit(FeeStructure $feeStructure)
    {
        $hostels = Hostel::active()->get();

        return view('fee-structures.edit', compact('feeStructure', 'hostels'));
    }

    public function update(Request $request, FeeStructure $feeStructure)
    {
        $feeStructure->update($this->validated($request));

        return redirect()->route('fee-structures.index')->with('status', 'Fee structure updated successfully.');
    }

    public function destroy(FeeStructure $feeStructure)
    {
        $feeStructure->delete();

        return back()->with('status', 'Fee structure deleted successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'hostel_id' => ['nullable', 'exists:hostels,id'],
            'room_type' => ['required', 'in:single,double,triple,dormitory,any'],
            'amount' => ['required', 'numeric', 'min:0'],
            'frequency' => ['required', 'in:monthly,yearly,one_time'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }
}