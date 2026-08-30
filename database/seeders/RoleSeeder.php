<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Student;
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

        // Student — login account
        $studentUser = User::firstOrCreate(
            ['email' => 'rahul@student.test'],
            [
                'name' => 'Rahul',
                'password' => Hash::make('Student@123'),
                'role_id' => $studentRole->id,
                'status' => 'active',
            ]
        );

        // FIX: A student User account alone isn't enough — the portal
        // (StudentPortalController::currentStudent) looks up a Student
        // record whose user_id matches the logged-in user. Without this,
        // every portal page throws "not linked to a student profile" (403).
        // firstOrCreate on student_uid keeps this seeder safe to re-run.
        Student::firstOrCreate(
            ['student_uid' => 'STU-DEMO-0001'],
            [
                'user_id' => $studentUser->id,
                'name' => $studentUser->name,
                'email' => $studentUser->email,
                'phone' => '9999999999',
                'gender' => 'male',
                'status' => 'active',
                'admission_date' => now(),
            ]
        );
    }
}