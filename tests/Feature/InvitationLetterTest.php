<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\BankSoal;
use App\Models\Employee;
use App\Models\Room;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationLetterTest extends TestCase
{
    use RefreshDatabase;

    public function test_rapat_can_preview_invitation(): void
    {
        $user = User::factory()->create();
        $agenda = Agenda::factory()->create(['type' => 'rapat']);

        $this->actingAs($user)
            ->get(route('admin.agendas.invitation-preview', $agenda))
            ->assertOk()
            ->assertSee('Undangan', false);
    }

    public function test_rapat_can_download_invitation_pdf(): void
    {
        $user = User::factory()->create();
        $agenda = Agenda::factory()->create([
            'type' => 'rapat',
            'letter_number' => '925/RSAZRA/X/2026',
        ]);

        $this->actingAs($user)
            ->get(route('admin.agendas.invitation-letter', ['agenda' => $agenda->id, 'download' => 1]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_non_rapat_cannot_preview_or_download_invitation(): void
    {
        $user = User::factory()->create();

        foreach (['diklat', 'pelatihan'] as $type) {
            $agenda = Agenda::factory()->create(['type' => $type]);

            $this->actingAs($user)
                ->get(route('admin.agendas.invitation-preview', $agenda))
                ->assertNotFound();
            $this->actingAs($user)
                ->get(route('admin.agendas.invitation-letter', $agenda))
                ->assertNotFound();
        }
    }

    public function test_store_non_rapat_ignores_letter_number(): void
    {
        $user = User::factory()->create();
        $bankSoal = BankSoal::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.agendas.store'), [
            'title' => 'Diklat Kompetensi',
            'event_date' => '2026-10-20',
            'event_time' => '09:00',
            'event_end_time' => '12:00',
            'unit_id' => Unit::factory()->create()->id,
            'event_leader_id' => Employee::factory()->create()->id,
            'room_id' => Room::factory()->create()->id,
            'type' => 'diklat',
            'bank_soal_id' => $bankSoal->id,
            'letter_number' => '999/RSAZRA/X/2026',
        ]);

        $response->assertRedirect(route('admin.agendas.index'));
        $this->assertNull(Agenda::first()->letter_number);
    }

    public function test_store_rapat_auto_fills_letter_number_when_empty(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.agendas.store'), [
            'title' => 'Rapat Koordinasi',
            'event_date' => '2026-10-20',
            'event_time' => '10:00',
            'unit_id' => Unit::factory()->create()->id,
            'event_leader_id' => Employee::factory()->create()->id,
            'room_id' => Room::factory()->create()->id,
            'type' => 'rapat',
        ]);

        $response->assertRedirect(route('admin.agendas.index'));
        $this->assertMatchesRegularExpression(
            '#^\d{3}/RSAZRA/(I|II|III|IV|V|VI|VII|VIII|IX|X|XI|XII)/\d{4}$#',
            Agenda::first()->letter_number,
        );
    }

    public function test_export_pdf_non_rapat_still_works_without_invitation(): void
    {
        $user = User::factory()->create();
        $agenda = Agenda::factory()->create(['type' => 'diklat']);

        $this->actingAs($user)
            ->get(route('admin.agendas.export-pdf', $agenda))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_store_rapat_saves_custom_recipients(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.agendas.store'), [
            'title' => 'Rapat Koordinasi',
            'event_date' => '2026-10-20',
            'event_time' => '10:00',
            'unit_id' => Unit::factory()->create()->id,
            'event_leader_id' => Employee::factory()->create()->id,
            'room_id' => Room::factory()->create()->id,
            'type' => 'rapat',
            'letter_recipients' => "Kepala Dinas Kesehatan\nPuskesmas Bogor Utara",
        ]);

        $response->assertRedirect(route('admin.agendas.index'));
        $this->assertSame(
            "Kepala Dinas Kesehatan\nPuskesmas Bogor Utara",
            Agenda::first()->letter_recipients
        );
    }

    public function test_store_non_rapat_ignores_custom_recipients(): void
    {
        $user = User::factory()->create();
        $bankSoal = BankSoal::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.agendas.store'), [
            'title' => 'Diklat Kompetensi',
            'event_date' => '2026-10-20',
            'event_time' => '09:00',
            'event_end_time' => '12:00',
            'unit_id' => Unit::factory()->create()->id,
            'event_leader_id' => Employee::factory()->create()->id,
            'room_id' => Room::factory()->create()->id,
            'type' => 'diklat',
            'bank_soal_id' => $bankSoal->id,
            'letter_recipients' => 'Siapa pun',
        ]);

        $response->assertRedirect(route('admin.agendas.index'));
        $this->assertNull(Agenda::first()->letter_recipients);
    }

    public function test_preview_uses_custom_recipients(): void
    {
        $user = User::factory()->create();
        $agenda = Agenda::factory()->create([
            'type' => 'rapat',
            'letter_recipients' => "Kepala Dinas Kesehatan\nPuskesmas Bogor Utara",
        ]);

        $this->actingAs($user)
            ->get(route('admin.agendas.invitation-preview', $agenda))
            ->assertOk()
            ->assertSee('Kepala Dinas Kesehatan', false)
            ->assertDontSee('Manager GA', false);
    }
}
