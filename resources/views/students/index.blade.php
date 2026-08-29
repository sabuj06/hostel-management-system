@extends('layouts.app')

@section('title', 'Students')

@section('page-title', 'Student Management')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">

    <form method="GET" class="d-flex gap-2">

        <input
            type="text"
            name="search"
            class="form-control"
            placeholder="Search name, UID, phone..."
            value="{{ request('search') }}"
        >

        <select
            name="status"
            class="form-select"
            onchange="this.form.submit()"
        >
            <option value="">All Status</option>

            <option
                value="active"
                @selected(request('status') === 'active')
            >
                Active
            </option>

            <option
                value="inactive"
                @selected(request('status') === 'inactive')
            >
                Inactive
            </option>

            <option
                value="left"
                @selected(request('status') === 'left')
            >
                Left
            </option>
        </select>

        <button class="btn btn-outline-secondary">
            <i class="bi bi-search"></i>
        </button>

    </form>


    <a
        href="{{ route('students.create') }}"
        class="btn btn-primary"
    >
        <i class="bi bi-plus-lg"></i>
        Add Student
    </a>

</div>


<div class="card shadow-sm border-0">

    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">

                <tr>

                    <th>UID</th>

                    <th>Name</th>

                    <th>Course/Dept</th>

                    <th>Phone</th>

                    <th>Room/Bed</th>

                    <th>Status</th>

                    <th class="text-end">
                        Actions
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse($students as $student)

                    <tr>

                        <td>
                            {{ $student->student_uid }}
                        </td>


                        <td>
                            {{ $student->name }}
                        </td>


                        <td class="small text-muted">

                            {{ $student->course }}
                            /
                            {{ $student->department }}

                        </td>


                        <td>
                            {{ $student->phone ?? '-' }}
                        </td>


                        <td>

                            @if($student->currentAllocation)

                                <span class="badge bg-info text-dark">

                                    Room
                                    {{ $student->currentAllocation->room->room_number }}

                                </span>

                            @else

                                <span class="badge bg-secondary">
                                    Not Allocated
                                </span>

                            @endif

                        </td>


                        <td>

                            <span
                                class="badge bg-{{
                                    $student->status === 'active'
                                        ? 'success'
                                        : (
                                            $student->status === 'left'
                                                ? 'dark'
                                                : 'danger'
                                        )
                                }}"
                            >

                                {{ ucfirst($student->status) }}

                            </span>

                        </td>


                        <td class="text-end">

                            {{-- View --}}

                            <a
                                href="{{ route('students.show', $student) }}"
                                class="btn btn-sm btn-outline-secondary"
                                title="View Student"
                            >
                                <i class="bi bi-eye"></i>
                            </a>


                            {{-- QR Code --}}

                            <a
                                href="{{ route('students.id-card', $student) }}"
                                class="btn btn-sm btn-outline-dark"
                                title="QR Code"
                            >
                                <i class="bi bi-qr-code"></i>
                            </a>


                            {{-- Edit --}}

                            <a
                                href="{{ route('students.edit', $student) }}"
                                class="btn btn-sm btn-outline-primary"
                                title="Edit Student"
                            >
                                <i class="bi bi-pencil"></i>
                            </a>


                            {{-- Delete --}}

                            <form
                                action="{{ route('students.destroy', $student) }}"
                                method="POST"
                                class="d-inline"
                                onsubmit="return confirm('Delete this student?')"
                            >

                                @csrf

                                @method('DELETE')

                                <button
                                    class="btn btn-sm btn-outline-danger"
                                    title="Delete Student"
                                >
                                    <i class="bi bi-trash"></i>
                                </button>

                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="7"
                            class="text-center text-muted py-4"
                        >
                            No students found.
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    <div class="card-footer bg-white">

        {{ $students->links() }}

    </div>

</div>

@endsection