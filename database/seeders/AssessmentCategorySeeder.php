<?php

namespace Database\Seeders;

use App\Models\AssessmentCategory;
use Illuminate\Database\Seeder;

class AssessmentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['Written Work', 'Performance Task', 'Term Assessment'];

        foreach ($categories as $name) {
            AssessmentCategory::firstOrCreate(['name' => $name]);
        }
    }
}
