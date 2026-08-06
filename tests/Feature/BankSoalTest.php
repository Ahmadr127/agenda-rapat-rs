<?php

namespace Tests\Feature;

use App\Models\BankSoal;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankSoalTest extends TestCase
{
    use RefreshDatabase;

    private function validQuestionData(array $overrides = []): array
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

    public function test_index_displays_bank_soals(): void
    {
        $user = User::factory()->create();
        $bankSoal = BankSoal::factory()->create(['title' => 'Soal Matematika']);

        $response = $this->actingAs($user)->get(route('admin.bank-soals.index'));

        $response->assertOk();
        $response->assertSee('Soal Matematika');
    }

    public function test_create_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.bank-soals.create'));

        $response->assertOk();
    }

    public function test_bank_soal_can_be_stored(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.bank-soals.store'), [
            'title' => 'Soal IPA Kelas 10',
            'description' => 'Soal untuk ujian semester',
            'questions' => [
                $this->validQuestionData(),
                $this->validQuestionData(['question_text' => 'Apa rumus air?', 'correct_option' => 'b']),
            ],
        ]);

        $response->assertRedirect(route('admin.bank-soals.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('bank_soals', ['title' => 'Soal IPA Kelas 10']);
        $this->assertDatabaseHas('questions', ['question_text' => 'Apa ibu kota Indonesia?']);
        $this->assertDatabaseHas('questions', ['question_text' => 'Apa rumus air?']);
    }

    public function test_store_requires_title(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.bank-soals.store'), [
            'title' => '',
            'questions' => [$this->validQuestionData()],
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_store_requires_at_least_one_question(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.bank-soals.store'), [
            'title' => 'Soal Kosong',
            'questions' => [],
        ]);

        $response->assertSessionHasErrors('questions');
    }

    public function test_store_validates_question_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.bank-soals.store'), [
            'title' => 'Soal Tidak Lengkap',
            'questions' => [
                ['question_text' => 'Pertanyaan tanpa opsi'],
            ],
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_show_displays_bank_soal_with_questions(): void
    {
        $user = User::factory()->create();
        $bankSoal = BankSoal::factory()->create(['title' => 'Soal Biologi']);
        Question::factory()->create([
            'bank_soal_id' => $bankSoal->id,
            'question_text' => 'Apa fungsi mitokondria?',
        ]);

        $response = $this->actingAs($user)->get(route('admin.bank-soals.show', $bankSoal));

        $response->assertOk();
        $response->assertSee('Soal Biologi');
        $response->assertSee('Apa fungsi mitokondria?');
    }

    public function test_edit_page_is_displayed(): void
    {
        $user = User::factory()->create();
        $bankSoal = BankSoal::factory()->create(['title' => 'Soal Fisika']);
        Question::factory()->create(['bank_soal_id' => $bankSoal->id]);

        $response = $this->actingAs($user)->get(route('admin.bank-soals.edit', $bankSoal));

        $response->assertOk();
        $response->assertSee('Soal Fisika');
    }

    public function test_bank_soal_can_be_updated(): void
    {
        $user = User::factory()->create();
        $bankSoal = BankSoal::factory()->create(['title' => 'Judul Lama']);
        $oldQuestion = Question::factory()->create([
            'bank_soal_id' => $bankSoal->id,
            'question_text' => 'Soal lama akan dihapus',
        ]);

        $response = $this->actingAs($user)->put(route('admin.bank-soals.update', $bankSoal), [
            'title' => 'Judul Baru',
            'description' => 'Deskripsi baru',
            'questions' => [
                $this->validQuestionData(['question_text' => 'Soal baru setelah update']),
            ],
        ]);

        $response->assertRedirect(route('admin.bank-soals.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('bank_soals', ['title' => 'Judul Baru']);
        $this->assertDatabaseHas('questions', ['question_text' => 'Soal baru setelah update']);
        $this->assertDatabaseMissing('questions', ['question_text' => 'Soal lama akan dihapus']);
    }

    public function test_bank_soal_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $bankSoal = BankSoal::factory()->create(['title' => 'Akan Dihapus']);
        $question = Question::factory()->create(['bank_soal_id' => $bankSoal->id]);

        $response = $this->actingAs($user)->delete(route('admin.bank-soals.destroy', $bankSoal));

        $response->assertRedirect(route('admin.bank-soals.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('bank_soals', ['id' => $bankSoal->id]);
        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $response = $this->get(route('admin.bank-soals.index'));

        $response->assertRedirect(route('login'));
    }
}
