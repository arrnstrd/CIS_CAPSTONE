<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'schooladmin@cis.edu.ph'],
            [
                'first_name' => 'School',
                'last_name' => 'Admin',
                'email' => 'schooladmin@cis.edu.ph',
                'password' => Hash::make('SchoolAdminPassword2026#'),
                'role' => User::ROLE_ADMIN,
                'status' => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'first_name' => 'Test',
                'last_name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('SchoolAdminPassword2026#'),
                'role' => User::ROLE_ADMIN,
                'status' => 'active',
            ]
        );
    }
}
