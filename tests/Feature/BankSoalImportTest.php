<?php

namespace Tests\Feature;

use App\Models\BankSoal;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankSoalImportTest extends TestCase
{
    use RefreshDatabase;

    private function questionWith5Options(array $overrides = []): array
    {
        return array_merge([
            'question_text' => 'Apa ibu kota Indonesia?',
            'option_a' => 'Jakarta',
            'option_b' => 'Bandung',
            'option_c' => 'Surabaya',
            'option_d' => 'Medan',
            'option_e' => 'Makassar',
            'correct_option' => 'a',
        ], $overrides);
    }

    private function questionWith3Options(array $overrides = []): array
    {
        return array_merge([
            'question_text' => 'Warna bendera Indonesia?',
            'option_a' => 'Merah Putih',
            'option_b' => 'Biru Kuning',
            'option_c' => 'Hijau Hitam',
            'correct_option' => 'a',
        ], $overrides);
    }

    private function questionWith4Options(array $overrides = []): array
    {
        return array_merge([
            'question_text' => 'Berapa jumlah provinsi di Indonesia?',
            'option_a' => '34',
            'option_b' => '35',
            'option_c' => '36',
            'option_d' => '37',
            'correct_option' => 'a',
        ], $overrides);
    }

    // --- store() tests ---

    public function test_store_accepts_questions_with_all_five_options(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.bank-soals.store'), [
            'title' => 'Bank Soal 5 Opsi',
            'description' => 'Semua soal punya 5 opsi',
            'questions' => [
                $this->questionWith5Options(),
                $this->questionWith5Options(['question_text' => 'Apa rumus air?', 'correct_option' => 'b']),
            ],
        ]);

        $response->assertRedirect(route('admin.bank-soals.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('bank_soals', ['title' => 'Bank Soal 5 Opsi']);
        $this->assertDatabaseHas('questions', [
            'question_text' => 'Apa ibu kota Indonesia?',
            'option_d' => 'Medan',
            'option_e' => 'Makassar',
        ]);
    }

    public function test_store_accepts_questions_with_only_three_options(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.bank-soals.store'), [
            'title' => 'Bank Soal 3 Opsi',
            'questions' => [
                $this->questionWith3Options(),
            ],
        ]);

        $response->assertRedirect(route('admin.bank-soals.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('bank_soals', ['title' => 'Bank Soal 3 Opsi']);
        $this->assertDatabaseHas('questions', [
            'question_text' => 'Warna bendera Indonesia?',
            'option_d' => null,
            'option_e' => null,
        ]);
    }

    public function test_store_accepts_questions_with_four_options_but_no_option_e(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.bank-soals.store'), [
            'title' => 'Bank Soal 4 Opsi',
            'questions' => [
                $this->questionWith4Options(),
            ],
        ]);

        $response->assertRedirect(route('admin.bank-soals.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('questions', [
            'question_text' => 'Berapa jumlah provinsi di Indonesia?',
            'option_d' => '37',
            'option_e' => null,
        ]);
    }

    public function test_store_fails_validation_when_option_a_is_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.bank-soals.store'), [
            'title' => 'Soal Tidak Lengkap',
            'questions' => [
                [
                    'question_text' => 'Pertanyaan tanpa option_a',
                    'option_b' => 'Opsi B',
                    'option_c' => 'Opsi C',
                    'correct_option' => 'b',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('questions.0.option_a');
    }

    public function test_store_fails_validation_when_option_b_is_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.bank-soals.store'), [
            'title' => 'Soal Tidak Lengkap',
            'questions' => [
                [
                    'question_text' => 'Pertanyaan tanpa option_b',
                    'option_a' => 'Opsi A',
                    'option_c' => 'Opsi C',
                    'correct_option' => 'a',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('questions.0.option_b');
    }

    public function test_store_fails_validation_when_option_c_is_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.bank-soals.store'), [
            'title' => 'Soal Tidak Lengkap',
            'questions' => [
                [
                    'question_text' => 'Pertanyaan tanpa option_c',
                    'option_a' => 'Opsi A',
                    'option_b' => 'Opsi B',
                    'correct_option' => 'a',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('questions.0.option_c');
    }

    public function test_store_fails_validation_when_correct_option_is_invalid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.bank-soals.store'), [
            'title' => 'Soal Jawaban Salah',
            'questions' => [
                $this->questionWith3Options(['correct_option' => 'f']),
            ],
        ]);

        $response->assertSessionHasErrors('questions.0.correct_option');
    }

    public function test_store_fails_validation_when_no_questions_provided(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.bank-soals.store'), [
            'title' => 'Bank Soal Kosong',
            'questions' => [],
        ]);

        $response->assertSessionHasErrors('questions');
    }

    public function test_store_fails_validation_when_questions_key_is_absent(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.bank-soals.store'), [
            'title' => 'Tanpa Soal Sama Sekali',
        ]);

        $response->assertSessionHasErrors('questions');
    }

    // --- update() tests ---

    public function test_update_accepts_questions_without_option_d_and_option_e(): void
    {
        $user = User::factory()->create();
        $bankSoal = BankSoal::factory()->create(['title' => 'Judul Lama']);
        Question::factory()->create(['bank_soal_id' => $bankSoal->id]);

        $response = $this->actingAs($user)->put(route('admin.bank-soals.update', $bankSoal), [
            'title' => 'Judul Diperbarui',
            'questions' => [
                $this->questionWith3Options(['question_text' => 'Soal baru hanya 3 opsi']),
            ],
        ]);

        $response->assertRedirect(route('admin.bank-soals.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('bank_soals', ['title' => 'Judul Diperbarui']);
        $this->assertDatabaseHas('questions', [
            'question_text' => 'Soal baru hanya 3 opsi',
            'option_d' => null,
            'option_e' => null,
        ]);
    }

    public function test_update_replaces_all_questions_with_three_option_questions(): void
    {
        $user = User::factory()->create();
        $bankSoal = BankSoal::factory()->create(['title' => 'Bank Soal Lama']);
        $oldQuestion = Question::factory()->create([
            'bank_soal_id' => $bankSoal->id,
            'question_text' => 'Soal lama dengan 5 opsi',
        ]);

        $response = $this->actingAs($user)->put(route('admin.bank-soals.update', $bankSoal), [
            'title' => 'Bank Soal Diperbarui',
            'questions' => [
                $this->questionWith3Options(['question_text' => 'Soal baru pertama hanya 3 opsi']),
                $this->questionWith3Options(['question_text' => 'Soal baru kedua hanya 3 opsi', 'correct_option' => 'b']),
            ],
        ]);

        $response->assertRedirect(route('admin.bank-soals.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('questions', ['question_text' => 'Soal lama dengan 5 opsi']);
        $this->assertDatabaseHas('questions', [
            'question_text' => 'Soal baru pertama hanya 3 opsi',
            'option_d' => null,
            'option_e' => null,
        ]);
        $this->assertDatabaseHas('questions', [
            'question_text' => 'Soal baru kedua hanya 3 opsi',
            'option_d' => null,
            'option_e' => null,
        ]);
    }

    public function test_update_accepts_mix_of_three_and_five_option_questions(): void
    {
        $user = User::factory()->create();
        $bankSoal = BankSoal::factory()->create();
        Question::factory()->create(['bank_soal_id' => $bankSoal->id]);

        $response = $this->actingAs($user)->put(route('admin.bank-soals.update', $bankSoal), [
            'title' => 'Bank Soal Campuran',
            'questions' => [
                $this->questionWith5Options(['question_text' => 'Soal dengan semua opsi']),
                $this->questionWith3Options(['question_text' => 'Soal hanya tiga opsi']),
            ],
        ]);

        $response->assertRedirect(route('admin.bank-soals.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('questions', [
            'question_text' => 'Soal dengan semua opsi',
            'option_e' => 'Makassar',
        ]);
        $this->assertDatabaseHas('questions', [
            'question_text' => 'Soal hanya tiga opsi',
            'option_d' => null,
            'option_e' => null,
        ]);
    }
}
