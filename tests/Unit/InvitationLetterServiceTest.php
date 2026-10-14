<?php

namespace Tests\Unit;

use App\Models\Agenda;
use App\Models\Employee;
use App\Models\User;
use App\Services\InvitationLetterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvitationLetterServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_suggest_number_follows_sequence_code_roman_year_format(): void
    {
        $service = new InvitationLetterService;

        $number = $service->suggestNumber(now().'');

        $this->assertMatchesRegularExpression(
            '#^\d{3}/RSAZRA/(I|II|III|IV|V|VI|VII|VIII|IX|X|XI|XII)/\d{4}$#',
            $number
        );
    }

    public function test_roman_month_october_is_x(): void
    {
        $this->assertSame('X', (new InvitationLetterService)->romanMonth(10));
    }

    public function test_letter_data_uses_dot_time_format_and_leader_signature_fallback(): void
    {
        $agenda = Agenda::factory()->create([
            'title' => 'Assessment RS Pasutri',
            'event_date' => '2026-10-06',
            'event_time' => '10:00',
            'event_end_time' => '15:00',
            'letter_number' => '925/RSAZRA/X/2026',
        ]);
        $agenda->load(['room', 'unit', 'eventLeader', 'employees']);

        $data = (new InvitationLetterService)->letterData($agenda);

        $this->assertSame('925/RSAZRA/X/2026', $data['letterNumber']);
        $this->assertSame('10.00 s.d 15.00 WIB', $data['timeRange']);
        $this->assertStringContainsString('Bogor, 6 Oktober 2026', $data['letterPlaceDate']);
        $this->assertSame('Assessment RS Pasutri', $data['agendaTitle']);
        // Input kosong → tanpa baris penerima.
        $this->assertSame([], $data['recipients']);
        $this->assertSame(['Arsip'], $data['tembusan']);
    }

    public function test_recipients_stays_empty_despite_attendees_when_input_empty(): void
    {
        $agenda = Agenda::factory()->create([
            'event_date' => '2026-10-06',
            'event_time' => '10:00',
            'letter_recipients' => null,
        ]);
        $agenda->employees()->attach($agenda->eventLeader->id);
        $agenda->load(['room', 'unit', 'eventLeader', 'employees']);

        $data = (new InvitationLetterService)->letterData($agenda);

        // Peserta hadir bukan undangan: tetap kosong.
        $this->assertSame([], $data['recipients']);
    }

    public function test_custom_recipients_text_beats_defaults_even_with_attendees(): void
    {
        $agenda = Agenda::factory()->create([
            'event_date' => '2026-10-06',
            'event_time' => '10:00',
            'letter_recipients' => "Kepala Dinas Kesehatan\n\n  Puskesmas Bogor Utara  \r\n",
        ]);
        $agenda->employees()->attach($agenda->eventLeader->id);
        $agenda->load(['room', 'unit', 'eventLeader', 'employees']);

        $data = (new InvitationLetterService)->letterData($agenda);

        $this->assertSame(['Kepala Dinas Kesehatan', 'Puskesmas Bogor Utara'], $data['recipients']);
    }

    public function test_parse_recipients_text_trims_and_skips_empty_lines(): void
    {
        $parsed = (new InvitationLetterService)->parseRecipientsText(" A\r\n\r\nB \n");

        $this->assertSame(['A', 'B'], $parsed);
        $this->assertSame([], (new InvitationLetterService)->parseRecipientsText(null));
    }

    public function test_leader_signature_falls_back_to_linked_user_signature(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('user-signatures/direktur.png', 'fake-png-bytes');

        $user = User::factory()->create(['signature_path' => 'user-signatures/direktur.png']);
        $leader = Employee::factory()->create(['user_id' => $user->id, 'signature_path' => null]);
        $agenda = Agenda::factory()->create([
            'event_date' => '2026-10-06',
            'event_time' => '10:00',
            'event_leader_id' => $leader->id,
        ]);
        $agenda->load(['room', 'unit', 'eventLeader', 'employees']);

        $data = (new InvitationLetterService)->letterData($agenda);

        $this->assertStringStartsWith('data:', $data['signatureDataUri']);
        $this->assertStringContainsString('base64,', $data['signatureDataUri']);
    }
}
