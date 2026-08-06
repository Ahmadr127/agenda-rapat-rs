<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add unit_id column (nullable initially for data migration)
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('full_name')->constrained('units');
        });

        // Step 2: Populate unit_id from matching units.name = employees.organization
        DB::statement('
            UPDATE employees
            SET unit_id = units.id
            FROM units
            WHERE employees.organization = units.name
        ');

        // Step 3: Make unit_id non-nullable and drop organization
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable(false)->change();
            $table->dropColumn('organization');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('organization')->after('full_name')->default('');
        });

        DB::statement('
            UPDATE employees
            SET organization = units.name
            FROM units
            WHERE employees.unit_id = units.id
        ');

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};
