<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Tambah kolom username untuk login (username ATAU email).
     * Username existing di-backfill dari local-part email, lowercase,
     * dengan suffix angka bila duplikat.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 255)->nullable()->unique()->after('email');
        });

        $taken = [];

        DB::table('users')->select('id', 'email')->orderBy('id')->chunk(200, function ($users) use (&$taken) {
            foreach ($users as $user) {
                $base = strtolower(trim((string) Str::before($user->email, '@')));
                $base = $base !== '' ? $base : 'user'.$user->id;

                $username = $base;
                $counter = 1;
                while (isset($taken[$username]) || DB::table('users')->where('username', $username)->exists()) {
                    $username = $base.$counter;
                    $counter++;
                }
                $taken[$username] = true;

                DB::table('users')->where('id', $user->id)->update(['username' => $username]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
