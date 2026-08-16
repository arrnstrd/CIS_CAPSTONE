<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->smallInteger('age')->nullable()->after('birthdate');
            $table->string('birthplace')->nullable()->after('age');
            $table->string('mother_tongue')->nullable()->after('birthplace');
            $table->string('ip_ethnic_group')->nullable()->after('mother_tongue');
            $table->string('religion')->nullable()->after('ip_ethnic_group');

            $table->date('birthdate')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['age', 'birthplace', 'mother_tongue', 'ip_ethnic_group', 'religion']);
            $table->date('birthdate')->nullable(false)->change();
        });
    }
};