<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('grading_configs', function (Blueprint $table) {
            $table->id(); // i

            // Foreign keys
            $table->foreignId('teaching_assignment_id')->constrained('teaching_assignments')->cascadeOnDelete();
            $table->foreignId('assessment_category_id')->constrained('assessment_categories')->cascadeOnDelete();

            $table->decimal('weight', 5, 2);
            $table->timestamps();

            // Prevent duplicate category configs for the same teaching assignment
            $table->unique(['teaching_assignment_id', 'assessment_category_id'], 'grading_configs_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grading_configs');
    }
};
