<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 public function up(): void
{
    Schema::table('enrollments', function (Blueprint $table) {
        $table->dropUnique('student_id_school_year_unique');

        $table->dropColumn('school_year');
    });
}

public function down(): void
{
    Schema::table('enrollments', function (Blueprint $table) {
        $table->string('school_year')->nullable();

        $table->unique(
            ['student_id', 'school_year'],
            'student_id_school_year_unique'
        );
    });
}
};
