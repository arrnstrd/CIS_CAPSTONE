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
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teaching_assignment_id')
                ->constrained('teaching_assignments')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('assessment_category_id')
                ->constrained('assessment_categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('grading_period_id')
                ->constrained('grading_periods')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('title');
            $table->unsignedInteger('total_items');
            $table->date('assessment_date');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index('assessment_date');
            $table->index(['teaching_assignment_id', 'grading_period_id']);
            $table->index(['assessment_category_id', 'grading_period_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
