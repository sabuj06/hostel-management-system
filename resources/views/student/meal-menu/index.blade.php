@extends('layouts.student')

@section('title', 'Meal Menu')
@section('page-title', 'This Week\'s Meal Menu')

@section('content')
<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr><th style="width:120px;">Day</th><th>Breakfast</th><th>Lunch</th><th>Dinner</th></tr>
            </thead>
            <tbody>
                @foreach(\App\Models\MealMenu::DAYS as $day)
                <tr class="{{ $day === strtolower(now()->format('l')) ? 'table-warning' : '' }}">
                    <td class="fw-semibold text-capitalize">{{ $day }}</td>
                    @foreach(\App\Models\MealMenu::MEAL_TYPES as $mealType)
                    <td>{{ $menus[$day][$mealType]->items ?? '—' }}</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <p class="text-muted small mb-0">Won't be eating on some days? Submit a mess cut so those days aren't billed.</p>
    <a href="{{ route('student-portal.mess-cuts.create') }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-calendar-x"></i> Request Mess Cut
    </a>
</div>
@endsection