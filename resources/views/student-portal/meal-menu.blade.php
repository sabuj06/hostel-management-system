@extends('layouts.student')

@section('title', 'Meal Menu')
@section('page-title', 'Weekly Meal Menu')

@section('content')

<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    Here is this week's hostel meal menu.
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="bi bi-egg-fried me-2"></i>
            Weekly Meal Menu
        </h5>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 120px;">Day</th>
                    <th>Breakfast</th>
                    <th>Lunch</th>
                    <th>Dinner</th>
                </tr>
            </thead>

            <tbody>
                @foreach(\App\Models\MealMenu::DAYS as $day)
                    <tr>
                        <td class="fw-semibold text-capitalize">
                            {{ $day }}
                        </td>

                        @foreach(\App\Models\MealMenu::MEAL_TYPES as $mealType)
                            @php
                                $existing = $menus[$day][$mealType] ?? null;
                            @endphp

                            <td>
                                {{ $existing->items ?? 'Not available' }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection