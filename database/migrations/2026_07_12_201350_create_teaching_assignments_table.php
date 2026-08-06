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
        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('school_year_id')->constrained('school_years')->cascadeOnUpdate()->restrictOnDelete();
            $table->enum('session_type', ['morning', 'afternoon', 'whole_day']);
            $table->time('in_start');
            $table->time('late_threshold');
            $table->time('out_end');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(
                ['teacher_id', 'subject_id', 'section_id', 'school_year_id'],
                'teaching_assignments_unique'
            );
            $table->index(['teacher_id', 'school_year_id']);
            $table->index(['section_id', 'school_year_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teaching_assignments');
    }
};
