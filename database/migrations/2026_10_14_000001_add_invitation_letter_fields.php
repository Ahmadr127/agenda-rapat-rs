<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            if (! Schema::hasColumn('agendas', 'letter_number')) {
                $table->string('letter_number', 100)->nullable()->after('material_file_path');
            }
        });

        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'signature_path')) {
                $table->string('signature_path')->nullable()->after('profession');
            }
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropColumn('letter_number');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('signature_path');
        });
    }
};
