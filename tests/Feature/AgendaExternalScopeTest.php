<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\Employee;
use App\Models\Room;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AgendaExternalScopeTest extends TestCase
{
    use RefreshDatabase;

    private function createRapat(?string $scope = null, bool $hasMembers = false, array $memberIds = []): Agenda
    {
        $unit = Unit::factory()->create();
        $room = Room::factory()->create();
        $leader = Employee::factory()->create(['unit_id' => $unit->id]);

        $agenda = Agenda::factory()->create([
            'type' => 'rapat',
            'title' => $scope === 'external' ? 'Rapat Eksternal' : 'Rapat Internal',
            'event_date' => today(),
            'event_time' => '09:00',
            'unit_id' => $unit->id,
            'event_leader_id' => $leader->id,
            'room_id' => $room->id,
            'scope' => $scope,
            'has_members' => $hasMembers,
        ]);

        foreach ($memberIds as $employeeId) {
            $agenda->employees()->attach($employeeId, ['signature_image_path' => null]);
        }

        return $agenda;
    }

    public function test_rapat_agenda_defaults_to_internal_scope_and_open_roster(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(
            route('admin.agendas.store'),
            [
                'title' => 'Rapat Default',
                'event_date' => today()->toDateString(),
                'event_time' => '09:00',
                'unit_id' => Unit::factory()->create()->id,
                'event_leader_id' => Employee::factory()->create()->id,
                'room_id' => Room::factory()->create()->id,
                'type' => 'rapat',
            ],
        );

        $response->assertRedirect(route('admin.agendas.index'));
        $this->assertDatabaseHas('agendas', [
            'title' => 'Rapat Default',
            'type' => 'rapat',
            'scope' => 'internal',
            'has_members' => false,
        ]);
    }

    public function test_rapat_agenda_can_be_stored_as_external_with_member_roster(): void
    {
        $user = User::factory()->create();
        $member = Employee::factory()->create();

        $response = $this->actingAs($user)->post(
            route('admin.agendas.store'),
            [
                'title' => 'Rapat Eksternal',
                'event_date' => today()->toDateString(),
                'event_time' => '09:00',
                'unit_id' => Unit::factory()->create()->id,
                'event_leader_id' => Employee::factory()->create()->id,
                'room_id' => Room::factory()->create()->id,
                'type' => 'rapat',
                'scope' => 'external',
                'member_ids' => [$member->id],
            ],
        );

        $response->assertRedirect(route('admin.agendas.index'));
        $this->assertDatabaseHas('agendas', [
            'title' => 'Rapat Eksternal',
            'scope' => 'external',
            'has_members' => true,
        ]);
        $this->assertDatabaseHas('agenda_employee', [
            'agenda_id' => Agenda::where('title', 'Rapat Eksternal')->first()->id,
            'employee_id' => $member->id,
        ]);
    }

    public function test_public_agenda_list_filters_scope_by_host(): void
    {
        config([
            'agenda.internal_domain' => 'rsazra.local',
            'agenda.external_domain' => 'agenda.rsazra.co.id',
            'agenda.default_scope' => 'internal',
        ]);

        $internal = $this->createRapat('internal');
        $external = $this->createRapat('external');

        // Domain internal: melihat semua agenda.
        $response = $this->call('GET', '/', [], [], [], ['HTTP_HOST' => 'rsazra.local']);
        $response->assertOk();
        $response->assertSee($internal->title);
        $response->assertSee($external->title);

        // Domain eksternal: hanya agenda eksternal.
        $response = $this->call('GET', '/', [], [], [], ['HTTP_HOST' => 'agenda.rsazra.co.id']);
        $response->assertOk();
        $response->assertSee($external->title);
        $response->assertDontSee($internal->title);

        // Host tidak dikenal = internal.
        $response = $this->call('GET', '/', [], [], [], ['HTTP_HOST' => 'unknown.example.com']);
        $response->assertOk();
        $response->assertSee($internal->title);
    }

    public function test_non_member_cannot_sign_public_attendance_for_roster_agenda(): void
    {
        Storage::fake('public');

        $agenda = $this->createRapat('internal', true, [Employee::factory()->create()->id]);

        $outsider = Employee::factory()->create();

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->postJson("/absen/{$agenda->id}/sign", [
                'employee_id' => $outsider->id,
                'signature' => UploadedFile::fake()->image('signature.png', 300, 100),
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('agenda_employee', [
            'agenda_id' => $agenda->id,
            'employee_id' => $outsider->id,
        ]);
    }

    public function test_member_can_sign_public_attendance_for_roster_agenda(): void
    {
        Storage::fake('public');

        $member = Employee::factory()->create();
        $agenda = $this->createRapat('internal', true, [$member->id]);

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->postJson("/absen/{$agenda->id}/sign", [
                'employee_id' => $member->id,
                'signature' => UploadedFile::fake()->image('signature.png', 300, 100),
            ]);

        $response->assertOk();
        $response->assertJson(['message' => 'Absensi berhasil disimpan.']);
    }

    public function test_admin_can_update_notulensi_from_agenda_detail(): void
    {
        $user = User::factory()->create();
        $agenda = $this->createRapat('internal');

        $note = $agenda->notes()->create([
            'topic' => 'Topik Lama',
            'decision' => 'Keputusan Lama',
            'remarks' => 'Catatan lama',
            'pj' => 'PJ Lama',
        ]);

        $response = $this->actingAs($user)->put(
            route('admin.agendas.notes.update', [$agenda, $note]),
            [
                'topic' => 'Topik Baru',
                'decision' => 'Keputusan Baru',
                'remarks' => 'Catatan baru',
                'pj' => 'PJ Baru',
            ],
        );

        $response->assertRedirect(route('admin.agendas.show', $agenda));
        $this->assertDatabaseHas('agenda_notes', [
            'id' => $note->id,
            'topic' => 'Topik Baru',
            'decision' => 'Keputusan Baru',
            'pj' => 'PJ Baru',
        ]);
    }
}
