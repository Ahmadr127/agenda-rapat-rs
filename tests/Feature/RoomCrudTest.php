<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_displays_rooms(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['room_name' => 'Ruang Rapat Utama']);

        $response = $this->actingAs($user)->get(route('admin.rooms.index'));

        $response->assertOk();
        $response->assertSee('Ruang Rapat Utama');
    }

    public function test_create_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.rooms.create'));

        $response->assertOk();
    }

    public function test_room_can_be_stored(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.rooms.store'), [
            'room_name' => 'Ruang Baru',
            'description' => 'Deskripsi ruang baru',
        ]);

        $response->assertRedirect(route('admin.rooms.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('rooms', ['room_name' => 'Ruang Baru']);
    }

    public function test_store_requires_room_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.rooms.store'), [
            'room_name' => '',
        ]);

        $response->assertSessionHasErrors('room_name');
    }

    public function test_store_allows_null_description(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.rooms.store'), [
            'room_name' => 'Ruang Tanpa Deskripsi',
        ]);

        $response->assertRedirect(route('admin.rooms.index'));
        $this->assertDatabaseHas('rooms', ['room_name' => 'Ruang Tanpa Deskripsi']);
    }

    public function test_edit_page_is_displayed(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['room_name' => 'Ruang Edit']);

        $response = $this->actingAs($user)->get(route('admin.rooms.edit', $room));

        $response->assertOk();
        $response->assertSee('Ruang Edit');
    }

    public function test_room_can_be_updated(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['room_name' => 'Nama Lama']);

        $response = $this->actingAs($user)->put(route('admin.rooms.update', $room), [
            'room_name' => 'Nama Baru',
            'description' => 'Deskripsi diperbarui',
        ]);

        $response->assertRedirect(route('admin.rooms.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('rooms', ['room_name' => 'Nama Baru']);
        $this->assertDatabaseMissing('rooms', ['room_name' => 'Nama Lama']);
    }

    public function test_update_requires_room_name(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create();

        $response = $this->actingAs($user)->put(route('admin.rooms.update', $room), [
            'room_name' => '',
        ]);

        $response->assertSessionHasErrors('room_name');
    }

    public function test_room_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['room_name' => 'Ruang Hapus']);

        $response = $this->actingAs($user)->delete(route('admin.rooms.destroy', $room));

        $response->assertRedirect(route('admin.rooms.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('rooms', ['room_name' => 'Ruang Hapus']);
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $response = $this->get(route('admin.rooms.index'));

        $response->assertRedirect(route('login'));
    }
}
