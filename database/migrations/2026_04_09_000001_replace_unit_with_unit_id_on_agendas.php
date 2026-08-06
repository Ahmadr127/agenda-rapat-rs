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
            $table->foreignId('unit_id')->nullable()->after('organizer')->constrained('units');
        });

        // Populate unit_id from matching units.name = agendas.unit
        DB::statement('
            UPDATE agendas
            SET unit_id = units.id
            FROM units
            WHERE agendas.unit = units.name
        ');

        Schema::table('agendas', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->string('unit')->nullable()->after('organizer');
        });

        DB::statement('
            UPDATE agendas
            SET unit = units.name
            FROM units
            WHERE agendas.unit_id = units.id
        ');

        Schema::table('agendas', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};
