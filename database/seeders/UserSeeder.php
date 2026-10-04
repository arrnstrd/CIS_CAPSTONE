<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed default demo accounts matching the Quick Demo Access dropdown in login.blade.php.
     */
    public function run(): void
    {
        $users = [
            [
                'email' => 'superadmin@cis.edu.ph',
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'role' => User::ROLE_SUPER_ADMIN,
                'status' => 'active',
                'password' => Hash::make('ProtectedAdminAccount2026#'),
            ],
            [
                'email' => 'schooladmin@cis.edu.ph',
                'first_name' => 'School',
                'last_name' => 'Admin',
                'role' => User::ROLE_ADMIN,
                'status' => 'active',
                'password' => Hash::make('SchoolAdminPassword2026#'),
            ],
            [
                'email' => 'teacher@cis.edu.ph',
                'first_name' => 'Arriane',
                'last_name' => 'Estrada',
                'role' => User::ROLE_TEACHER,
                'status' => 'active',
                'password' => Hash::make('TeacherPassword2026#'),
            ],
            [
                'email' => 'arriane.estrada.dev',
                'first_name' => 'Arriane',
                'last_name' => 'Estrada',
                'role' => User::ROLE_TEACHER,
                'status' => 'active',
                'password' => Hash::make('TeacherPassword2026#'),
            ],
            [
                'email' => 'scanner@cis.edu.ph',
                'first_name' => 'Scanner',
                'last_name' => 'Operator',
                'role' => User::ROLE_SCANNER_OPERATOR,
                'status' => 'active',
                'password' => Hash::make('ScannerPassword2026#'),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
