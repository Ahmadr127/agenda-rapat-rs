<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->enum('type', ['diklat', 'pelatihan', 'rapat'])->default('rapat')->after('unit');
            $table->foreignId('bank_soal_id')->nullable()->after('type')->constrained('bank_soals')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('agendas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_soal_id');
            $table->dropColumn('type');
        });
    }
};
