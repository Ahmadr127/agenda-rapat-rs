<?php

namespace Database\Seeders;

use App\Models\BankSoal;
use App\Models\Question;
use Illuminate\Database\Seeder;

class BankSoalSeeder extends Seeder
{
    public function run(): void
    {
        $bankSoal = BankSoal::create([
            'title'       => 'Template Bank Soal Perawat',
            'description' => 'Bank soal kompetensi keperawatan meliputi tanda vital, farmakologi, keselamatan pasien, dan gawat darurat.',
        ]);

        $questions = [
            [
                'question_text' => 'Posisi yang tepat untuk pasien dengan sesak napas berat adalah?',
                'option_a'      => 'Trendelenburg (kepala lebih rendah dari kaki)',
                'option_b'      => 'Litotomi (kedua kaki diangkat)',
                'option_c'      => 'Semi-Fowler 30–45° atau Fowler tinggi',
                'option_d'      => 'Prone (tengkurap)',
                'option_e'      => 'Supine datar tanpa bantal',
                'correct_option'=> 'c',
            ],
            [
                'question_text' => 'Berapa nilai normal saturasi oksigen (SpO2) pada orang dewasa sehat?',
                'option_a'      => '70–80%',
                'option_b'      => '80–85%',
                'option_c'      => '85–90%',
                'option_d'      => '90–94%',
                'option_e'      => '95–100%',
                'correct_option'=> 'e',
            ],
            [
                'question_text' => 'Berapa frekuensi pernapasan normal pada orang dewasa?',
                'option_a'      => '5–10 kali/menit',
                'option_b'      => '12–20 kali/menit',
                'option_c'      => '25–35 kali/menit',
                'option_d'      => '35–45 kali/menit',
                'option_e'      => '45–60 kali/menit',
                'correct_option'=> 'b',
            ],
            [
                'question_text' => 'Prinsip "6 Benar" dalam pemberian obat yang aman meliputi?',
                'option_a'      => 'Benar pasien, obat, dosis, waktu, rute, dokumentasi',
                'option_b'      => 'Benar dokter, obat, dosis, waktu, rute, dokumentasi',
                'option_c'      => 'Benar pasien, warna obat, dosis, waktu, rute, dokumentasi',
                'option_d'      => 'Benar pasien, obat, dosis, hari, rute, ruangan',
                'option_e'      => 'Benar pasien, obat, dosis, waktu, ruangan, perawat',
                'correct_option'=> 'a',
            ],
            [
                'question_text' => 'Saat menemukan pasien tidak sadarkan diri, tindakan pertama perawat adalah?',
                'option_a'      => 'Langsung melakukan kompresi dada',
                'option_b'      => 'Memasang infus darurat',
                'option_c'      => 'Memeriksa respons dengan memanggil dan menepuk bahu',
                'option_d'      => 'Memberikan oksigen segera',
                'option_e'      => 'Menghubungi keluarga pasien',
                'correct_option'=> 'c',
            ],
            [
                'question_text' => 'Tanda klinis utama dehidrasi berat pada pasien adalah?',
                'option_a'      => 'Tekanan darah meningkat dan diuresis bertambah',
                'option_b'      => 'Turgor kulit menurun dan mukosa mulut kering',
                'option_c'      => 'Nadi lambat dan berat badan bertambah',
                'option_d'      => 'Suhu tubuh menurun dan keringat berlebih',
                'option_e'      => 'Edema perifer dan pernapasan cepat',
                'correct_option'=> 'b',
            ],
            [
                'question_text' => 'Nilai tekanan darah yang diklasifikasikan normal pada orang dewasa adalah?',
                'option_a'      => '≥140/90 mmHg',
                'option_b'      => '130–139/80–89 mmHg',
                'option_c'      => '<120/80 mmHg',
                'option_d'      => '90–100/60–70 mmHg',
                'option_e'      => '<90/60 mmHg',
                'correct_option'=> 'c',
            ],
            [
                'question_text' => 'Tujuan utama pemasangan kateter urin pada pasien adalah?',
                'option_a'      => 'Mendeteksi infeksi saluran kemih secara langsung',
                'option_b'      => 'Memberikan obat melalui saluran kemih',
                'option_c'      => 'Mengukur kadar glukosa dalam urin',
                'option_d'      => 'Drainase kandung kemih dan memantau haluaran urin',
                'option_e'      => 'Mengambil sampel urin steril untuk kultur',
                'correct_option'=> 'd',
            ],
            [
                'question_text' => 'Teknik asepsis dalam keperawatan bertujuan untuk?',
                'option_a'      => 'Mempercepat penyembuhan luka pasien',
                'option_b'      => 'Mengurangi nyeri pasien selama prosedur',
                'option_c'      => 'Mencegah masuk dan penyebaran mikroorganisme patogen',
                'option_d'      => 'Mempermudah pemasangan alat medis',
                'option_e'      => 'Memantau tanda-tanda vital pasien secara akurat',
                'correct_option'=> 'c',
            ],
            [
                'question_text' => 'Yang dimaksud dengan edema pitting adalah?',
                'option_a'      => 'Bengkak akibat infeksi bakteri lokal',
                'option_b'      => 'Bengkak keras yang tidak berubah saat ditekan',
                'option_c'      => 'Bengkak akibat alergi kulit',
                'option_d'      => 'Akumulasi cairan yang meninggalkan cekungan saat ditekan',
                'option_e'      => 'Bengkak pada area luka operasi',
                'correct_option'=> 'd',
            ],
            [
                'question_text' => 'Berapa nilai normal nadi orang dewasa dalam keadaan istirahat?',
                'option_a'      => '20–40 kali/menit',
                'option_b'      => '40–60 kali/menit',
                'option_c'      => '60–100 kali/menit',
                'option_d'      => '100–120 kali/menit',
                'option_e'      => '>120 kali/menit',
                'correct_option'=> 'c',
            ],
            [
                'question_text' => 'Tindakan keperawatan yang tepat untuk mencegah dekubitus pada pasien tirah baring adalah?',
                'option_a'      => 'Memberikan analgesik secara rutin',
                'option_b'      => 'Memasang kateter urin permanen',
                'option_c'      => 'Membatasi asupan cairan pasien',
                'option_d'      => 'Melakukan alih posisi setiap 2 jam',
                'option_e'      => 'Memasang restrain pada ekstremitas',
                'correct_option'=> 'd',
            ],
            [
                'question_text' => 'Skala nyeri berapa yang mengindikasikan nyeri berat menurut Numeric Rating Scale (NRS)?',
                'option_a'      => '0',
                'option_b'      => '1–3',
                'option_c'      => '4–6',
                'option_d'      => '7–9',
                'option_e'      => '10',
                'correct_option'=> 'd',
            ],
            [
                'question_text' => 'Apa yang harus segera dilakukan perawat jika pasien mengalami anafilaksis?',
                'option_a'      => 'Berikan antihistamin oral',
                'option_b'      => 'Berikan epinefrin intramuskular dan panggil bantuan darurat',
                'option_c'      => 'Berikan kortikosteroid intravena',
                'option_d'      => 'Pasang infus dan berikan cairan NaCl 0,9%',
                'option_e'      => 'Posisikan pasien duduk tegak',
                'correct_option'=> 'b',
            ],
            [
                'question_text' => 'Glasgow Coma Scale (GCS) dengan nilai berapa yang menunjukkan pasien koma?',
                'option_a'      => 'GCS 15',
                'option_b'      => 'GCS 12–14',
                'option_c'      => 'GCS 9–11',
                'option_d'      => 'GCS 5–8',
                'option_e'      => 'GCS ≤8',
                'correct_option'=> 'e',
            ],
            [
                'question_text' => 'Cairan infus apa yang digunakan untuk mengatasi hipovolemia ringan?',
                'option_a'      => 'Dextrose 5%',
                'option_b'      => 'NaCl 0,9% (Normal Saline)',
                'option_c'      => 'Dextrose 10%',
                'option_d'      => null,
                'option_e'      => null,
                'correct_option'=> 'b',
            ],
            [
                'question_text' => 'Apa yang dimaksud dengan triage dalam keperawatan gawat darurat?',
                'option_a'      => 'Proses administrasi pendaftaran pasien',
                'option_b'      => 'Prosedur operasi darurat',
                'option_c'      => 'Proses pemilahan pasien berdasarkan tingkat kegawatan',
                'option_d'      => null,
                'option_e'      => null,
                'correct_option'=> 'c',
            ],
        ];

        foreach ($questions as $question) {
            Question::create(array_merge(['bank_soal_id' => $bankSoal->id], $question));
        }
    }
}
