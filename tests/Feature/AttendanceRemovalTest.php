<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\AgendaQuestion;
use App\Models\AgendaQuestionAnswer;
use App\Models\Employee;
use App\Models\Room;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceRemovalTest extends TestCase
{
    use RefreshDatabase;

    private function createAgendaWithSignedEmployee(string $eventDate): array
    {
        Storage::fake('public');

        $unit = Unit::factory()->create();
        $room = Room::factory()->create();
        $leader = Employee::factory()->create(['unit_id' => $unit->id]);
        $employee = Employee::factory()->create(['unit_id' => $unit->id]);

        $agenda = Agenda::factory()->create([
            'type' => 'rapat',
            'event_date' => $eventDate,
            'event_time' => '10:00',
            'unit_id' => $unit->id,
            'event_leader_id' => $leader->id,
            'room_id' => $room->id,
        ]);

        Storage::disk('public')->put('signatures/hadir.png', 'fake-image');
        $agenda->employees()->attach($employee->id, [
            'signature_image_path' => 'signatures/hadir.png',
        ]);

        $question = AgendaQuestion::factory()->create(['agenda_id' => $agenda->id]);
        AgendaQuestionAnswer::create([
            'agenda_id' => $agenda->id,
            'employee_id' => $employee->id,
            'agenda_question_id' => $question->id,
            'selected_option' => 'a',
            'is_correct' => true,
            'quiz_type' => 'pretest',
        ]);

        return [$agenda, $employee];
    }

    public function test_admin_can_remove_attendance_before_agenda_finished(): void
    {
        $user = User::factory()->create();
        [$agenda, $employee] = $this->createAgendaWithSignedEmployee(now()->toDateString());

        $response = $this->actingAs($user)->delete(
            route('admin.agendas.attendees.destroy', [$agenda, $employee])
        );

        $response->assertRedirect(route('admin.agendas.show', $agenda));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('agenda_employee', [
            'agenda_id' => $agenda->id,
            'employee_id' => $employee->id,
        ]);
        Storage::disk('public')->assertMissing('signatures/hadir.png');
        $this->assertDatabaseMissing('agenda_question_answers', [
            'agenda_id' => $agenda->id,
            'employee_id' => $employee->id,
        ]);
    }

    public function test_admin_cannot_remove_attendance_after_agenda_finished(): void
    {
        $user = User::factory()->create();
        [$agenda, $employee] = $this->createAgendaWithSignedEmployee(
            now()->subDay()->toDateString()
        );

        $response = $this->actingAs($user)->delete(
            route('admin.agendas.attendees.destroy', [$agenda, $employee])
        );

        $response->assertForbidden();
        $this->assertDatabaseHas('agenda_employee', [
            'agenda_id' => $agenda->id,
            'employee_id' => $employee->id,
        ]);
    }

    public function test_public_can_unsign_on_event_day(): void
    {
        [$agenda, $employee] = $this->createAgendaWithSignedEmployee(now()->toDateString());

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->deleteJson("/absen/{$agenda->id}/sign", ['employee_id' => $employee->id]);

        $response->assertOk();
        $response->assertJson(['message' => 'Kehadiran berhasil dihapus.']);
        $this->assertDatabaseMissing('agenda_employee', [
            'agenda_id' => $agenda->id,
            'employee_id' => $employee->id,
        ]);
    }

    public function test_public_cannot_unsign_after_event_day(): void
    {
        [$agenda, $employee] = $this->createAgendaWithSignedEmployee(
            now()->subDay()->toDateString()
        );

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->deleteJson("/absen/{$agenda->id}/sign", ['employee_id' => $employee->id]);

        $response->assertNotFound();
        $this->assertDatabaseHas('agenda_employee', [
            'agenda_id' => $agenda->id,
            'employee_id' => $employee->id,
        ]);
    }

    public function test_unsign_unknown_attendance_returns_error(): void
    {
        [$agenda] = $this->createAgendaWithSignedEmployee(now()->toDateString());
        $outsider = Employee::factory()->create();

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->deleteJson("/absen/{$agenda->id}/sign", ['employee_id' => $outsider->id]);

        $response->assertStatus(422);
    }

    public function test_admin_cannot_remove_attendance_after_end_hour_today(): void
    {
        $user = User::factory()->create();
        [$agenda, $employee] = $this->createAgendaWithSignedEmployee(now()->toDateString());
        $agenda->update([
            'event_time' => now()->subHours(2)->format('H:i'),
            'event_end_time' => now()->subHour()->format('H:i'),
        ]);

        $response = $this->actingAs($user)->delete(
            route('admin.agendas.attendees.destroy', [$agenda, $employee])
        );

        $response->assertForbidden();
        $this->assertDatabaseHas('agenda_employee', [
            'agenda_id' => $agenda->id,
            'employee_id' => $employee->id,
        ]);
    }

    public function test_admin_can_remove_attendance_before_end_hour_today(): void
    {
        $user = User::factory()->create();
        [$agenda, $employee] = $this->createAgendaWithSignedEmployee(now()->toDateString());
        $agenda->update([
            'event_time' => now()->subHour()->format('H:i'),
            'event_end_time' => now()->addHour()->format('H:i'),
        ]);

        $response = $this->actingAs($user)->delete(
            route('admin.agendas.attendees.destroy', [$agenda, $employee])
        );

        $response->assertRedirect(route('admin.agendas.show', $agenda));
        $this->assertDatabaseMissing('agenda_employee', [
            'agenda_id' => $agenda->id,
            'employee_id' => $employee->id,
        ]);
    }

    public function test_public_cannot_unsign_after_end_hour_today(): void
    {
        [$agenda, $employee] = $this->createAgendaWithSignedEmployee(now()->toDateString());
        $agenda->update([
            'event_time' => now()->subHours(2)->format('H:i'),
            'event_end_time' => now()->subHour()->format('H:i'),
        ]);

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->deleteJson("/absen/{$agenda->id}/sign", ['employee_id' => $employee->id]);

        $response->assertForbidden();
        $this->assertDatabaseHas('agenda_employee', [
            'agenda_id' => $agenda->id,
            'employee_id' => $employee->id,
        ]);
    }

    public function test_attendance_page_marks_locked_after_end_hour(): void
    {
        [$agenda] = $this->createAgendaWithSignedEmployee(now()->toDateString());
        $agenda->update([
            'event_time' => now()->subHours(2)->format('H:i'),
            'event_end_time' => now()->subHour()->format('H:i'),
        ]);

        $this->get("/absen/{$agenda->id}")
            ->assertOk()
            ->assertSee('attendanceLocked: true', false);
    }

    public function test_attendance_page_marks_open_before_end_hour(): void
    {
        [$agenda] = $this->createAgendaWithSignedEmployee(now()->toDateString());
        $agenda->update([
            'event_time' => now()->subHour()->format('H:i'),
            'event_end_time' => now()->addHour()->format('H:i'),
        ]);

        $this->get("/absen/{$agenda->id}")
            ->assertOk()
            ->assertSee('attendanceLocked: false', false);
    }
}
