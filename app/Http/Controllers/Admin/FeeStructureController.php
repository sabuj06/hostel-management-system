<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeStructure;
use App\Models\Hostel;
use Illuminate\Http\Request;
use App\ActivityLogger;

class FeeStructureController extends Controller
{
    public function index()
    {
        $feeStructures = FeeStructure::with('hostel')
            ->latest()
            ->paginate(10);

        return view(
            'fee-structures.index',
            compact('feeStructures')
        );
    }

    public function create()
    {
        $hostels = Hostel::active()->get();

        return view(
            'fee-structures.create',
            compact('hostels')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create Fee Structure
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $feeStructure = FeeStructure::create($data);

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        ActivityLogger::log(
            action: 'created',
            module: 'fee_structures',
            description: "Fee structure created: {$feeStructure->name}",
            subject: $feeStructure,
            newValues: $feeStructure->fresh()->toArray()
        );

        return redirect()
            ->route('fee-structures.index')
            ->with(
                'status',
                'Fee structure created successfully.'
            );
    }

    public function edit(FeeStructure $feeStructure)
    {
        $hostels = Hostel::active()->get();

        return view(
            'fee-structures.edit',
            compact('feeStructure', 'hostels')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Fee Structure
    |--------------------------------------------------------------------------
    */
    public function update(
        Request $request,
        FeeStructure $feeStructure
    ) {
        // Save old values before update
        $oldValues = $feeStructure->toArray();

        $feeStructure->update(
            $this->validated($request)
        );

        $feeStructure->refresh();

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        ActivityLogger::log(
            action: 'updated',
            module: 'fee_structures',
            description: "Fee structure updated: {$feeStructure->name}",
            subject: $feeStructure,
            oldValues: $oldValues,
            newValues: $feeStructure->toArray()
        );

        return redirect()
            ->route('fee-structures.index')
            ->with(
                'status',
                'Fee structure updated successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Fee Structure
    |--------------------------------------------------------------------------
    */
    public function destroy(FeeStructure $feeStructure)
    {
        // Save old values before deletion
        $oldValues = $feeStructure->toArray();

        $feeStructureName = $feeStructure->name;

        $feeStructure->delete();

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        ActivityLogger::log(
            action: 'deleted',
            module: 'fee_structures',
            description: "Fee structure deleted: {$feeStructureName}",
            subject: $feeStructure,
            oldValues: $oldValues
        );

        return back()->with(
            'status',
            'Fee structure deleted successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'hostel_id' => [
                'nullable',
                'exists:hostels,id'
            ],

            'room_type' => [
                'required',
                'in:single,double,triple,dormitory,any'
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0'
            ],

            'frequency' => [
                'required',
                'in:monthly,yearly,one_time'
            ],

            'description' => [
                'nullable',
                'string'
            ],

            'status' => [
                'required',
                'in:active,inactive'
            ],
        ]);
    }
}