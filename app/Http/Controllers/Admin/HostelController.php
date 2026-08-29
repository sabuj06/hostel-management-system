<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use Illuminate\Http\Request;
use App\ActivityLogger;
use App\Services\ActivityLogService;

class HostelController extends Controller
{
    public function index(Request $request)
    {
        $hostels = Hostel::withCount('blocks')
            ->when(
                $request->search,
                fn ($q) => $q->where(
                    'name',
                    'like',
                    "%{$request->search}%"
                )
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'hostels.index',
            compact('hostels')
        );
    }

    public function create()
    {
        return view('hostels.create');
    }

    /*
    |--------------------------------------------------------------------------
    | Create Hostel
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'type' => [
                'required',
                'in:boys,girls,mixed'
            ],
            'address' => [
                'nullable',
                'string',
                'max:500'
            ],
            'warden_name' => [
                'nullable',
                'string',
                'max:255'
            ],
            'contact_number' => [
                'nullable',
                'string',
                'max:20'
            ],
            'status' => [
                'required',
                'in:active,inactive'
            ],
        ]);

        $hostel = Hostel::create($data);

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        ActivityLogger::log(
            action: 'created',
            module: 'hostels',
            description: "Hostel created: {$hostel->name}",
            subject: $hostel,
            newValues: $hostel->fresh()->toArray()
        );

        return redirect()
            ->route('hostels.index')
            ->with(
                'status',
                'Hostel created successfully.'
            );
    }

    public function edit(Hostel $hostel)
    {
        return view(
            'hostels.edit',
            compact('hostel')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Hostel
    |--------------------------------------------------------------------------
    */
    public function update(
        Request $request,
        Hostel $hostel
    ) {
        // Old values before update
        $oldValues = $hostel->toArray();

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'type' => [
                'required',
                'in:boys,girls,mixed'
            ],
            'address' => [
                'nullable',
                'string',
                'max:500'
            ],
            'warden_name' => [
                'nullable',
                'string',
                'max:255'
            ],
            'contact_number' => [
                'nullable',
                'string',
                'max:20'
            ],
            'status' => [
                'required',
                'in:active,inactive'
            ],
        ]);

        $hostel->update($data);

        $hostel->refresh();

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        ActivityLogger::log(
            action: 'updated',
            module: 'hostels',
            description: "Hostel updated: {$hostel->name}",
            subject: $hostel,
            oldValues: $oldValues,
            newValues: $hostel->toArray()
        );

        return redirect()
            ->route('hostels.index')
            ->with(
                'status',
                'Hostel updated successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Hostel
    |--------------------------------------------------------------------------
    */
    public function destroy(Hostel $hostel)
    {
        // Save old values before deletion
        $oldValues = $hostel->toArray();

        $hostelName = $hostel->name;

        $hostel->delete();

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */
        ActivityLogger::log(
            action: 'deleted',
            module: 'hostels',
            description: "Hostel deleted: {$hostelName}",
            subject: $hostel,
            oldValues: $oldValues
        );

        return back()->with(
            'status',
            'Hostel deleted successfully.'
        );
    }
}