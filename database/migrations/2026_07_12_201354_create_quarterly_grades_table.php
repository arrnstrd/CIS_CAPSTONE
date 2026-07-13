<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('quarterly_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teaching_assignment_id')
                ->constrained('teaching_assignments')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('enrollment_id')->constrained('enrollments')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('grading_period_id')
                ->constrained('grading_periods')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->decimal('written_work_grade', 5, 2)->nullable();
            $table->decimal('performance_task_grade', 5, 2)->nullable();
            $table->decimal('quarterly_assessment_grade', 5, 2)->nullable();
            $table->decimal('initial_grade', 5, 2)->nullable();
            $table->decimal('transmuted_grade', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(
                ['teaching_assignment_id', 'enrollment_id', 'grading_period_id'],
                'quarterly_grades_unique'
            );
            $table->index(['enrollment_id', 'grading_period_id']);
        });
    }

 
    public function down(): void
    {
        Schema::dropIfExists('quarterly_grades');
    }
};
