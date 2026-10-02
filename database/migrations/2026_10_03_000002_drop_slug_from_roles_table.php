<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom slug dihapus total — identitas role memakai name.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Index wajib dilepas dulu (wajib di SQLite).
            $table->dropUnique('roles_slug_unique');
            $table->dropColumn('slug');
            $table->unique('name', 'roles_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique('roles_name_unique');
            $table->string('slug', 50)->nullable()->unique();
        });
    }
};
