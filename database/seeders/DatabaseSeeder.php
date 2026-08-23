<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        User::factory()->create([
            'first_name' => 'CIS',
            'last_name' => 'Admin',
            'role' => 'super_admin',
            'status' => 'active',
            'email' => 'superadmin@cis.edu.ph',
        ]);

        $this->call(TeacherSeeder::class);
    }
}
