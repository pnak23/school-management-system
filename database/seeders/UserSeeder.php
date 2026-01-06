<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user with your specified credentials
        $adminUser = User::firstOrCreate(
            ['email' => 'povmuny@school.com'],
            [
                'name' => 'Admin User',
                'email' => 'povmuny@school.com',
                'password' => Hash::make('123'),
                'status' => 'active',
                'is_active' => 1,
                'email_verified_at' => now(),
            ]
        );

        // Assign admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && !$adminUser->hasRole('admin')) {
            $adminUser->assignRole($adminRole);
            $this->command->info('✓ Admin role assigned to ' . $adminUser->email);
        }

        // Create sample teacher
        $teacher = User::firstOrCreate(
            ['email' => 'teacher@school.com'],
            [
                'name' => 'John Teacher',
                'email' => 'teacher@school.com',
                'password' => Hash::make('123'),
                'status' => 'active',
                'is_active' => 1,
                'email_verified_at' => now(),
            ]
        );

        $teacherRole = Role::where('name', 'teacher')->first();
        if ($teacherRole && !$teacher->hasRole('teacher')) {
            $teacher->assignRole($teacherRole);
            $this->command->info('✓ Teacher role assigned to ' . $teacher->email);
        }

        // Create sample student
        $student = User::firstOrCreate(
            ['email' => 'student@school.com'],
            [
                'name' => 'Jane Student',
                'email' => 'student@school.com',
                'password' => Hash::make('123'),
                'status' => 'active',
                'is_active' => 1,
                'email_verified_at' => now(),
            ]
        );

        $studentRole = Role::where('name', 'student')->first();
        if ($studentRole && !$student->hasRole('student')) {
            $student->assignRole($studentRole);
            $this->command->info('✓ Student role assigned to ' . $student->email);
        }

        // Create sample principal
        $principal = User::firstOrCreate(
            ['email' => 'principal@school.com'],
            [
                'name' => 'Principal Smith',
                'email' => 'principal@school.com',
                'password' => Hash::make('123'),
                'status' => 'active',
                'is_active' => 1,
                'email_verified_at' => now(),
            ]
        );

        $principalRole = Role::where('name', 'principal')->first();
        if ($principalRole && !$principal->hasRole('principal')) {
            $principal->assignRole($principalRole);
            $this->command->info('✓ Principal role assigned to ' . $principal->email);
        }

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('  Users seeded successfully!');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('📧 Login Credentials (All passwords: 123)');
        $this->command->info('───────────────────────────────────────────────────────────');
        $this->command->info('  Admin:     povmuny@school.com     | Password: 123');
        $this->command->info('  Teacher:   teacher@school.com     | Password: 123');
        $this->command->info('  Student:   student@school.com     | Password: 123');
        $this->command->info('  Principal: principal@school.com   | Password: 123');
        $this->command->info('───────────────────────────────────────────────────────────');
        $this->command->info('');
        $this->command->info('🌐 Login URL: http://localhost:8000/login');
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════');
    }
}
