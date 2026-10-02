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
        'Operator Unit' => [
            'agendas.manage',
            'employees.manage',
        ],
        'Viewer / Pimpinan' => [],
    ];

    public const ROLE_DESCRIPTIONS = [
        'Superadmin' => 'Akses penuh semua fitur dan pengaturan sistem.',
        'Operator Unit' => 'Mengelola agenda dan pegawai pada unit sendiri.',
        'Viewer / Pimpinan' => 'Hanya melihat data, rekap, dan export.',
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
