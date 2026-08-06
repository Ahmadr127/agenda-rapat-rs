<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add FK columns
        Schema::table('agendas', function (Blueprint $table) {
            $table->foreignId('organizer_id')->nullable()->after('status')->constrained('employees');
            $table->foreignId('meeting_chair_id')->nullable()->after('organizer_id')->constrained('employees');
        });

        // Step 2: Populate from matching employee full_name
        DB::statement('
            UPDATE agendas
            SET organizer_id = e.id
            FROM employees e
            WHERE agendas.organizer = e.full_name
        ');

        DB::statement('
            UPDATE agendas
            SET meeting_chair_id = e.id
            FROM employees e
            WHERE agendas.meeting_chair = e.full_name
        ');

        // Step 2b: Fallback — assign first employee to unmatched rows
        $fallbackId = DB::table('employees')->min('id');
        if ($fallbackId) {
            DB::table('agendas')->whereNull('organizer_id')->update(['organizer_id' => $fallbackId]);
            DB::table('agendas')->whereNull('meeting_chair_id')->update(['meeting_chair_id' => $fallbackId]);
        }

        // Step 3: Make columns not null, drop old string columns, make unit_id not null
        Schema::table('agendas', function (Blueprint $table) {
            $table->foreignId('organizer_id')->nullable(false)->change();
            $table->foreignId('meeting_chair_id')->nullable(false)->change();
            $table->foreignId('unit_id')->nullable(false)->change();
            $table->dropColumn(['organizer', 'meeting_chair']);
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->string('organizer')->after('status')->default('');
            $table->string('meeting_chair')->after('organizer')->default('');
            $table->foreignId('unit_id')->nullable()->change();
        });

        DB::statement('
            UPDATE agendas
            SET organizer = e.full_name
            FROM employees e
            WHERE agendas.organizer_id = e.id
        ');

        DB::statement('
            UPDATE agendas
            SET meeting_chair = e.full_name
            FROM employees e
            WHERE agendas.meeting_chair_id = e.id
        ');

        Schema::table('agendas', function (Blueprint $table) {
            $table->dropForeign(['organizer_id']);
            $table->dropColumn('organizer_id');
            $table->dropForeign(['meeting_chair_id']);
            $table->dropColumn('meeting_chair_id');
        });
    }
};
