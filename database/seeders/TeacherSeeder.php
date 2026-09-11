<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'arriane.estrada.dev'],
            [
                'first_name' => 'Arriane',
                'last_name' => 'Estrada',
                'email' => 'arriane.estrada.dev',
                'password' => Hash::make('TeacherPassword2026#'),
                'role' => User::ROLE_TEACHER,
                'status' => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'teacher@cis.edu.ph'],
            [
                'first_name' => 'Arriane',
                'last_name' => 'Estrada',
                'email' => 'teacher@cis.edu.ph',
                'password' => Hash::make('TeacherPassword2026#'),
                'role' => User::ROLE_TEACHER,
                'status' => 'active',
            ]
        );
    }
}
