<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'hostels' => ['view', 'create', 'edit', 'delete'],
            'students' => ['view', 'create', 'edit', 'delete'],
            'room_allocations' => ['view', 'create', 'transfer', 'checkout'],
            'fees' => ['view', 'create', 'edit', 'delete'],
            'payments' => ['view', 'create', 'delete'],
            'complaints' => ['view', 'create', 'assign', 'resolve'],
            'notices' => ['view', 'create', 'edit', 'delete'],
            'visitors' => ['view', 'create', 'checkout'],
            'reports' => ['view'],
            'users' => ['view', 'create', 'edit', 'delete'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$module}.{$action}",
                ], [
                    'module' => $module,
                ]);
            }
        }

        // Admin gets every permission by default
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->permissions()->sync(Permission::pluck('id'));
        }

        // Warden gets everything except user management
        $warden = Role::where('name', 'warden')->first();
        if ($warden) {
            $ids = Permission::where('module', '!=', 'users')->pluck('id');
            $warden->permissions()->sync($ids);
        }

        // Staff gets limited operational access
        $staff = Role::where('name', 'staff')->first();
        if ($staff) {
            $ids = Permission::whereIn('name', [
                'students.view', 'room_allocations.view', 'complaints.view',
                'complaints.assign', 'complaints.resolve', 'visitors.view',
                'visitors.create', 'visitors.checkout', 'notices.view',
            ])->pluck('id');
            $staff->permissions()->sync($ids);
        }
    }
}