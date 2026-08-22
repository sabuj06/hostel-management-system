<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('role')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('name', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%");
                });
            })
            ->when($request->role_id, fn ($q) => $q->where('role_id', $request->role_id))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('users.index')->with('status', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'status' => ['required', 'in:active,inactive'],
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('status', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('status', 'You cannot delete your own account.');
        }

        $user->delete();

        return back()->with('status', 'User deleted successfully.');
    }

    // AJAX: quick active/inactive toggle from the list page
    public function toggleStatus(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'You cannot deactivate your own account.'], 422);
        }

        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);

        return response()->json(['success' => true, 'status' => $user->status]);
    }
}
