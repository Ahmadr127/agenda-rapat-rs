<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SimutuOrganisasiSeeder extends Seeder
{
    /**
     * Pemetaan role Simutu (database/data/simutu_organisasi.json) ke
     * role aplikasi (tabel roles, lihat RbacSeeder). Hanya tiga role:
     *
     * - Administrator           -> Superadmin (akses penuh)
     * - Tim Mutu                -> Admin (kelola operasional unit sendiri)
     * - Validator               -> Admin (input data per unit)
     * - Staff (Pelaksana)       -> Staff (agenda unit sendiri)
     * - Koordinator (Leader Ruangan)      -> Admin
     * - Penanggung Jawab (PJ Shift)       -> Admin
     * - Supervisor (Leader Lintas Unit)   -> Admin
     * - Kepala unit (Manager Unit)        -> Admin
     * - Kepala (Divisi/Bagian)            -> Staff
     * - Dokter Spesialis        -> Staff
     */
    private const SIMUTU_TO_APP_ROLE = [
        1 => Role::SYSTEM_SUPERADMIN,
        2 => Role::SYSTEM_ADMIN,
        3 => Role::SYSTEM_ADMIN,
        4 => Role::SYSTEM_STAFF,
        5 => Role::SYSTEM_ADMIN,
        6 => Role::SYSTEM_ADMIN,
        7 => Role::SYSTEM_ADMIN,
        8 => Role::SYSTEM_ADMIN,
        9 => Role::SYSTEM_STAFF,
        10 => Role::SYSTEM_STAFF,
    ];

    /**
     * Data sumber: backup PostgreSQL Simutu (sql/simutu_backup.sql),
     * diekstrak ke database/data/simutu_organisasi.json.
     *
     * Sistem ini tidak memiliki tabel role, sehingga:
     * - structural_role = nama role Simutu (mis. "Kepala", "Staff")
     * - job_position    = deskripsi singkat role (mis. "Manager Unit")
     *
     * Setiap user hasil import juga dipetakan ke SATU role aplikasi
     * (tabel role_user) sesuai SIMUTU_TO_APP_ROLE di atas.
     */
    public function run(): void
    {
        $data = $this->loadData();

        $roles = collect($data['roles'])->keyBy('id');
        $unitIds = $this->seedUnits($data['units']);
        $appRoles = $this->resolveAppRoles();

        foreach ($data['users'] as $row) {
            /** @var array{nama_role: string, deskripsi_role: ?string} $role */
            $role = $roles->get((int) $row['role_id']);

            $user = $this->seedUser($row);

            Employee::updateOrCreate(
                ['nip' => $row['nip']],
                [
                    'user_id' => $user->id,
                    'full_name' => $row['nama_lengkap'],
                    'unit_id' => $unitIds[(int) $row['unit_id']],
                    'job_position' => $this->jobPosition($role),
                    'structural_role' => mb_substr($role['nama_role'], 0, 255),
                    'profession' => $row['profesi'],
                ]
            );

            $appRoleId = $appRoles[(int) $row['role_id']] ?? $appRoles['fallback'];

            if ($appRoleId !== null) {
                $user->roles()->sync([$appRoleId]);
            }
        }
    }

    /**
     * Upsert user Simutu. Password di JSON sudah berupa hash bcrypt,
     * sehingga tulis/baca SELALU lewat query builder agar tidak di-hash
     * ulang oleh cast 'hashed' pada model User (sekaligus agar kolom
     * password yang NOT NULL selalu terisi saat insert user baru).
     */
    private function seedUser(array $row): User
    {
        $existing = User::where('email', $row['email'])->first();

        if ($existing === null) {
            $id = DB::table('users')->insertGetId([
                'name' => $row['nama_lengkap'],
                'email' => $row['email'],
                'username' => $row['username'] ?? null,
                'password' => $row['password'],
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return User::findOrFail($id);
        }

        $existing->fill([
            'name' => $row['nama_lengkap'],
            'username' => $row['username'] ?? null,
            'email_verified_at' => now(),
        ])->save();

        DB::table('users')->where('id', $existing->id)->update([
            'password' => $row['password'],
        ]);

        return $existing->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    private function loadData(): array
    {
        $path = database_path('data/simutu_organisasi.json');

        $decoded = json_decode((string) file_get_contents($path), true);

        if (! is_array($decoded)) {
            throw new \RuntimeException("Tidak dapat membaca {$path}");
        }

        return $decoded;
    }

    /**
     * Ambil id role aplikasi untuk setiap role Simutu. Role aplikasi
     * dibuat oleh RbacSeeder, jadi pastikan urutan seed:
     * RbacSeeder dulu, baru SimutuOrganisasiSeeder (lihat DatabaseSeeder).
     *
     * @return array<int|string, int|null> role_id Simutu => id role aplikasi
     */
    private function resolveAppRoles(): array
    {
        $byName = Role::query()->pluck('id', 'name');

        $ids = [];

        foreach (self::SIMUTU_TO_APP_ROLE as $simutuId => $appRoleName) {
            $ids[$simutuId] = $byName->get($appRoleName);
        }

        $ids['fallback'] = $byName->get(Role::SYSTEM_STAFF);

        return $ids;
    }

    /**
     * @param  array<int, array{id: int|string, nama_unit: string}>  $units
     * @return array<int, int> id unit Simutu => id unit lokal
     */
    private function seedUnits(array $units): array
    {
        $existing = Unit::query()
            ->get()
            ->keyBy(fn (Unit $unit) => mb_strtolower($unit->name));

        $ids = [];

        foreach ($units as $unit) {
            $key = mb_strtolower($unit['nama_unit']);
            $model = $existing->get($key);

            if (! $model instanceof Unit) {
                $model = Unit::create(['name' => $unit['nama_unit']]);
                $existing->put($key, $model);
            }

            $ids[(int) $unit['id']] = $model->id;
        }

        return $ids;
    }

    /**
     * @param  array{nama_role: string, deskripsi_role: ?string}  $role
     */
    private function jobPosition(array $role): string
    {
        $deskripsi = trim((string) $role['deskripsi_role']);

        if ($deskripsi !== '' && mb_strlen($deskripsi) < 40) {
            return mb_substr($deskripsi, 0, 255);
        }

        return mb_substr($role['nama_role'], 0, 255);
    }
}
