<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\Employee;
use App\Models\Room;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CsvExportSanitizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_agenda_export_csv_sanitizes_multiline_description(): void
    {
        $user = User::factory()->create();
        $unit = Unit::factory()->create(['name' => 'Unit Export']);
        $room = Room::factory()->create(['room_name' => 'Ruang Export']);
        $organizer = Employee::factory()->create([
            'unit_id' => $unit->id,
            'full_name' => 'Penyelenggara Export',
        ]);

        Agenda::factory()->create([
            'title' => 'Agenda Sanitasi',
            'description' => "Baris satu\r\nBaris dua\n\nBaris tiga",
            'room_id' => $room->id,
            'unit_id' => $unit->id,
            'event_leader_id' => $organizer->id,
            'event_date' => '2026-04-15',
            'event_time' => '08:00',
            'event_end_time' => '10:00',
        ]);

        $response = $this->actingAs($user)->get(route('admin.agendas.export-csv'));

        $response->assertOk();
        $csv = $response->streamedContent();

        $this->assertStringContainsString('Baris satu Baris dua Baris tiga', $csv);
        $this->assertStringNotContainsString("Baris satu\r\nBaris dua\n\nBaris tiga", $csv);
    }

    public function test_employee_agendas_export_csv_sanitizes_multiline_description(): void
    {
        $user = User::factory()->create();
        $unit = Unit::factory()->create(['name' => 'Unit Peserta']);
        $room = Room::factory()->create(['room_name' => 'Ruang Peserta']);
        $organizer = Employee::factory()->create([
            'unit_id' => $unit->id,
            'full_name' => 'Penyelenggara Peserta',
        ]);
        $employee = Employee::factory()->create([
            'unit_id' => $unit->id,
            'full_name' => 'Peserta Export',
        ]);

        $agenda = Agenda::factory()->create([
            'title' => 'Agenda Peserta',
            'description' => "Topik awal\nTopik lanjutan\r\nKesimpulan",
            'room_id' => $room->id,
            'unit_id' => $unit->id,
            'event_leader_id' => $organizer->id,
            'event_date' => '2026-04-16',
            'event_time' => '09:00',
            'event_end_time' => '11:00',
        ]);

        $agenda->employees()->attach($employee->id, ['signature_image_path' => 'signatures/peserta.png']);

        $response = $this->actingAs($user)->get(route('admin.employee-recaps.agendas.export-csv', [
            'employee' => $employee,
        ]));

        $response->assertOk();
        $csv = $response->streamedContent();

        $this->assertStringContainsString('Topik awal Topik lanjutan Kesimpulan', $csv);
        $this->assertStringNotContainsString("Topik awal\nTopik lanjutan\r\nKesimpulan", $csv);
        $this->assertStringContainsString('Agenda Peserta', $csv);
    }
}
