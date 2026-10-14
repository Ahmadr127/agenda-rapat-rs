<?php

namespace Tests\Unit;

use App\Models\Agenda;
use App\Services\InvitationLetterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        // Belum ada peserta → daftar bawaan 9 jabatan.
        $this->assertCount(9, $data['recipients']);
        $this->assertContains('Direktur Rumah Sakit', $data['recipients']);
        $this->assertSame(['Arsip'], $data['tembusan']);
    }

    public function test_recipients_prefers_registered_employees_over_defaults(): void
    {
        $agenda = Agenda::factory()->create([
            'event_date' => '2026-10-06',
            'event_time' => '10:00',
        ]);
        $leader = $agenda->eventLeader;
        $agenda->employees()->attach($leader->id);
        $agenda->load(['room', 'unit', 'eventLeader', 'employees']);

        $data = (new InvitationLetterService)->letterData($agenda);

        $this->assertStringContainsString($leader->full_name, $data['recipients'][0]);
    }
}
