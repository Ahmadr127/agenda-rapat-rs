<?php

namespace App\Support;

use App\Models\User;

/**
 * Akses tipe agenda (rapat / diklat / pelatihan) berbasis permission.
 *
 * Permission yang dipakai:
 * - agendas.type-rapat
 * - agendas.type-diklat
 * - agendas.type-pelatihan
 *
 * Aturan unit SDM (non `agendas.manage-all` + unit SDM → hanya rapat)
 * tetap ditegakkan sebagai batas tambahan di atas permission, agar
 * aturan bisnis lama tidak jebol saat role memegang semua izin tipe.
 */
class AgendaTypeAccess
{
    /**
     * @return array<string, array{permission: string, label: string}>
     */
    public static function types(): array
    {
        return [
            'rapat' => ['permission' => 'agendas.type-rapat', 'label' => 'Rapat'],
            'diklat' => ['permission' => 'agendas.type-diklat', 'label' => 'Diklat'],
            'pelatihan' => ['permission' => 'agendas.type-pelatihan', 'label' => 'Pelatihan'],
        ];
    }

    /**
     * Daftar tipe yang boleh dipilih user, masing-masing
     * berbentuk ['id' => 'rapat', 'name' => 'Rapat'].
     *
     * @return list<array{id: string, name: string}>
     */
    public static function allowedTypes(User $user): array
    {
        $allowed = [];

        foreach (static::types() as $id => $meta) {
            if ($user->can($meta['permission'])) {
                $allowed[] = ['id' => $id, 'name' => $meta['label']];
            }
        }

        if (! $user->can('agendas.manage-all') && $user->employee?->unit?->name === 'SDM') {
            $allowed = array_values(array_filter($allowed, fn (array $type) => $type['id'] === 'rapat'));
        }

        return $allowed;
    }

    /**
     * @return list<string>
     */
    public static function allowedTypeIds(User $user): array
    {
        return array_column(static::allowedTypes($user), 'id');
    }

    /**
     * Tipe tunggal yang otomatis terpilih bila user hanya memegang
     * satu izin tipe; null bila nol/lebih dari satu.
     */
    public static function soleType(User $user): ?string
    {
        $ids = static::allowedTypeIds($user);

        return count($ids) === 1 ? $ids[0] : null;
    }

    public static function label(string $type): string
    {
        return static::types()[$type]['label'] ?? ucfirst($type);
    }
}
