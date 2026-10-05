<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Room;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    private function role(string $name): Role
    {
        return Role::where('name', $name)->firstOrFail();
    }

    private function makeAdmin(Unit $unit): User
    {
        $user = User::factory()->create();
        Employee::factory()->create(['user_id' => $user->id, 'unit_id' => $unit->id]);
        $user->roles()->sync([$this->role(Role::SYSTEM_ADMIN)->id]);

        return $user->fresh();
    }

    private function makeStaff(?Unit $unit = null): User
    {
        $user = User::factory()->create();
        if ($unit) {
            Employee::factory()->create(['user_id' => $user->id, 'unit_id' => $unit->id]);
        }
        $user->roles()->sync([$this->role(Role::SYSTEM_STAFF)->id]);

        return $user->fresh();
    }

    private function validRapatData(int $unitId, array $overrides = []): array
    {
        return array_merge([
            'title' => 'Rapat Koordinasi',
            'event_date' => '2026-05-10',
            'event_time' => '09:00',
            'unit_id' => $unitId,
            'event_leader_id' => Employee::factory()->create()->id,
            'room_id' => Room::factory()->create()->id,
            'type' => 'rapat',
        ], $overrides);
    }

    public function test_staff_cannot_manage_users_and_units(): void
    {
        $staff = $this->makeStaff();

        $this->actingAs($staff)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($staff)->post(route('admin.units.store'), ['name' => 'X'])->assertForbidden();
        $this->actingAs($staff)->get(route('admin.units.index'))->assertOk();
    }

    public function test_admin_cannot_write_units_but_can_write_rooms(): void
    {
        $admin = $this->makeAdmin(Unit::factory()->create());

        $this->actingAs($admin)->get(route('admin.units.index'))->assertOk();
        $this->actingAs($admin)->post(route('admin.units.store'), ['name' => 'X'])->assertForbidden();
        $this->actingAs($admin)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($admin)
            ->post(route('admin.rooms.store'), ['room_name' => 'Ruang Admin'])
            ->assertRedirect(route('admin.rooms.index'));
    }

    public function test_admin_agenda_scoped_to_own_unit(): void
    {
        $unitA = Unit::factory()->create();
        $unitB = Unit::factory()->create();
        $admin = $this->makeAdmin($unitA);

        // Buat di unit sendiri: sukses.
        $this->actingAs($admin)
            ->post(route('admin.agendas.store'), $this->validRapatData($unitA->id))
            ->assertRedirect(route('admin.agendas.index'));

        // Buat di unit lain: ditolak validasi.
        $this->actingAs($admin)
            ->post(route('admin.agendas.store'), $this->validRapatData($unitB->id))
            ->assertSessionHasErrors('unit_id');

        // Ubah agenda unit lain: 403.
        $other = Agenda::factory()->create(['unit_id' => $unitB->id]);
        $this->actingAs($admin)
            ->put(route('admin.agendas.update', $other), $this->validRapatData($unitB->id))
            ->assertForbidden();

        // Ubah agenda sendiri: sukses.
        $own = Agenda::factory()->create(['unit_id' => $unitA->id]);
        $this->actingAs($admin)
            ->put(route('admin.agendas.update', $own), $this->validRapatData($unitA->id))
            ->assertRedirect(route('admin.agendas.index'));
    }

    public function test_staff_agenda_scoped_to_own_unit(): void
    {
        $unitA = Unit::factory()->create();
        $unitB = Unit::factory()->create();
        $staff = $this->makeStaff($unitA);

        $this->actingAs($staff)->get(route('admin.agendas.index'))->assertOk();

        // Buat di unit sendiri: sukses.
        $this->actingAs($staff)
            ->post(route('admin.agendas.store'), $this->validRapatData($unitA->id))
            ->assertRedirect(route('admin.agendas.index'));

        // Buat di unit lain: ditolak validasi.
        $this->actingAs($staff)
            ->post(route('admin.agendas.store'), $this->validRapatData($unitB->id))
            ->assertSessionHasErrors('unit_id');
    }

    public function test_sdm_admin_locked_to_rapat(): void
    {
        $sdm = Unit::factory()->create(['name' => 'SDM']);
        $admin = $this->makeAdmin($sdm);
        // Leader dibuat eksplisit di unit SDM agar tidak ada pembuatan unit
        // acak (nama unit factory terbatas → rawan tabrakan 'SDM').
        $leader = Employee::factory()->create(['unit_id' => $sdm->id]);
        $bankSoal = \App\Models\BankSoal::factory()->create();
        \App\Models\Question::factory()->create(['bank_soal_id' => $bankSoal->id]);

        $this->actingAs($admin)
            ->post(route('admin.agendas.store'), $this->validRapatData($sdm->id, [
                'type' => 'diklat',
                'event_end_time' => '12:00',
                'bank_soal_id' => $bankSoal->id,
                'event_leader_id' => $leader->id,
            ]))
            ->assertSessionHasErrors('type');

        $this->actingAs($admin)
            ->post(route('admin.agendas.store'), $this->validRapatData($sdm->id, [
                'event_leader_id' => $leader->id,
            ]))
            ->assertRedirect(route('admin.agendas.index'));
    }

    public function test_admin_employee_scoped_to_own_unit(): void
    {
        $unitA = Unit::factory()->create();
        $unitB = Unit::factory()->create();
        $admin = $this->makeAdmin($unitA);

        $empA = Employee::factory()->create(['unit_id' => $unitA->id]);
        $empB = Employee::factory()->create(['unit_id' => $unitB->id]);

        $this->actingAs($admin)->get(route('admin.employees.edit', $empA))->assertOk();
        $this->actingAs($admin)->get(route('admin.employees.edit', $empB))->assertForbidden();

        // Simpan pegawai ke unit lain: 403.
        $this->actingAs($admin)->post(route('admin.employees.store'), [
            'nip' => '998877665544332211',
            'full_name' => 'Pegawai Lintas Unit',
            'unit_id' => $unitB->id,
            'job_position' => 'Staf',
            'structural_role' => 'Staf',
            'profession' => 'Administrasi',
        ])->assertForbidden();
    }

    public function test_staff_sidebar_shows_only_agenda_and_dashboard(): void
    {
        $staff = $this->makeStaff(Unit::factory()->create());

        $response = $this->actingAs($staff)->get(route('admin.agendas.index'))->assertOk();

        // Menu yang terlihat: Beranda + Agenda.
        $response->assertSee('Beranda');
        $response->assertSee(route('admin.agendas.index'), false);

        // Menu lain tersembunyi (cek URL agar tidak tabrakan dengan isi halaman).
        foreach ([
            'admin.employees.index',
            'admin.employee-recaps.index',
            'admin.rooms.index',
            'admin.units.index',
            'admin.bank-soals.index',
            'admin.users.index',
            'admin.roles.index',
        ] as $routeName) {
            $response->assertDontSee(route($routeName), false);
        }
    }

    public function test_admin_sidebar_shows_operational_menus_without_account(): void
    {
        $admin = $this->makeAdmin(Unit::factory()->create());

        $response = $this->actingAs($admin)->get(route('admin.agendas.index'))->assertOk();

        foreach ([
            'admin.employees.index',
            'admin.employee-recaps.index',
            'admin.rooms.index',
            'admin.agendas.index',
            'admin.bank-soals.index',
        ] as $routeName) {
            $response->assertSee(route($routeName), false);
        }

        foreach ([
            'admin.units.index',
            'admin.users.index',
            'admin.roles.index',
        ] as $routeName) {
            $response->assertDontSee(route($routeName), false);
        }
    }

    public function test_roles_management_requires_permission(): void
    {
        $staff = $this->makeStaff();
        $admin = $this->makeAdmin(Unit::factory()->create());
        $superadmin = User::factory()->create();

        $this->actingAs($staff)->get(route('admin.roles.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.roles.index'))->assertForbidden();
        $this->actingAs($superadmin)->get(route('admin.roles.index'))->assertOk();

        // Superadmin bisa membuat role baru (nama jadi identitas).
        $permissionIds = \App\Models\Permission::whereIn('key', ['agendas.manage'])->pluck('id')->all();
        $this->actingAs($superadmin)->post(route('admin.roles.store'), [
            'name' => 'Koordinator',
            'description' => 'Koordinator ruangan',
            'permission_ids' => $permissionIds,
        ])->assertRedirect(route('admin.roles.index'));

        $this->assertDatabaseHas('roles', ['name' => 'Koordinator']);
    }

    public function test_backfill_maps_legacy_manager_to_superadmin(): void
    {
        $legacy = User::factory()->create();
        $legacy->roles()->detach();
        Employee::factory()->create(['user_id' => $legacy->id, 'job_position' => 'MANAGER IT']);

        $plain = User::factory()->create();
        $plain->roles()->detach();

        $this->artisan('users:assign-roles', ['--force' => true])->assertSuccessful();

        $this->assertTrue($legacy->fresh()->hasRole(Role::SYSTEM_SUPERADMIN));
        $this->assertTrue($plain->fresh()->hasRole(Role::SYSTEM_STAFF));
    }

    public function test_role_form_pages_render_interactive_data(): void
    {
        $superadmin = User::factory()->create();

        $this->actingAs($superadmin)->get(route('admin.roles.create'))
            ->assertOk()
            ->assertSee('Mulai Cepat', false)
            ->assertSee('Ringkasan Role', false);

        $role = Role::where('name', Role::SYSTEM_ADMIN)->firstOrFail();
        $this->actingAs($superadmin)->get(route('admin.roles.edit', $role))
            ->assertOk()
            ->assertSee('Ringkasan Role', false);
    }

    public function test_master_permissions_are_split_per_module(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Peran Tes']);
        $role->permissions()->sync(
            \App\Models\Permission::where('key', 'units.manage')->pluck('id')
        );
        $user->roles()->sync([$role->id]);

        // Boleh tulis unit...
        $this->actingAs($user->fresh())
            ->post(route('admin.units.store'), ['name' => 'Unit Tes '.uniqid()])
            ->assertRedirect(route('admin.units.index'));

        // ...tapi tidak boleh tulis ruangan & bank soal.
        $this->actingAs($user->fresh())
            ->post(route('admin.rooms.store'), ['room_name' => 'Ruang Tes'])
            ->assertForbidden();
        $this->actingAs($user->fresh())
            ->post(route('admin.bank-soals.store'), [
                'title' => 'Bank Soal Tes',
                'questions' => [[
                    'question_text' => 'Apa ibu kota Indonesia?',
                    'option_a' => 'Jakarta',
                    'option_b' => 'Bandung',
                    'option_c' => 'Surabaya',
                    'correct_option' => 'a',
                ]],
            ])
            ->assertForbidden();
    }
}
