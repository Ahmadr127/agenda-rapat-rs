<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    /**
     * Data awal role→permission, di-key berdasarkan NAMA role.
     * Setelah seed, komposisi ini dikelola lewat UI, bukan kode.
     *
     * Hanya tiga role bawaan, semuanya memakai permission yang sudah
     * ada di Permission::catalog() (tidak ada permission baru):
     * - Superadmin: semua akses.
     * - Admin: kelola operasional unit sendiri (ruangan, bank soal,
     *   agenda, pegawai) tanpa users/roles/units dan tanpa lintas unit.
     * - Staff: kelola agenda unit sendiri saja.
     */
    public const ROLE_PERMISSIONS = [
        'Superadmin' => [
            'users.manage',
            'roles.manage',
            'units.manage',
            'rooms.manage',
            'bank-soals.manage',
            'agendas.manage',
            'agendas.manage-all',
            'employees.manage',
            'employees.manage-all',
        ],
        'Admin' => [
            'rooms.manage',
            'bank-soals.manage',
            'agendas.manage',
            'employees.manage',
        ],
        'Staff' => [
            'agendas.manage',
        ],
    ];

    public const ROLE_DESCRIPTIONS = [
        'Superadmin' => 'Akses penuh semua fitur dan pengaturan sistem.',
        'Admin' => 'Mengelola ruangan, bank soal, agenda, dan pegawai pada unit sendiri.',
        'Staff' => 'Mengelola agenda pada unit sendiri.',
    ];

    public function run(): void
    {
        foreach (Permission::catalog() as $key => $meta) {
            Permission::firstOrCreate(
                ['key' => $key],
                ['label' => $meta['label'], 'group' => $meta['group']],
            );
        }

        foreach (static::ROLE_PERMISSIONS as $name => $keys) {
            $role = Role::firstOrCreate(
                ['name' => $name],
                ['description' => static::ROLE_DESCRIPTIONS[$name] ?? null],
            );

            $ids = Permission::whereIn('key', $keys)->pluck('id');
            $role->permissions()->sync($ids);
        }

        $this->migrateLegacyMasterPermission();
        $this->migrateLegacyRoles();
    }

    /**
     * Migrasi satu kali: role lama (sebelum penyederhanaan 3 role)
     * dipetakan ke padanannya, user-nya dipindahkan, lalu role lama
     * dihapus agar hanya tersisa Superadmin, Admin, Staff.
     */
    private function migrateLegacyRoles(): void
    {
        $mapping = [
            'Operator Unit' => 'Admin',
            'Pengelola Konten' => 'Admin',
            'Viewer / Pimpinan' => 'Staff',
        ];

        foreach ($mapping as $oldName => $newName) {
            $old = Role::where('name', $oldName)->first();
            $new = Role::where('name', $newName)->first();

            if (! $old || ! $new || $old->id === $new->id) {
                continue;
            }

            $userIds = $old->users()->pluck('users.id');

            $old->users()->detach();

            foreach ($userIds as $userId) {
                $new->users()->syncWithoutDetaching([$userId]);
            }

            $old->delete();
        }
    }
    /**
     * Migrasi satu kali: izin gabungan lama master.manage dipecah menjadi
     * units.manage + rooms.manage + bank-soals.manage pada role yang
     * memegangnya, lalu baris izin lama dihapus.
     */
    private function migrateLegacyMasterPermission(): void
    {
        $legacy = Permission::where('key', 'master.manage')->first();

        if (! $legacy) {
            return;
        }

        $replacements = Permission::whereIn('key', [
            'units.manage',
            'rooms.manage',
            'bank-soals.manage',
        ])->pluck('id');

        foreach ($legacy->roles as $role) {
            $role->permissions()->syncWithoutDetaching($replacements);
        }

        $legacy->delete();
    }
}
