<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogService;
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
            ->when(
                $request->role_id,
                fn ($q) => $q->where('role_id', $request->role_id)
            )
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

    public function store(
        Request $request,
        ActivityLogService $activityLogService
    ) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        $activityLogService->log(
            action: 'created',
            module: 'users',
            description: "User '{$user->name}' ({$user->email}) created",
            subject: $user,
            newValues: $user->toArray()
        );

        return redirect()
            ->route('users.index')
            ->with('status', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(
        Request $request,
        User $user,
        ActivityLogService $activityLogService
    ) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'unique:users,email,' . $user->id
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => [
                'nullable',
                'confirmed',
                Password::min(8)
            ],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $oldValues = $user->toArray();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        $user->refresh();

        $activityLogService->log(
            action: 'updated',
            module: 'users',
            description: "User '{$user->name}' ({$user->email}) updated",
            subject: $user,
            oldValues: $oldValues,
            newValues: $user->toArray()
        );

        return redirect()
            ->route('users.index')
            ->with('status', 'User updated successfully.');
    }

    public function destroy(
        Request $request,
        User $user,
        ActivityLogService $activityLogService
    ) {
        if ($user->id === $request->user()->id) {
            return back()->with(
                'status',
                'You cannot delete your own account.'
            );
        }

        $oldValues = $user->toArray();

        $userName = $user->name;
        $userEmail = $user->email;

        $user->delete();

        $activityLogService->log(
            action: 'deleted',
            module: 'users',
            description: "User '{$userName}' ({$userEmail}) deleted",
            subject: $user,
            oldValues: $oldValues
        );

        return back()->with(
            'status',
            'User deleted successfully.'
        );
    }

    public function toggleStatus(
        Request $request,
        User $user,
        ActivityLogService $activityLogService
    ) {
        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot deactivate your own account.'
            ], 422);
        }

        $oldStatus = $user->status;

        $user->update([
            'status' => $user->status === 'active'
                ? 'inactive'
                : 'active'
        ]);

        $user->refresh();

        $activityLogService->log(
            action: 'status_changed',
            module: 'users',
            description:
                "User '{$user->name}' status changed from "
                . "{$oldStatus} to {$user->status}",
            subject: $user,
            oldValues: [
                'status' => $oldStatus,
            ],
            newValues: [
                'status' => $user->status,
            ]
        );

        return response()->json([
            'success' => true,
            'status' => $user->status
        ]);
    }
}