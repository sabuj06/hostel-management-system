<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use App\ActivityLogger;

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
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:roles,name',
                'alpha_dash'
            ],
            'label' => [
                'required',
                'string',
                'max:255'
            ],
        ]);

        $role = Role::create($data);

        ActivityLogger::log(
            action: 'created',
            module: 'roles',
            description: "Role '{$role->name}' created",
            subject: $role,
            newValues: $role->toArray()
        );

        return redirect()
            ->route('roles.index')
            ->with('status', 'Role created successfully.');
    }

    // Shows the permission matrix (modules x actions) for a role
    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        $assignedIds = $role->permissions()
            ->pluck('permissions.id')
            ->toArray();

        return view(
            'roles.edit',
            compact('role', 'permissions', 'assignedIds')
        );
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
        ]);

        $oldValues = $role->toArray();

        $role->update($data);
        $role->refresh();

        ActivityLogger::log(
            action: 'updated',
            module: 'roles',
            description: "Role '{$role->name}' updated",
            subject: $role,
            oldValues: $oldValues,
            newValues: $role->toArray()
        );

        return back()->with('status', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->exists()) {
            return back()->with(
                'status',
                'Cannot delete a role that has users assigned to it.'
            );
        }

        if (in_array(
            $role->name,
            ['admin', 'warden', 'staff', 'student'],
            true
        )) {
            return back()->with(
                'status',
                'System roles cannot be deleted.'
            );
        }

        $oldValues = $role->toArray();
        $roleName = $role->name;

        $role->delete();

        ActivityLogger::log(
            action: 'deleted',
            module: 'roles',
            description: "Role '{$roleName}' deleted",
            subject: $role,
            oldValues: $oldValues
        );

        return redirect()
            ->route('roles.index')
            ->with('status', 'Role deleted successfully.');
    }

    // AJAX: toggle a single permission checkbox for a role — instant save, no page reload
    public function togglePermission(Request $request, Role $role)
    {
        $data = $request->validate([
            'permission_id' => ['required', 'exists:permissions,id'],
            'checked' => ['required', 'boolean'],
        ]);

        $permission = Permission::findOrFail($data['permission_id']);

        if ($data['checked']) {
            $role->permissions()
                ->syncWithoutDetaching([$data['permission_id']]);

            ActivityLogger::log(
                action: 'permission_assigned',
                module: 'roles',
                description: "Permission '{$permission->name}' assigned to role '{$role->name}'",
                subject: $role,
                newValues: [
                    'permission_id' => $permission->id,
                    'permission_name' => $permission->name,
                ]
            );
        } else {
            $role->permissions()
                ->detach($data['permission_id']);

            ActivityLogger::log(
                action: 'permission_removed',
                module: 'roles',
                description: "Permission '{$permission->name}' removed from role '{$role->name}'",
                subject: $role,
                oldValues: [
                    'permission_id' => $permission->id,
                    'permission_name' => $permission->name,
                ]
            );
        }

        return response()->json([
            'success' => true
        ]);
    }
}