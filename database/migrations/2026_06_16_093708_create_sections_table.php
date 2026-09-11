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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('level' , ['elementary' , 'highschool' , 'senior_high_school']);
            $table->unsignedInteger('grade_level');
            $table->foreignId('advisor_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->timestamps();

            $table->unique([
                'name',
                'level', 
                'grade_level'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
