<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Phase 1: only auth/user stats exist. More cards (rooms, fees,
        // complaints, visitors...) will be wired in as those modules land
        // in later phases.
        $stats = [
            'total_users'   => User::count(),
            'active_users'  => User::where('status', 'active')->count(),
            'role'          => $user->role?->label ?? 'No role assigned',
        ];

        return view('dashboard', compact('stats'));
    }
}