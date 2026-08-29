@extends('layouts.app')

@section('title', 'Damage Reports')

@section('page-title', 'Asset Damage Reports')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">

    <h4 class="mb-0">
        <i class="bi bi-tools me-2"></i>
        Damage / Repair Reports
    </h4>

    <div class="d-flex gap-2">

        <a href="{{ route('assets.index') }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-box-seam"></i>
            Assets
        </a>

        <a href="{{ route('asset-damage-reports.create') }}"
           class="btn btn-primary">
            <i class="bi bi-plus-lg"></i>
            Report Damage
        </a>

    </div>

</div>

@if(session('status'))

    <div class="alert alert-success">
        {{ session('status') }}
    </div>

@endif


{{-- Status Filter --}}
<form method="GET"
      class="d-flex gap-2 mb-3"
      style="max-width:250px;">

    <select name="status"
            class="form-select"
            onchange="this.form.submit()">

        <option value="">All Statuses</option>

        <option value="reported"
            {{ request('status') === 'reported' ? 'selected' : '' }}>
            Reported
        </option>

        <option value="under_repair"
            {{ request('status') === 'under_repair' ? 'selected' : '' }}>
            Under Repair
        </option>

        <option value="repaired"
            {{ request('status') === 'repaired' ? 'selected' : '' }}>
            Repaired
        </option>

        <option value="written_off"
            {{ request('status') === 'written_off' ? 'selected' : '' }}>
            Written Off
        </option>

    </select>

</form>


<div class="card shadow-sm border-0">

    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">

                <tr>
                    <th>Asset</th>
                    <th>Room</th>
                    <th>Description</th>
                    <th>Reported By</th>
                    <th>Status</th>
                    <th>Repair Cost</th>
                    <th class="text-end">Update</th>
                </tr>

            </thead>

            <tbody>

                @forelse($reports as $report)

                <tr>

                    <td>
                        {{ $report->asset->name ?? '-' }}
                    </td>

                    <td>
                        {{ $report->room->room_number ?? '-' }}
                    </td>

                    <td>
                        {{ \Illuminate\Support\Str::limit($report->description, 60) }}
                    </td>

                    <td>
                        {{ $report->reportedBy->name ?? '-' }}
                    </td>

                    <td>

                        @php

                            $badge = [
                                'reported' => 'secondary',
                                'under_repair' => 'warning',
                                'repaired' => 'success',
                                'written_off' => 'danger',
                            ][$report->status] ?? 'secondary';

                        @endphp

                        <span class="badge bg-{{ $badge }} text-capitalize">
                            {{ str_replace('_', ' ', $report->status) }}
                        </span>

                    </td>

                    <td>
                        {{ $report->repair_cost
                            ? '₹' . number_format($report->repair_cost, 2)
                            : '-' }}
                    </td>

                    <td class="text-end">

                        {{-- IMPORTANT: PATCH FORM --}}
                       <form method="POST"
      action="{{ route('asset-damage-reports.status', ['assetDamageReport' => $report->id]) }}"
      class="d-flex gap-1 justify-content-end">

    @csrf
    @method('PATCH')

    <select name="status"
            class="form-select form-select-sm"
            style="width:130px;">

        <option value="reported"
            {{ $report->status === 'reported' ? 'selected' : '' }}>
            Reported
        </option>

        <option value="under_repair"
            {{ $report->status === 'under_repair' ? 'selected' : '' }}>
            Under Repair
        </option>

        <option value="repaired"
            {{ $report->status === 'repaired' ? 'selected' : '' }}>
            Repaired
        </option>

        <option value="written_off"
            {{ $report->status === 'written_off' ? 'selected' : '' }}>
            Written Off
        </option>

    </select>

    <input type="number"
           step="0.01"
           name="repair_cost"
           class="form-control form-control-sm"
           style="width:90px;"
           placeholder="Cost"
           value="{{ $report->repair_cost }}">

    <button type="submit"
            class="btn btn-sm btn-outline-primary">
        Save
    </button>

</form>

                    </td>

                </tr>

                @empty

                <tr>
                    <td colspan="7"
                        class="text-center text-muted py-4">
                        No damage reports yet.
                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    <div class="card-footer bg-white">
        {{ $reports->links() }}
    </div>

</div>

@endsection