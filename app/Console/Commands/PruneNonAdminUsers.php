<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneNonAdminUsers extends Command
{
    protected $signature = 'users:prune-non-admin
                                {--keep-email=* : Email tambahan yang dipertahankan (selain admin default)}
                                {--keep-username=* : Username tambahan yang dipertahankan (selain admin default)}
                                {--delete-privileged : Hapus juga akun yang memegang izin kelola (kecuali yang masuk keep-list)}
                                {--dry-run : Tampilkan rencana penghapusan tanpa benar-benar menghapus}
                                {--force : Lewati konfirmasi}';

    protected $description = 'Hapus semua akun user kecuali admin. Data pegawai TIDAK dihapus (relasi user_id di-null-kan).';

    public function handle(): int
    {
        // Akun admin default — JANGAN diubah tanpa koordinasi.
        $keepEmails = array_unique(array_merge(
            ['admin@rsazra.co.id'],
            $this->option('keep-email') ?? []
        ));
        $keepUsernames = array_unique(array_merge(
            ['admin'],
            $this->option('keep-username') ?? []
        ));

        $targets = User::query()
            ->whereNotIn('email', $keepEmails)
            ->whereNotIn('username', $keepUsernames)
            // Proteksi lapis kedua: akun pemegang izin kelola dianggap admin.
            ->when(! $this->option('delete-privileged'), function ($query) {
                $query->whereDoesntHave('roles.permissions', fn ($q) => $q->whereIn('key', ['users.manage', 'roles.manage']));
            })
            ->orderBy('id')
            ->get(['id', 'name', 'username', 'email']);

        $totalUsers = User::count();
        $employeeCountBefore = Employee::count();
        $linkedEmployees = Employee::whereIn('user_id', $targets->pluck('id'))->count();

        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Total akun user', $totalUsers],
                ['Akun dipertahankan (admin)', $totalUsers - $targets->count()],
                ['Akun akan dihapus', $targets->count()],
                ['Pegawai ter-link (user_id → NULL, data AMAN)', $linkedEmployees],
                ['Total data pegawai (tidak boleh berubah)', $employeeCountBefore],
            ]
        );

        if ($targets->isEmpty()) {
            $this->info('Tidak ada akun yang perlu dihapus.');

            return self::SUCCESS;
        }

        $this->comment('Contoh akun yang akan dihapus (maks. 20):');
        $this->table(
            ['ID', 'Nama', 'Username', 'Email'],
            $targets->take(20)->map(fn ($u) => [$u->id, $u->name, $u->username, $u->email])->all()
        );

        if ($this->option('dry-run')) {
            $this->info('[DRY-RUN] Tidak ada data yang dihapus.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm("Hapus {$targets->count()} akun di atas? Data pegawai TIDAK akan dihapus.", false)) {
            $this->warn('Dibatalkan.');

            return self::SUCCESS;
        }

        $deleted = 0;
        $unlinked = 0;

        foreach ($targets->chunk(500) as $chunk) {
            $ids = $chunk->pluck('id')->all();

            DB::transaction(function () use ($ids, &$deleted, &$unlinked) {
                // PENTING: hanya putuskan relasi, JANGAN hapus baris employees.
                // (FK employees.user_id juga nullOnDelete sebagai pengaman lapis kedua.)
                $unlinked += Employee::whereIn('user_id', $ids)->update(['user_id' => null]);
                $deleted += User::whereIn('id', $ids)->delete();
            });
        }

        $employeeCountAfter = Employee::count();

        $this->info("Selesai. {$deleted} akun dihapus, {$unlinked} pegawai dilepas tautannya.");
        $this->info("Data pegawai: {$employeeCountBefore} → {$employeeCountAfter} (harus sama).");

        if ($employeeCountBefore !== $employeeCountAfter) {
            $this->error('PERINGATAN: jumlah data pegawai berubah! Segera investigasi.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
