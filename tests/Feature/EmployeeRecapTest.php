<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\Employee;
use App\Models\Room;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeRecapTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_recap_page_shows_attendance_count_and_total_hours(): void
    {
        $user = User::factory()->create();
        $unit = Unit::factory()->create(['name' => 'Unit Rekap']);
        $room = Room::factory()->create();
        $organizer = Employee::factory()->create(['unit_id' => $unit->id]);
        $employee = Employee::factory()->create([
            'unit_id' => $unit->id,
            'full_name' => 'Andi Rekap',
        ]);

        $agendaOne = $this->createAgenda($room->id, $organizer->id, '2026-04-10', '08:00', '10:00');
        $agendaTwo = $this->createAgenda($room->id, $organizer->id, '2026-04-11', '13:00', '14:30');
        $agendaUnsigned = $this->createAgenda($room->id, $organizer->id, '2026-04-12', '09:00', '11:00');

        $agendaOne->employees()->attach($employee->id, ['signature_image_path' => 'signatures/a.png']);
        $agendaTwo->employees()->attach($employee->id, ['signature_image_path' => 'signatures/b.png']);
        $agendaUnsigned->employees()->attach($employee->id, ['signature_image_path' => null]);

        $response = $this->actingAs($user)->get(route('admin.employee-recaps.index'));

        $response->assertOk();
        $response->assertSee('Andi Rekap');
        $response->assertSee('2 kali');
        $response->assertSee('3,50 jam');
    }

    public function test_employee_recap_export_csv_respects_unit_and_date_filters(): void
    {
        $user = User::factory()->create();
        $unitA = Unit::factory()->create(['name' => 'Unit A']);
        $unitB = Unit::factory()->create(['name' => 'Unit B']);
        $room = Room::factory()->create();
        $organizer = Employee::factory()->create(['unit_id' => $unitA->id]);

        $employeeA = Employee::factory()->create([
            'unit_id' => $unitA->id,
            'full_name' => 'Budi Filter',
            'nip' => '100000000000000001',
        ]);
        $employeeB = Employee::factory()->create([
            'unit_id' => $unitB->id,
            'full_name' => 'Cici Lain',
            'nip' => '100000000000000002',
        ]);

        $oldAgenda = $this->createAgenda($room->id, $organizer->id, '2026-04-10', '08:00', '10:00');
        $filteredAgenda = $this->createAgenda($room->id, $organizer->id, '2026-04-13', '09:00', '10:00');
        $otherUnitAgenda = $this->createAgenda($room->id, $organizer->id, '2026-04-13', '09:00', '12:00');

        $oldAgenda->employees()->attach($employeeA->id, ['signature_image_path' => 'signatures/old.png']);
        $filteredAgenda->employees()->attach($employeeA->id, ['signature_image_path' => 'signatures/filter.png']);
        $otherUnitAgenda->employees()->attach($employeeB->id, ['signature_image_path' => 'signatures/other.png']);

        $response = $this->actingAs($user)->get(route('admin.employee-recaps.export-csv', [
            'unit_id' => $unitA->id,
            'date_from' => '2026-04-11',
            'date_to' => '2026-04-30',
        ]));

        $response->assertOk();
        $csv = $response->streamedContent();

        $this->assertStringContainsString('Budi Filter', $csv);
        $this->assertStringContainsString('Jam Rapat', $csv);
        $this->assertStringContainsString('Jam Diklat/Pelatihan', $csv);
        $this->assertStringContainsString('1.00', $csv);
    }

    private function createAgenda(
        int $roomId,
        int $organizerId,
        string $date,
        string $startTime,
        string $endTime
    ): Agenda {
        return Agenda::factory()->create([
            'room_id' => $roomId,
            'event_leader_id' => $organizerId,
            'event_date' => $date,
            'event_time' => $startTime,
            'event_end_time' => $endTime,
        ]);
    }
}
