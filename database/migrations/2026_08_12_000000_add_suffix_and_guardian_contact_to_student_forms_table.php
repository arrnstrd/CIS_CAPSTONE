<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('suffix', 20)->nullable()->after('last_name');
        });

        Schema::table('guardians', function (Blueprint $table) {
            $table->string('contact_number', 20)->nullable()->after('relationship');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('suffix');
        });

        Schema::table('guardians', function (Blueprint $table) {
            $table->dropColumn('contact_number');
        });
    }
};
