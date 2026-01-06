<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'System Administrator with full access to all features and settings',
                'is_active' => 1,
            ],
            [
                'name' => 'teacher',
                'description' => 'Teacher with access to manage classes, students, and grades',
                'is_active' => 1,
            ],
            [
                'name' => 'student',
                'description' => 'Student with access to view courses, assignments, and grades',
                'is_active' => 1,
            ],
            [
                'name' => 'staff',
                'description' => 'School staff with limited administrative access',
                'is_active' => 1,
            ],
            [
                'name' => 'parent',
                'description' => 'Parent/Guardian with access to view their children\'s information',
                'is_active' => 1,
            ],
            [
                'name' => 'principal',
                'description' => 'School Principal with elevated administrative privileges',
                'is_active' => 1,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name']], // Check by name
                $roleData // Create with all data if not exists
            );
        }

        $this->command->info('Roles seeded successfully!');
        $this->command->info('Created roles: admin, teacher, student, staff, parent, principal');
    }
}
