<?php

namespace Tests\Unit;

use App\Models\BankSoal;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankSoalModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_soal_has_many_questions(): void
    {
        $bankSoal = BankSoal::factory()->create();
        Question::factory()->count(3)->create(['bank_soal_id' => $bankSoal->id]);

        $this->assertCount(3, $bankSoal->questions);
    }

    public function test_deleting_bank_soal_cascades_to_questions(): void
    {
        $bankSoal = BankSoal::factory()->create();
        Question::factory()->count(2)->create(['bank_soal_id' => $bankSoal->id]);

        $bankSoal->delete();

        $this->assertDatabaseCount('questions', 0);
    }

    public function test_question_belongs_to_bank_soal(): void
    {
        $bankSoal = BankSoal::factory()->create();
        $question = Question::factory()->create(['bank_soal_id' => $bankSoal->id]);

        $this->assertInstanceOf(BankSoal::class, $question->bankSoal);
        $this->assertEquals($bankSoal->id, $question->bankSoal->id);
    }
}
