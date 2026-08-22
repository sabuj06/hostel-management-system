<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users', 'permissions')->get();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name', 'alpha_dash'],
            'label' => ['required', 'string', 'max:255'],
        ]);

        Role::create($data);

        return redirect()->route('roles.index')->with('status', 'Role created successfully.');
    }

    // Shows the permission matrix (modules x actions) for a role
    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');
        $assignedIds = $role->permissions()->pluck('permissions.id')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'assignedIds'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
        ]);

        $role->update($data);

        return back()->with('status', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->exists()) {
            return back()->with('status', 'Cannot delete a role that has users assigned to it.');
        }

        if (in_array($role->name, ['admin', 'warden', 'staff', 'student'], true)) {
            return back()->with('status', 'System roles cannot be deleted.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('status', 'Role deleted successfully.');
    }

    // AJAX: toggle a single permission checkbox for a role — instant save, no page reload
    public function togglePermission(Request $request, Role $role)
    {
        $data = $request->validate([
            'permission_id' => ['required', 'exists:permissions,id'],
            'checked' => ['required', 'boolean'],
        ]);

        if ($data['checked']) {
            $role->permissions()->syncWithoutDetaching([$data['permission_id']]);
        } else {
            $role->permissions()->detach($data['permission_id']);
        }

        return response()->json(['success' => true]);
    }
}