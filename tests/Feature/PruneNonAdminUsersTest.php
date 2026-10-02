<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruneNonAdminUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_does_not_delete_anything(): void
    {
        User::factory()->create(['email' => 'admin@rsazra.co.id', 'username' => 'admin']);
        User::factory()->count(3)->create();

        $this->artisan('users:prune-non-admin', ['--dry-run' => true])
            ->assertSuccessful();

        $this->assertEquals(4, User::count());
    }

    public function test_deletes_non_admin_but_preserves_employees(): void
    {
        $admin = User::factory()->create(['email' => 'admin@rsazra.co.id', 'username' => 'admin']);
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        // Jadikan akun biasa: lepas role bawaan factory.
        $userA->roles()->detach();
        $userB->roles()->detach();
        $employeeA = Employee::factory()->create(['user_id' => $userA->id]);
        $employeeB = Employee::factory()->create(['user_id' => $userB->id]);

        $employeeCountBefore = Employee::count();

        $this->artisan('users:prune-non-admin', ['--force' => true])
            ->assertSuccessful();

        // Admin tetap, sisanya hilang.
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertDatabaseMissing('users', ['id' => $userA->id]);
        $this->assertDatabaseMissing('users', ['id' => $userB->id]);

        // Data pegawai UTUH, hanya relasinya yang dilepas.
        $this->assertEquals($employeeCountBefore, Employee::count());
        $this->assertDatabaseHas('employees', ['id' => $employeeA->id, 'user_id' => null]);
        $this->assertDatabaseHas('employees', ['id' => $employeeB->id, 'user_id' => null]);
    }

    public function test_privileged_user_is_protected_by_default(): void
    {
        User::factory()->create(['email' => 'admin@rsazra.co.id', 'username' => 'admin']);
        // Factory memberi role superadmin (pemegang izin kelola).
        $privileged = User::factory()->create();
        $regular = User::factory()->create();
        $regular->roles()->detach();

        $this->artisan('users:prune-non-admin', ['--force' => true])
            ->assertSuccessful();

        $this->assertDatabaseHas('users', ['id' => $privileged->id]);
        $this->assertDatabaseMissing('users', ['id' => $regular->id]);
    }

    public function test_delete_privileged_option_removes_them_too(): void
    {
        User::factory()->create(['email' => 'admin@rsazra.co.id', 'username' => 'admin']);
        $privileged = User::factory()->create();

        $this->artisan('users:prune-non-admin', ['--force' => true, '--delete-privileged' => true])
            ->assertSuccessful();

        $this->assertDatabaseMissing('users', ['id' => $privileged->id]);
    }

    public function test_keep_email_option_is_respected(): void
    {
        User::factory()->create(['email' => 'admin@rsazra.co.id', 'username' => 'admin']);
        $special = User::factory()->create(['email' => 'penting@rsazra.co.id']);

        $this->artisan('users:prune-non-admin', [
            '--force' => true,
            '--keep-email' => ['penting@rsazra.co.id'],
        ])->assertSuccessful();

        $this->assertDatabaseHas('users', ['id' => $special->id]);
    }
}
