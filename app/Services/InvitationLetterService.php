<?php

namespace App\Services;

use App\Models\Agenda;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Layered service untuk surat undangan otomatis.
 *
 * Tanggung jawab lapis ini:
 * - Penomoran surat (auto-fill, tetap bisa diedit di form).
 * - Penyusunan data surat dari relasi Agenda (tanpa upload manual).
 * - Render PDF formal (kop header + footer RS AZRA).
 *
 * View lapis presentasi:
 * - resources/views/admin/agendas/invitation-pdf.blade.php (wrapper PDF)
 * - resources/views/admin/agendas/partials/_invitation-letter-body.blade.php (isi surat)
 */
class InvitationLetterService
{
    public const LETTER_CODE = 'RSAZRA';

    public const LETTER_CITY = 'Bogor';

    /** Daftar "Kepada Yth." bawaan bila agenda belum punya peserta terdaftar. */
    public const DEFAULT_RECIPIENTS = [
        'Direktur Rumah Sakit',
        'Manager Pelayanan Medis',
        'Manager Penunjang Medis',
        'Manager Keperawatan',
        'Manager SDM',
        'Manager Marketing',
        'Manager Akuntansi, Procurement, dan Pajak',
        'Manager IT',
        'Manager GA',
    ];

    public const DEFAULT_TEMBUSAN = ['Arsip'];

    /**
     * Usulan nomor surat otomatis: {urut}/RSAZRA/{romawi}/{tahun}.
     * Urutan dihitung dari jumlah agenda pada bulan yang sama + 1.
     * Hasilnya hanya usulan — form tetap mengizinkan edit manual.
     */
    public function suggestNumber(CarbonInterface|string|null $date = null): string
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();

        $count = Agenda::whereYear('event_date', $date->year)
            ->whereMonth('event_date', $date->month)
            ->count();

        $sequence = str_pad((string) ($count + 1), 3, '0', STR_PAD_LEFT);

        return sprintf(
            '%s/%s/%s/%s',
            $sequence,
            self::LETTER_CODE,
            $this->romanMonth((int) $date->format('n')),
            $date->format('Y'),
        );
    }

    public function romanMonth(int $month): string
    {
        return [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ][$month] ?? 'I';
    }

    /**
     * Susun seluruh data yang dibutuhkan partial surat.
     */
    public function letterData(Agenda $agenda): array
    {
        $agenda->loadMissing(['room', 'unit', 'eventLeader', 'employees.unit']);

        $eventDate = $agenda->event_date instanceof CarbonInterface
            ? Carbon::parse($agenda->event_date)
            : Carbon::parse((string) $agenda->event_date);
        $eventDate = $eventDate->locale('id');

        $leader = $agenda->eventLeader;

        return [
            'agenda' => $agenda,
            'city' => self::LETTER_CITY,
            // "Bogor, 6 Oktober 2026"
            'letterPlaceDate' => self::LETTER_CITY.', '.$eventDate->translatedFormat('j F Y'),
            'letterNumber' => $agenda->letter_number ?: $this->suggestNumber($eventDate),
            // "Selasa, 6 Oktober 2026"
            'eventDayDate' => $eventDate->translatedFormat('l, j F Y'),
            'timeRange' => $this->timeRange($agenda),
            'location' => $agenda->room?->room_name ?? '-',
            'agendaTitle' => $agenda->title,
            'recipients' => $this->recipients($agenda),
            'tembusan' => self::DEFAULT_TEMBUSAN,
            'leaderName' => $leader?->full_name ?? '-',
            'leaderTitle' => $leader?->job_position ?: ($leader?->structural_role ?: 'Direktur Rumah Sakit AZRA'),
            'signatureDataUri' => $this->leaderSignature($agenda),
            'headerDataUri' => $this->publicImageDataUri('images/surat/header.png'),
            'footerDataUri' => $this->publicImageDataUri('images/surat/footer.png'),
        ];
    }

    /** "10.00 s.d 15.00 WIB" / "10.00 WIB" — format Indonesia memakai titik. */
    public function timeRange(Agenda $agenda): string
    {
        $start = $agenda->event_time ? $this->formatTimeId((string) $agenda->event_time) : '-';
        $end = $agenda->event_end_time ? $this->formatTimeId((string) $agenda->event_end_time) : null;

        return $end ? "{$start} s.d {$end} WIB" : "{$start} WIB";
    }

    public function formatTimeId(string $time): string
    {
        try {
            return Carbon::parse($time)->format('H.i');
        } catch (\Throwable) {
            return $time;
        }
    }

    /**
     * Daftar "Kepada Yth.": peserta terdaftar (nama + jabatan) bila sudah ada,
     * jika tidak daftar jabatan bawaan RS AZRA.
     *
     * @return array<int, string>
     */
    public function recipients(Agenda $agenda): array
    {
        if ($agenda->relationLoaded('employees') && $agenda->employees->count() > 0) {
            return $agenda->employees
                ->sortBy('full_name')
                ->map(fn ($e) => trim($e->full_name.($e->job_position ? ' — '.$e->job_position : '')))
                ->values()
                ->all();
        }

        return self::DEFAULT_RECIPIENTS;
    }

    /**
     * TTD pimpinan: tanda tangan resmi pegawai (employees.signature_path)
     * bila ada, jika tidak tanda tangan kehadiran pada agenda ini.
     */
    public function leaderSignature(Agenda $agenda): ?string
    {
        $leader = $agenda->eventLeader;

        if ($leader?->signature_path) {
            $uri = $this->storageDataUri($leader->signature_path);
            if ($uri) {
                return $uri;
            }
        }

        if ($leader && $agenda->relationLoaded('employees')) {
            $pivot = $agenda->employees->firstWhere('id', $leader->id);
            if ($pivot?->pivot?->signature_image_path) {
                return $this->storageDataUri($pivot->pivot->signature_image_path);
            }
        }

        return null;
    }

    public function toPdf(Agenda $agenda)
    {
        return Pdf::loadView('admin.agendas.invitation-pdf', $this->letterData($agenda))
            ->setPaper('a4', 'portrait');
    }

    private function storageDataUri(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        try {
            if (! Storage::disk('public')->exists($path)) {
                return null;
            }

            $absolute = Storage::disk('public')->path($path);
            if (! is_file($absolute)) {
                return null;
            }

            $mime = mime_content_type($absolute) ?: 'image/png';

            return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($absolute));
        } catch (\Throwable) {
            return null;
        }
    }

    private function publicImageDataUri(string $relative): ?string
    {
        $absolute = public_path($relative);

        if (! is_file($absolute)) {
            return null;
        }

        $ext = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));
        $mime = $ext === 'jpg' || $ext === 'jpeg' ? 'image/jpeg' : 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($absolute));
    }
}
