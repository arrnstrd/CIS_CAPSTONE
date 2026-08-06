<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $user = User::create([
            'first_name' => 'Arriane',
            'last_name' => 'Estrada',
            'email' => 'arriane.estrada.dev',
            'password' => Hash::make('Password123'),
            'role' => User::ROLE_TEACHER,
            'status' => 'active',
        ]);

        Teacher::create([
            'user_id' => $user->id,
            'status' => 'active',
        ]);
    }
}
