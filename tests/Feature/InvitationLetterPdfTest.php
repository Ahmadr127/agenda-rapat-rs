<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Services\InvitationLetterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationLetterPdfTest extends TestCase
{
    use RefreshDatabase;

    private function makeRapat(): Agenda
    {
        return Agenda::factory()->create([
            'title' => 'Assessment RS Pasutri',
            'event_date' => '2026-10-06',
            'event_time' => '10:00',
            'event_end_time' => '15:00',
            'type' => 'rapat',
            'letter_number' => '925/RSAZRA/X/2026',
        ]);
    }

    public function test_pdf_template_uses_print_safe_table_layout(): void
    {
        $agenda = $this->makeRapat();
        $service = app(InvitationLetterService::class);

        $html = view('admin.agendas.invitation-pdf', $service->letterData($agenda))->render();

        // Kertas A4 portrait + margin seimbang 2,5 cm kiri-kanan.
        $this->assertMatchesRegularExpression('/@page\s*\{[^}]*A4[^}]*\}/i', $html);
        $this->assertMatchesRegularExpression('/@page\s*\{[^}]*25mm[^}]*\}/', $html);

        // Tipografi dokumen resmi.
        $this->assertStringContainsString('font-size: 11pt', $html);

        // Struktur tabel tanpa flexbox/grid/gap.
        $this->assertStringContainsString('<table', $html);
        $this->assertDoesNotMatchRegularExpression('/display\s*:\s*(flex|grid)/i', $html);
        $this->assertDoesNotMatchRegularExpression('/\bgap\s*:/i', $html);

        // Tanpa spasi manual untuk kolom.
        $this->assertStringNotContainsString('&nbsp;', $html);

        // Seluruh data dinamis ter-render.
        $this->assertStringContainsString('925/RSAZRA/X/2026', $html);
        $this->assertStringContainsString('Undangan', $html);
        $this->assertStringContainsString('Direktur Rumah Sakit', $html);
        $this->assertStringContainsString('Oktober 2026', $html);
        $this->assertStringContainsString('10.00 s.d 15.00 WIB', $html);
        $this->assertStringContainsString('Assessment RS Pasutri', $html);
        $this->assertStringContainsString($agenda->room->room_name, $html);
        $this->assertStringContainsString($agenda->eventLeader->full_name, $html);
        $this->assertStringContainsString('Tembusan', $html);
        $this->assertStringContainsString('Arsip', $html);
    }

    public function test_service_generates_single_page_pdf(): void
    {
        $agenda = $this->makeRapat();

        $pdf = app(InvitationLetterService::class)->toPdf($agenda)->output();

        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertGreaterThan(5000, strlen($pdf));

        // Tepat 1 halaman: node /Page dikurangi node katalog /Pages.
        $pages = substr_count($pdf, '/Type /Page') - substr_count($pdf, '/Type /Pages');
        $this->assertSame(1, $pages);
    }

    public function test_rendered_pdf_has_balanced_margins_indents_and_full_bleed_header(): void
    {
        $agenda = $this->makeRapat();

        // Render tanpa kompresi agar operator posisi teks bisa dibaca langsung.
        $raw = app(InvitationLetterService::class)->toPdf($agenda)->output(['compress' => 0]);

        preg_match_all(
            '/BT\s+([\d.]+)\s+([\d.]+)\s+Td\s+\/\S+\s+[\d.]+\s+Tf\s+\[(.*?)\] TJ/',
            $raw,
            $matches,
            PREG_SET_ORDER
        );
        $this->assertNotEmpty($matches, 'Tidak ada baris teks pada PDF.');

        $xOf = function (string $needle) use ($matches): ?float {
            foreach ($matches as $row) {
                if (str_contains($row[3], $needle)) {
                    return (float) $row[1];
                }
            }

            return null;
        };

        // Margin kiri 25mm ≈ 70.9pt.
        $this->assertEqualsWithDelta(70.9, $xOf('Kepada Yth.'), 1.0);
        // Indent penerima 18pt ≈ 88.9pt.
        $this->assertEqualsWithDelta(88.9, $xOf('Direktur Rumah Sakit'), 1.0);
        // Kop full-bleed: gambar header di-draw sebagai XObject image.
        $this->assertMatchesRegularExpression('/\/I\d+ Do/', $raw);
    }
}
