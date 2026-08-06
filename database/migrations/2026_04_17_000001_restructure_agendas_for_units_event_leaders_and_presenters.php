<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_presenters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_id')->constrained('agendas')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['agenda_id', 'employee_id']);
            $table->index(['agenda_id', 'sort_order']);
        });

        Schema::table('agendas', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('description')->constrained('units');
        });

        DB::statement('
            UPDATE agendas
            SET unit_id = employees.unit_id
            FROM employees
            WHERE agendas.organizer_id = employees.id
        ');

        Schema::table('agendas', function (Blueprint $table) {
            $table->dropForeign(['organizer_id']);
            $table->dropForeign(['meeting_chair_id']);
        });

        Schema::table('agendas', function (Blueprint $table) {
            $table->renameColumn('meeting_chair_id', 'event_leader_id');
        });

        Schema::table('agendas', function (Blueprint $table) {
            $table->foreign('event_leader_id')->references('id')->on('employees');
            $table->foreignId('unit_id')->nullable(false)->change();
            $table->dropColumn(['status', 'organizer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_presenters');

        Schema::table('agendas', function (Blueprint $table) {
            $table->enum('status', ['draft', 'active', 'completed'])->default('draft')->after('event_time');
            $table->foreignId('organizer_id')->nullable()->after('status')->constrained('employees');
            $table->dropForeign(['event_leader_id']);
        });

        DB::statement('
            UPDATE agendas
            SET organizer_id = employee_units.employee_id
            FROM (
                SELECT MIN(id) AS employee_id, unit_id
                FROM employees
                GROUP BY unit_id
            ) AS employee_units
            WHERE agendas.unit_id = employee_units.unit_id
        ');

        Schema::table('agendas', function (Blueprint $table) {
            $table->renameColumn('event_leader_id', 'meeting_chair_id');
        });

        Schema::table('agendas', function (Blueprint $table) {
            $table->foreign('meeting_chair_id')->references('id')->on('employees');
        });

        DB::statement('
            UPDATE agendas
            SET organizer_id = COALESCE(organizer_id, meeting_chair_id)
        ');

        Schema::table('agendas', function (Blueprint $table) {
            $table->foreignId('organizer_id')->nullable(false)->change();
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};
