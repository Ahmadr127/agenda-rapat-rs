<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('meeting_chair_id')->constrained('units');
        });

        DB::statement('
            UPDATE agendas
            SET unit_id = employees.unit_id
            FROM employees
            WHERE agendas.organizer_id = employees.id
        ');
    }
};
