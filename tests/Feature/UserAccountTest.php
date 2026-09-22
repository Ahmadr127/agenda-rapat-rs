<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAccountTest extends TestCase
{
    use RefreshDatabase;

    private function createManager(): User
    {
        $unit = Unit::create(['name' => 'IT']);

        $user = User::factory()->create();

        Employee::create([
            'nip' => '900000000000000001',
            'full_name' => 'Manager IT',
            'unit_id' => $unit->id,
            'job_position' => 'MANAGER IT',
            'structural_role' => 'Manager',
            'profession' => 'Administrasi',
            'user_id' => $user->id,
        ]);

        return $user;
    }

    private function createPlainEmployee(string $nip = '900000000000000002'): Employee
    {
        $unit = Unit::create(['name' => 'Unit '.$nip]);

        return Employee::create([
            'nip' => $nip,
            'full_name' => 'Pegawai '.$nip,
            'unit_id' => $unit->id,
            'job_position' => 'Pelaksana',
            'structural_role' => 'Staf',
            'profession' => 'Administrasi',
        ]);
    }

    public function test_manager_can_create_account_linked_to_employee(): void
    {
        $manager = $this->createManager();
        $employee = $this->createPlainEmployee();

        $response = $this->actingAs($manager)->post(route('admin.users.store'), [
            'employee_id' => $employee->id,
            'name' => 'Akun Baru',
            'username' => 'akun.baru',
            'email' => 'akun.baru@rsazra.co.id',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $user = User::where('username', 'akun.baru')->firstOrFail();
        $this->assertTrue(Hash::check('secret123', $user->password));
        $this->assertSame($user->id, $employee->refresh()->user_id);
    }

    public function test_create_uses_default_password_when_empty(): void
    {
        $manager = $this->createManager();
        $employee = $this->createPlainEmployee();

        $this->actingAs($manager)->post(route('admin.users.store'), [
            'employee_id' => $employee->id,
            'name' => 'Akun Default',
            'username' => 'akun.default',
            'email' => 'akun.default@rsazra.co.id',
        ])->assertRedirect(route('admin.users.index'));

        $user = User::where('username', 'akun.default')->firstOrFail();
        $this->assertTrue(Hash::check('rsazra2026', $user->password));
    }

    public function test_cannot_create_second_account_for_same_employee(): void
    {
        $manager = $this->createManager();
        $employee = $this->createPlainEmployee();
        $employee->update(['user_id' => User::factory()->create()->id]);

        $response = $this->actingAs($manager)->post(route('admin.users.store'), [
            'employee_id' => $employee->id,
            'name' => 'Duplikat',
            'username' => 'duplikat',
            'email' => 'duplikat@rsazra.co.id',
        ]);

        $response->assertSessionHasErrors('employee_id');
        $this->assertNull(User::where('username', 'duplikat')->first());
    }

    public function test_manager_can_update_account_and_unit(): void
    {
        $manager = $this->createManager();
        $employee = $this->createPlainEmployee();
        $user = User::factory()->create();
        $employee->update(['user_id' => $user->id]);

        $newUnit = Unit::create(['name' => 'Unit Baru']);

        $response = $this->actingAs($manager)->put(route('admin.users.update', $user), [
            'name' => 'Nama Baru',
            'username' => $user->username,
            'email' => $user->email,
            'unit_id' => $newUnit->id,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertSame('Nama Baru', $user->refresh()->name);
        $this->assertSame($newUnit->id, $employee->refresh()->unit_id);
    }

    public function test_manager_can_delete_account_but_employee_remains(): void
    {
        $manager = $this->createManager();
        $employee = $this->createPlainEmployee();
        $user = User::factory()->create();
        $employee->update(['user_id' => $user->id]);

        $response = $this->actingAs($manager)->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertNull(User::find($user->id));
        $this->assertNotNull(Employee::find($employee->id));
        $this->assertNull($employee->refresh()->user_id);
    }

    public function test_manager_cannot_delete_own_account(): void
    {
        $manager = $this->createManager();

        $response = $this->actingAs($manager)->delete(route('admin.users.destroy', $manager));

        $response->assertForbidden();
        $this->assertNotNull(User::find($manager->id));
    }

    public function test_regular_user_cannot_manage_accounts(): void
    {
        $user = User::factory()->create();
        $employee = $this->createPlainEmployee();

        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.users.create'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.users.store'), [
            'employee_id' => $employee->id,
            'name' => 'X',
            'username' => 'x',
            'email' => 'x@rsazra.co.id',
        ])->assertForbidden();
    }
}
