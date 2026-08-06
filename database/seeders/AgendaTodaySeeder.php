<?php

namespace Database\Seeders;

use App\Models\Agenda;
use App\Models\BankSoal;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AgendaTodaySeeder extends Seeder
{
    private string $today;

    public function __construct()
    {
        $this->today = now()->toDateString();
    }

    private array $letterFiles = [
        "Undangan Diklat Wajib PMKP dan PK 6 APRIL 2026.pdf",
        "Undangan Diklat wajib Compro, LH, IT 01 April 2026 (2).pdf",
        "Undangan Evaluasi Tenaga Magang.pdf",
    ];

    private array $materialFiles = ["materi1.pdf", "materi2.pdf"];

    public function run(): void
    {
        $bankSoalId = BankSoal::where(
            "title",
            "Template Bank Soal Perawat",
        )->value("id");

        $agendas = [
            // --- DIKLAT (4) ---
            [
                "type" => "diklat",
                "title" =>
                    "Diklat Wajib PMKP dan Peningkatan Kompetensi Klinis",
                "description" =>
                    "Diklat wajib seluruh staf klinis mengenai Peningkatan Mutu dan Keselamatan Pasien serta peningkatan kompetensi klinis sesuai standar akreditasi.",
                "event_date" => $this->today,
                "event_time" => "08:00",
                "event_end_time" => "16:00",
                "room_id" => 1,
                "unit_id" => 2,
                "event_leader_id" => 1,
                "bank_soal_id" => $bankSoalId,
                "letter_file" => $this->letterFiles[0],
                "material_file" => $this->materialFiles[0],
                "presenter_ids" => [8, 9],
            ],
            [
                "type" => "diklat",
                "title" => "Diklat Kompetensi Komputer, Legal & IT Rumah Sakit",
                "description" =>
                    "Peningkatan kompetensi staf di bidang penggunaan sistem informasi rumah sakit, aspek legal digital, dan keamanan data.",
                "event_date" => $this->today,
                "event_time" => "08:00",
                "event_end_time" => "15:00",
                "room_id" => 4,
                "unit_id" => 3,
                "event_leader_id" => 2,
                "bank_soal_id" => $bankSoalId,
                "letter_file" => $this->letterFiles[1],
                "material_file" => $this->materialFiles[1],
                "presenter_ids" => [7],
            ],
            [
                "type" => "diklat",
                "title" =>
                    "Diklat Keselamatan Pasien dan Manajemen Risiko Klinis",
                "description" =>
                    "Diklat untuk meningkatkan pemahaman dan penerapan budaya keselamatan pasien serta identifikasi dan mitigasi risiko klinis di lingkungan rumah sakit.",
                "event_date" => $this->today,
                "event_time" => "08:00",
                "event_end_time" => "14:00",
                "room_id" => 1,
                "unit_id" => 2,
                "event_leader_id" => 3,
                "bank_soal_id" => $bankSoalId,
                "letter_file" => $this->letterFiles[0],
                "material_file" => $this->materialFiles[0],
                "presenter_ids" => [10, 6],
            ],
            [
                "type" => "diklat",
                "title" => "Diklat Evaluasi dan Orientasi Tenaga Magang",
                "description" =>
                    "Program diklat khusus tenaga magang untuk mengenal standar pelayanan, prosedur operasional, dan budaya kerja rumah sakit.",
                "event_date" => $this->today,
                "event_time" => "09:00",
                "event_end_time" => "16:00",
                "room_id" => 2,
                "unit_id" => 1,
                "event_leader_id" => 4,
                "bank_soal_id" => $bankSoalId,
                "letter_file" => $this->letterFiles[2],
                "material_file" => $this->materialFiles[1],
                "presenter_ids" => [5],
            ],

            // --- PELATIHAN (3) ---
            [
                "type" => "pelatihan",
                "title" =>
                    "Pelatihan Basic Life Support (BLS) untuk Tenaga Kesehatan",
                "description" =>
                    "Pelatihan teknik pertolongan pertama pada kondisi henti jantung dan henti napas, meliputi RJP, penggunaan AED, dan manajemen jalan napas dasar.",
                "event_date" => $this->today,
                "event_time" => "08:00",
                "event_end_time" => "12:00",
                "room_id" => 1,
                "unit_id" => 5,
                "event_leader_id" => 5,
                "bank_soal_id" => $bankSoalId,
                "letter_file" => $this->letterFiles[0],
                "material_file" => $this->materialFiles[0],
                "presenter_ids" => [3, 8],
            ],
            [
                "type" => "pelatihan",
                "title" =>
                    "Pelatihan Pencegahan dan Pengendalian Infeksi (PPI)",
                "description" =>
                    "Pelatihan penerapan kewaspadaan standar, kebersihan tangan, penggunaan APD, dan pengelolaan limbah medis sesuai pedoman PPI nasional.",
                "event_date" => $this->today,
                "event_time" => "13:00",
                "event_end_time" => "17:00",
                "room_id" => 2,
                "unit_id" => 2,
                "event_leader_id" => 6,
                "bank_soal_id" => $bankSoalId,
                "letter_file" => $this->letterFiles[1],
                "material_file" => $this->materialFiles[1],
                "presenter_ids" => [9],
            ],
            [
                "type" => "pelatihan",
                "title" => "Pelatihan Manajemen Nyeri untuk Perawat dan Bidan",
                "description" =>
                    "Pelatihan asesmen nyeri menggunakan berbagai skala, penerapan intervensi farmakologis dan non-farmakologis, serta dokumentasi manajemen nyeri yang tepat.",
                "event_date" => $this->today,
                "event_time" => "08:00",
                "event_end_time" => "15:00",
                "room_id" => 3,
                "unit_id" => 1,
                "event_leader_id" => 1,
                "bank_soal_id" => $bankSoalId,
                "letter_file" => $this->letterFiles[2],
                "material_file" => $this->materialFiles[0],
                "presenter_ids" => [10, 7],
            ],

            // --- RAPAT (3) ---
            [
                "type" => "rapat",
                "title" => "Rapat Koordinasi Antar Unit Pelayanan",
                "description" =>
                    "Rapat koordinasi bulanan lintas unit untuk sinkronisasi program kerja, evaluasi capaian pelayanan, dan pembahasan kendala operasional.",
                "event_date" => $this->today,
                "event_time" => "08:00",
                "event_end_time" => null,
                "room_id" => 5,
                "unit_id" => 1,
                "event_leader_id" => 2,
                "bank_soal_id" => null,
                "letter_file" => $this->letterFiles[1],
                "material_file" => null,
                "presenter_ids" => [],
            ],
            [
                "type" => "rapat",
                "title" => "Rapat Evaluasi Program Kerja Triwulan I 2026",
                "description" =>
                    "Evaluasi capaian program kerja seluruh unit pada triwulan pertama tahun 2026, identifikasi gap, dan penyusunan rencana tindak lanjut.",
                "event_date" => $this->today,
                "event_time" => "10:00",
                "event_end_time" => null,
                "room_id" => 2,
                "unit_id" => 3,
                "event_leader_id" => 3,
                "bank_soal_id" => null,
                "letter_file" => $this->letterFiles[2],
                "material_file" => null,
                "presenter_ids" => [],
            ],
            [
                "type" => "rapat",
                "title" =>
                    "Rapat Pembahasan Revisi Standar Prosedur Operasional",
                "description" =>
                    "Pembahasan dan finalisasi revisi SPO pelayanan keperawatan yang disesuaikan dengan standar akreditasi terbaru dan kebutuhan operasional rumah sakit.",
                "event_date" => $this->today,
                "event_time" => "13:00",
                "event_end_time" => null,
                "room_id" => 3,
                "unit_id" => 4,
                "event_leader_id" => 4,
                "bank_soal_id" => null,
                "letter_file" => null,
                "material_file" => null,
                "presenter_ids" => [],
            ],
        ];

        foreach ($agendas as $data) {
            DB::transaction(function () use ($data) {
                $agenda = Agenda::create([
                    "type" => $data["type"],
                    "title" => $data["title"],
                    "description" => $data["description"],
                    "event_date" => $data["event_date"],
                    "event_time" => $data["event_time"],
                    "event_end_time" => $data["event_end_time"],
                    "room_id" => $data["room_id"],
                    "unit_id" => $data["unit_id"],
                    "event_leader_id" => $data["event_leader_id"],
                    "bank_soal_id" => $data["bank_soal_id"],
                ]);

                $fileUpdates = [];

                if ($data["letter_file"]) {
                    $src = public_path("templates/" . $data["letter_file"]);
                    if (file_exists($src)) {
                        $dest = "agenda-files/{$agenda->id}/letter.pdf";
                        Storage::disk("public")->put(
                            $dest,
                            file_get_contents($src),
                        );
                        $fileUpdates["letter_file_path"] = $dest;
                    }
                }

                if ($data["material_file"]) {
                    $src = public_path("templates/" . $data["material_file"]);
                    if (file_exists($src)) {
                        $dest = "agenda-files/{$agenda->id}/material.pdf";
                        Storage::disk("public")->put(
                            $dest,
                            file_get_contents($src),
                        );
                        $fileUpdates["material_file_path"] = $dest;
                    }
                }

                if ($fileUpdates) {
                    $agenda->update($fileUpdates);
                }

                if (!empty($data["presenter_ids"])) {
                    $syncData = collect($data["presenter_ids"])
                        ->mapWithKeys(
                            fn($id, $i) => [$id => ["sort_order" => $i + 1]],
                        )
                        ->all();
                    $agenda->presenters()->sync($syncData);
                }

                if (
                    $agenda->bank_soal_id &&
                    in_array($agenda->type, ["diklat", "pelatihan"])
                ) {
                    $questions = Question::where(
                        "bank_soal_id",
                        $agenda->bank_soal_id,
                    )->get();
                    $agenda
                        ->agendaQuestions()
                        ->createMany(
                            $questions->map
                                ->only([
                                    "question_text",
                                    "option_a",
                                    "option_b",
                                    "option_c",
                                    "option_d",
                                    "option_e",
                                    "correct_option",
                                ])
                                ->toArray(),
                        );
                }
            });
        }
    }
}
