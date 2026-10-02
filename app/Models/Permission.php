<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['key', 'label', 'group'];

    /**
     * Katalog ability aplikasi. Key di sini adalah identifier teknis
     * (seperti nama route) yang dipakai Gate/Policy/Blade; daftar
     * ROLE yang memegangnya hidup di database (dikelola via UI).
     *
     * @return array<string, array{label: string, group: string}>
     */
    public static function catalog(): array
    {
        return [
            'users.manage' => ['label' => 'Kelola akun & penetapan role', 'group' => 'akun'],
            'roles.manage' => ['label' => 'Kelola role & izin', 'group' => 'akun'],
            'units.manage' => ['label' => 'Tulis data unit', 'group' => 'unit'],
            'rooms.manage' => ['label' => 'Tulis data ruangan', 'group' => 'ruangan'],
            'bank-soals.manage' => ['label' => 'Tulis data bank soal', 'group' => 'bank soal'],
            'agendas.manage' => ['label' => 'Kelola agenda unit sendiri', 'group' => 'agenda'],
            'agendas.manage-all' => ['label' => 'Kelola agenda semua unit', 'group' => 'agenda'],
            'employees.manage' => ['label' => 'Kelola pegawai unit sendiri', 'group' => 'pegawai'],
            'employees.manage-all' => ['label' => 'Kelola pegawai semua unit', 'group' => 'pegawai'],
        ];
    }

    public static function keys(): array
    {
        return array_keys(static::catalog());
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role');
    }
}
