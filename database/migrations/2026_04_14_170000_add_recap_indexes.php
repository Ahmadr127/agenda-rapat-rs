<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agenda_employee', function (Blueprint $table) {
            $table->index(['employee_id', 'agenda_id'], 'agenda_employee_employee_agenda_index');
        });

        Schema::table('agendas', function (Blueprint $table) {
            $table->index('event_date', 'agendas_event_date_index');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->index('unit_id', 'employees_unit_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('agenda_employee', function (Blueprint $table) {
            $table->dropIndex('agenda_employee_employee_agenda_index');
        });

        Schema::table('agendas', function (Blueprint $table) {
            $table->dropIndex('agendas_event_date_index');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('employees_unit_id_index');
        });
    }
};
