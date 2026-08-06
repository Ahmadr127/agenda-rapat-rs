<?php

namespace Tests\Feature;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_displays_units(): void
    {
        $user = User::factory()->create();
        $unit = Unit::factory()->create(['name' => 'ICU']);

        $response = $this->actingAs($user)->get(route('admin.units.index'));

        $response->assertOk();
        $response->assertSee('ICU');
    }

    public function test_create_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.units.create'));

        $response->assertOk();
        $response->assertSee('Tambah Unit Baru');
    }

    public function test_unit_can_be_stored(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.units.store'), [
            'name' => 'Farmasi',
        ]);

        $response->assertRedirect(route('admin.units.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('units', ['name' => 'Farmasi']);
    }

    public function test_store_requires_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.units.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_edit_page_is_displayed(): void
    {
        $user = User::factory()->create();
        $unit = Unit::factory()->create(['name' => 'Radiologi']);

        $response = $this->actingAs($user)->get(route('admin.units.edit', $unit));

        $response->assertOk();
        $response->assertSee('Radiologi');
    }

    public function test_unit_can_be_updated(): void
    {
        $user = User::factory()->create();
        $unit = Unit::factory()->create(['name' => 'Lama']);

        $response = $this->actingAs($user)->put(route('admin.units.update', $unit), [
            'name' => 'Baru',
        ]);

        $response->assertRedirect(route('admin.units.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('units', ['name' => 'Baru']);
        $this->assertDatabaseMissing('units', ['name' => 'Lama']);
    }

    public function test_unit_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $unit = Unit::factory()->create(['name' => 'Hapus']);

        $response = $this->actingAs($user)->delete(route('admin.units.destroy', $unit));

        $response->assertRedirect(route('admin.units.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('units', ['name' => 'Hapus']);
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $response = $this->get(route('admin.units.index'));

        $response->assertRedirect(route('login'));
    }
}
