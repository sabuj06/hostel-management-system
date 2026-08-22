@extends('layouts.app')

@section('title', 'Meal Menu')
@section('page-title', 'Weekly Meal Menu')

@section('content')
<form method="GET" class="d-flex gap-2 mb-3">
    <select name="hostel_id" class="form-select" style="max-width:300px;" onchange="this.form.submit()">
        <option value="">All Hostels (default menu)</option>
        @foreach($hostels as $h)
            <option value="{{ $h->id }}" @selected($hostelId == $h->id)>{{ $h->name }}</option>
        @endforeach
    </select>
</form>

<div class="alert alert-info small">
    <i class="bi bi-info-circle"></i> Click any cell to edit the menu for that day/meal. It saves automatically — no save button needed.
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0" id="menu-grid">
            <thead class="table-light">
                <tr>
                    <th style="width:120px;">Day</th>
                    <th>Breakfast</th>
                    <th>Lunch</th>
                    <th>Dinner</th>
                </tr>
            </thead>
            <tbody>
                @foreach(\App\Models\MealMenu::DAYS as $day)
                <tr>
                    <td class="fw-semibold text-capitalize">{{ $day }}</td>
                    @foreach(\App\Models\MealMenu::MEAL_TYPES as $mealType)
                    @php $existing = $menus[$day][$mealType] ?? null; @endphp
                    <td>
                        <div class="menu-cell" data-day="{{ $day }}" data-meal="{{ $mealType }}" style="min-height:32px; cursor:pointer;">
                            <span class="cell-text">{{ $existing->items ?? '— click to add —' }}</span>
                        </div>
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        const hostelId = {{ $hostelId ? $hostelId : 'null' }};

        // Click a cell to turn it into an editable textarea
        $(document).on('click', '.menu-cell', function () {
            const $cell = $(this);
            if ($cell.find('textarea').length) return; // already editing

            const currentText = $cell.find('.cell-text').text().trim();
            const placeholder = currentText === '— click to add —' ? '' : currentText;

            $cell.html(`
                <textarea class="form-control form-control-sm" rows="2">${placeholder}</textarea>
                <div class="mt-1">
                    <button class="btn btn-sm btn-primary save-cell-btn">Save</button>
                    <button class="btn btn-sm btn-light cancel-cell-btn">Cancel</button>
                </div>
            `);
            $cell.find('textarea').focus();
        });

        // Save via AJAX
        $(document).on('click', '.save-cell-btn', function () {
            const $cell = $(this).closest('.menu-cell');
            const items = $cell.find('textarea').val().trim();
            const day = $cell.data('day');
            const meal = $cell.data('meal');

            if (!items) {
                alert('Menu items cannot be empty.');
                return;
            }

            $.ajax({
                url: '{{ route('meal-menu.save-cell') }}',
                method: 'POST',
                data: { hostel_id: hostelId, day_of_week: day, meal_type: meal, items: items },
                success: function (res) {
                    $cell.html(`<span class="cell-text">${res.items}</span>`);
                },
                error: function () {
                    alert('Failed to save menu item.');
                }
            });
        });

        // Cancel edit — revert to plain text view without saving
        $(document).on('click', '.cancel-cell-btn', function () {
            const $cell = $(this).closest('.menu-cell');
            $cell.click(); // trigger a fresh render via location reload fallback
            location.reload();
        });
    });
</script>
@endpush