<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\Employee;
use App\Models\Room;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    private function createActiveAgendaWithEmployee(): array
    {
        $room = Room::create(['room_name' => 'Test Room']);
        $unit = Unit::create(['name' => 'RS AZRA']);

        $organizer = Employee::create([
            'nip' => '000000000000000001',
            'full_name' => 'Organizer',
            'unit_id' => $unit->id,
            'job_position' => 'Direktur',
            'structural_role' => 'Kepala',
            'profession' => 'Tenaga Medis',
        ]);

        $agenda = Agenda::create([
            'title' => 'Test Agenda',
            'event_date' => now()->toDateString(),
            'event_time' => '10:00',
            'unit_id' => $unit->id,
            'event_leader_id' => $organizer->id,
            'room_id' => $room->id,
        ]);

        $employee = Employee::create([
            'nip' => '123456789012345678',
            'full_name' => 'Test Employee',
            'unit_id' => $unit->id,
            'job_position' => 'Dokter',
            'structural_role' => 'Staf',
            'profession' => 'Tenaga Medis',
        ]);

        $agenda->employees()->attach($employee->id);

        return [$agenda, $employee];
    }

    public function test_prevents_double_attendance(): void
    {
        [$agenda, $employee] = $this->createActiveAgendaWithEmployee();

        $agenda->employees()->updateExistingPivot($employee->id, [
            'signature_image_path' => 'signatures/existing.png',
        ]);

        $response = $this->withHeaders(['Accept' => 'application/json'])->post("/absen/{$agenda->id}/sign", [
            'employee_id' => $employee->id,
            'signature' => UploadedFile::fake()->image('signature.png', 300, 100),
        ]);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Anda sudah melakukan absensi.']);
    }

    public function test_allows_first_attendance(): void
    {
        Storage::fake('public');

        [$agenda, $employee] = $this->createActiveAgendaWithEmployee();

        $response = $this->withHeaders(['Accept' => 'application/json'])->post("/absen/{$agenda->id}/sign", [
            'employee_id' => $employee->id,
            'signature' => UploadedFile::fake()->image('signature.png', 300, 100),
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Absensi berhasil disimpan.']);

        $path = $agenda->employees()->where('employee_id', $employee->id)->first()->pivot->signature_image_path;
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_allows_walk_in_attendance(): void
    {
        Storage::fake('public');

        [$agenda] = $this->createActiveAgendaWithEmployee();

        $outsiderUnit = Unit::create(['name' => 'Other']);

        $outsider = Employee::create([
            'nip' => '999999999999999999',
            'full_name' => 'Outsider',
            'unit_id' => $outsiderUnit->id,
            'job_position' => 'Other',
            'structural_role' => 'Other',
            'profession' => 'Other',
        ]);

        $response = $this->withHeaders(['Accept' => 'application/json'])->post("/absen/{$agenda->id}/sign", [
            'employee_id' => $outsider->id,
            'signature' => UploadedFile::fake()->image('signature.png', 300, 100),
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Absensi berhasil disimpan.']);

        $this->assertNotNull(
            $agenda->employees()->where('employee_id', $outsider->id)->first()->pivot->signature_image_path
        );
    }
}
