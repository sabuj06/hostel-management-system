<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $roles = [
            ['name' => 'admin',   'label' => 'Administrator'],
            ['name' => 'warden',  'label' => 'Warden'],
            ['name' => 'staff',   'label' => 'Staff'],
            ['name' => 'student', 'label' => 'Student'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                $role
            );
        }

        // Get role IDs
        $adminRole = Role::where('name', 'admin')->first();
        $wardenRole = Role::where('name', 'warden')->first();
        $staffRole = Role::where('name', 'staff')->first();
        $studentRole = Role::where('name', 'student')->first();

        // Admin
        User::firstOrCreate(
            ['email' => 'admin@hostel.test'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'status' => 'active',
            ]
        );

        // Warden
        User::firstOrCreate(
            ['email' => 'warden@hostel.test'],
            [
                'name' => 'Hostel Warden',
                'password' => Hash::make('Warden@123'),
                'role_id' => $wardenRole->id,
                'status' => 'active',
            ]
        );

        // Staff
        User::firstOrCreate(
            ['email' => 'staff@hostel.test'],
            [
                'name' => 'Hostel Staff',
                'password' => Hash::make('Staff@123'),
                'role_id' => $staffRole->id,
                'status' => 'active',
            ]
        );

        // Student
        User::firstOrCreate(
            ['email' => 'rahul@student.test'],
            [
                'name' => 'Rahul',
                'password' => Hash::make('Student@123'),
                'role_id' => $studentRole->id,
                'status' => 'active',
            ]
        );
    }
}