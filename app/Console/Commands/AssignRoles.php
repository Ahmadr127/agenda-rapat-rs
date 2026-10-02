<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Console\Command;

class AssignRoles extends Command
{
    protected $signature = 'users:assign-roles
                                {--default= : Nama role untuk akun yang belum punya role (default: Operator Unit)}
                                {--dry-run : Tampilkan rencana tanpa mengubah data}
                                {--force : Lewati konfirmasi}';

    protected $description = 'Backfill satu kali: petakan akun lama ke role RBAC (MANAGER IT → superadmin).';

    public function handle(): int
    {
        $this->call('db:seed', ['--class' => RbacSeeder::class, '--force' => true]);

        $defaultName = (string) ($this->option('default') ?: Role::SYSTEM_OPERATOR);
        $defaultRole = Role::where('name', $defaultName)->first();

        if (! $defaultRole) {
            $this->error("Role \"{$defaultName}\" tidak ditemukan.");

            return self::FAILURE;
        }

        $superadmin = Role::where('name', Role::SYSTEM_SUPERADMIN)->firstOrFail();

        $rows = [];
        $planned = [];

        // 1. Akun admin default selalu superadmin.
        $admin = User::where('email', 'admin@rsazra.co.id')->first();
        if ($admin) {
            $planned[$admin->id] = ['user' => $admin, 'roles' => [$superadmin->id], 'alasan' => 'akun admin default'];
        }

        // 2. Jembatan legacy satu kali: jabatan MANAGER IT → superadmin.
        //    Setelah backfill ini, job_position tidak lagi dipakai untuk akses.
        $managers = User::whereHas('employee', fn ($q) => $q->where('job_position', 'MANAGER IT'))->get();
        foreach ($managers as $user) {
            $planned[$user->id] = ['user' => $user, 'roles' => [$superadmin->id], 'alasan' => 'jabatan MANAGER IT (legacy)'];
        }

        // 3. Akun tanpa role mendapat role default.
        $roleless = User::whereDoesntHave('roles')->whereNotIn('id', array_keys($planned))->get();
        foreach ($roleless as $user) {
            $planned[$user->id] = ['user' => $user, 'roles' => [$defaultRole->id], 'alasan' => "tanpa role → {$defaultName}"];
        }

        foreach ($planned as $plan) {
            $rows[] = [$plan['user']->id, $plan['user']->name, $plan['user']->email, $plan['alasan']];
        }

        $untouched = User::whereHas('roles')->whereNotIn('id', array_keys($planned))->count();

        $this->table(['ID', 'Nama', 'Email', 'Rencana'], $rows);
        $this->info('Akun akan diberi role: '.count($rows).' | Sudah punya role (tidak diubah): '.$untouched);

        if (empty($planned)) {
            $this->info('Tidak ada yang perlu diubah.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info('[DRY-RUN] Tidak ada data yang diubah.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Terapkan penetapan role di atas?', false)) {
            $this->warn('Dibatalkan.');

            return self::SUCCESS;
        }

        foreach ($planned as $plan) {
            $plan['user']->roles()->sync($plan['roles']);
        }

        $this->info('Selesai. '.count($planned).' akun diberi role.');

        return self::SUCCESS;
    }
}
