<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\MealMenu;
use Illuminate\Http\Request;

class MealMenuController extends Controller
{
    public function index(Request $request)
    {
        $hostels = Hostel::active()->get();
        $hostelId = $request->hostel_id ?: null;

        $menus = MealMenu::where('hostel_id', $hostelId)->get()
            ->groupBy('day_of_week')
            ->map(fn ($dayMenus) => $dayMenus->keyBy('meal_type'));

        return view('meal-menu.index', compact('hostels', 'menus', 'hostelId'));
    }

    // AJAX: save/update a single grid cell (day + meal_type) inline
    public function saveCell(Request $request)
    {
        $data = $request->validate([
            'hostel_id' => ['nullable', 'exists:hostels,id'],
            'day_of_week' => ['required', 'in:' . implode(',', MealMenu::DAYS)],
            'meal_type' => ['required', 'in:' . implode(',', MealMenu::MEAL_TYPES)],
            'items' => ['required', 'string', 'max:500'],
        ]);

        $menu = MealMenu::updateOrCreate(
            [
                'hostel_id' => $data['hostel_id'] ?? null,
                'day_of_week' => $data['day_of_week'],
                'meal_type' => $data['meal_type'],
            ],
            ['items' => $data['items']]
        );

        return response()->json(['success' => true, 'items' => $menu->items]);
    }
}