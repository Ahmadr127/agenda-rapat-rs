<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\AgendaImage;
use App\Models\AgendaNote;
use App\Models\AgendaQuestion;
use App\Models\AgendaQuestionAnswer;
use App\Models\BankSoal;
use App\Models\Employee;
use App\Models\Question;
use App\Models\Room;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendaTypeChangeTest extends TestCase
{
    use RefreshDatabase;

    private function validAgendaData(array $overrides = []): array
    {
        $room = Room::factory()->create();
        $chair = Employee::factory()->create();

        return array_merge([
            'title' => 'Test Agenda',
            'event_date' => '2026-05-01',
            'event_time' => '10:00',
            'event_end_time' => '11:00',
            'unit_id' => Unit::factory()->create()->id,
            'event_leader_id' => $chair->id,
            'room_id' => $room->id,
            'type' => 'rapat',
        ], $overrides);
    }

    private function createBankSoalWithQuestions(int $count = 3): BankSoal
    {
        $bankSoal = BankSoal::factory()->create();
        Question::factory()->count($count)->create(['bank_soal_id' => $bankSoal->id]);

        return $bankSoal;
    }

    private function createDiklatWithQuizAnswers(string $type = 'diklat', int $employeeCount = 2): array
    {
        $bankSoal = $this->createBankSoalWithQuestions(3);
        $agenda = Agenda::factory()->create([
            'event_date' => today(),
            'event_time' => '09:00',
            'event_end_time' => '12:00',
            'type' => $type,
            'bank_soal_id' => $bankSoal->id,
        ]);

        $questions = Question::where('bank_soal_id', $bankSoal->id)->get();
        $agenda->agendaQuestions()->createMany(
            $questions->map->only(['question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'option_e', 'correct_option'])->toArray()
        );

        $employees = Employee::factory()->count($employeeCount)->create();
        foreach ($employees as $employee) {
            $this->submitQuizForEmployee($agenda, $employee);
        }

        return [$agenda, $employees, $bankSoal];
    }

    private function submitQuizForEmployee(Agenda $agenda, Employee $employee, string $quizType = 'pretest'): void
    {
        $options = ['a', 'b', 'c', 'd', 'e'];
        foreach ($agenda->agendaQuestions as $question) {
            $selected = $options[array_rand($options)];
            AgendaQuestionAnswer::create([
                'agenda_id' => $agenda->id,
                'employee_id' => $employee->id,
                'agenda_question_id' => $question->id,
                'selected_option' => $selected,
                'is_correct' => $selected === $question->correct_option,
                'quiz_type' => $quizType,
            ]);
        }
    }

    private function updateAgenda(Agenda $agenda, array $overrides): \Illuminate\Testing\TestResponse
    {
        $user = User::factory()->create();

        $data = array_merge([
            'title' => $agenda->title,
            'event_date' => $agenda->event_date->format('Y-m-d'),
            'event_time' => '09:00',
            'event_end_time' => '12:00',
            'unit_id' => $agenda->unit_id,
            'event_leader_id' => $agenda->event_leader_id,
            'room_id' => $agenda->room_id,
            'type' => $agenda->type,
            'bank_soal_id' => $agenda->bank_soal_id,
        ], $overrides);

        return $this->actingAs($user)->put(route('admin.agendas.update', $agenda), $data);
    }

    // ===== Group 1: Diklat/Pelatihan → Rapat =====

    public function test_changing_diklat_to_rapat_deletes_quiz_answers(): void
    {
        [$agenda, $employees] = $this->createDiklatWithQuizAnswers();

        $this->assertDatabaseCount('agenda_questions', 3);
        $this->assertDatabaseCount('agenda_question_answers', 6);

        $response = $this->updateAgenda($agenda, ['type' => 'rapat', 'bank_soal_id' => null]);

        $response->assertRedirect(route('admin.agendas.index'));
        $this->assertDatabaseCount('agenda_questions', 0);
        $this->assertDatabaseCount('agenda_question_answers', 0);
        $this->assertNull($agenda->fresh()->bank_soal_id);
    }

    public function test_changing_pelatihan_to_rapat_deletes_quiz_answers(): void
    {
        [$agenda, $employees] = $this->createDiklatWithQuizAnswers('pelatihan');

        $this->assertDatabaseCount('agenda_question_answers', 6);

        $response = $this->updateAgenda($agenda, ['type' => 'rapat', 'bank_soal_id' => null]);

        $response->assertRedirect(route('admin.agendas.index'));
        $this->assertDatabaseCount('agenda_questions', 0);
        $this->assertDatabaseCount('agenda_question_answers', 0);
    }

    public function test_changing_diklat_to_rapat_preserves_attendance(): void
    {
        [$agenda, $employees] = $this->createDiklatWithQuizAnswers();

        foreach ($employees as $employee) {
            $agenda->employees()->attach($employee->id, [
                'signature_image_path' => 'signatures/test.png',
            ]);
        }

        $this->updateAgenda($agenda, ['type' => 'rapat', 'bank_soal_id' => null]);

        $this->assertDatabaseCount('agenda_employee', 2);
    }

    public function test_changing_diklat_to_rapat_preserves_images(): void
    {
        [$agenda] = $this->createDiklatWithQuizAnswers();

        $agenda->images()->createMany([
            ['image_path' => 'agenda-images/1/a.jpg'],
            ['image_path' => 'agenda-images/1/b.jpg'],
        ]);

        $this->updateAgenda($agenda, ['type' => 'rapat', 'bank_soal_id' => null]);

        $this->assertDatabaseCount('agenda_images', 2);
    }

    // ===== Group 2: Rapat → Diklat (notes dihapus) =====

    public function test_changing_rapat_to_diklat_deletes_notes(): void
    {
        $agenda = Agenda::factory()->create(['type' => 'rapat', 'event_time' => '09:00']);
        $agenda->notes()->createMany([
            ['topic' => 'Topik 1', 'decision' => 'Keputusan 1'],
            ['topic' => 'Topik 2', 'decision' => 'Keputusan 2'],
            ['topic' => 'Topik 3', 'decision' => 'Keputusan 3'],
        ]);

        $this->assertDatabaseCount('agenda_notes', 3);

        $bankSoal = $this->createBankSoalWithQuestions();
        $this->updateAgenda($agenda, ['type' => 'diklat', 'bank_soal_id' => $bankSoal->id]);

        $this->assertDatabaseCount('agenda_notes', 0);
    }

    public function test_changing_rapat_to_diklat_hides_notulensi_on_admin_show(): void
    {
        $agenda = Agenda::factory()->create(['type' => 'rapat', 'event_time' => '09:00']);
        $agenda->notes()->create(['topic' => 'Topik', 'decision' => 'Keputusan']);

        $bankSoal = $this->createBankSoalWithQuestions();
        $this->updateAgenda($agenda, ['type' => 'diklat', 'bank_soal_id' => $bankSoal->id]);

        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('admin.agendas.show', $agenda));

        $response->assertOk();
        $response->assertDontSee('Notulensi Rapat');
    }

    public function test_changing_rapat_to_diklat_blocks_new_note_creation(): void
    {
        $agenda = Agenda::factory()->create(['type' => 'rapat', 'event_date' => today(), 'event_time' => '09:00']);

        $bankSoal = $this->createBankSoalWithQuestions();
        $this->updateAgenda($agenda, ['type' => 'diklat', 'bank_soal_id' => $bankSoal->id]);

        $response = $this->post(route('agenda.input.note', $agenda), [
            'topic' => 'New Topic',
            'decision' => 'New Decision',
        ]);

        $response->assertForbidden();
    }

    public function test_changing_rapat_to_diklat_snapshots_questions(): void
    {
        $agenda = Agenda::factory()->create(['type' => 'rapat', 'event_time' => '09:00']);

        $bankSoal = $this->createBankSoalWithQuestions(5);
        $this->updateAgenda($agenda, ['type' => 'diklat', 'bank_soal_id' => $bankSoal->id]);

        $this->assertDatabaseCount('agenda_questions', 5);
        $this->assertEquals($bankSoal->id, $agenda->fresh()->bank_soal_id);
    }

    // ===== Group 3: Ganti Bank Soal =====

    public function test_changing_bank_soal_deletes_old_answers_and_resnaps(): void
    {
        [$agenda, $employees, $oldBankSoal] = $this->createDiklatWithQuizAnswers();

        $this->assertDatabaseCount('agenda_questions', 3);
        $this->assertDatabaseCount('agenda_question_answers', 6);

        $newBankSoal = $this->createBankSoalWithQuestions(5);
        $this->updateAgenda($agenda, ['bank_soal_id' => $newBankSoal->id]);

        $this->assertDatabaseCount('agenda_question_answers', 0);
        $this->assertDatabaseCount('agenda_questions', 5);
        $this->assertEquals($newBankSoal->id, $agenda->fresh()->bank_soal_id);
    }

    // ===== Group 4: Quiz Access After Type Change =====

    public function test_quiz_page_404_after_diklat_changed_to_rapat(): void
    {
        [$agenda] = $this->createDiklatWithQuizAnswers();

        $this->updateAgenda($agenda, ['type' => 'rapat', 'bank_soal_id' => null]);

        $response = $this->get(route('attendance.quiz', $agenda));

        $response->assertNotFound();
    }

    public function test_quiz_submission_404_after_diklat_changed_to_rapat(): void
    {
        [$agenda, $employees] = $this->createDiklatWithQuizAnswers();
        $newEmployee = Employee::factory()->create();

        $this->updateAgenda($agenda, ['type' => 'rapat', 'bank_soal_id' => null]);

        $response = $this->postJson(route('attendance.quiz.store', $agenda), [
            'employee_id' => $newEmployee->id,
            'answers' => [],
        ]);

        $response->assertNotFound();
    }

    // ===== Group 5: Note Access After Type Change =====

    public function test_note_creation_allowed_after_diklat_changed_to_rapat(): void
    {
        [$agenda] = $this->createDiklatWithQuizAnswers();

        $this->updateAgenda($agenda, ['type' => 'rapat', 'bank_soal_id' => null]);

        $response = $this->post(route('agenda.input.note', $agenda), [
            'topic' => 'Topik Baru',
            'decision' => 'Keputusan Baru',
        ]);

        $response->assertRedirect(route('agenda.input', $agenda));
        $this->assertDatabaseHas('agenda_notes', [
            'agenda_id' => $agenda->id,
            'topic' => 'Topik Baru',
        ]);
    }

    // ===== Group 6: Diklat ↔ Pelatihan =====

    public function test_changing_diklat_to_pelatihan_resnaps_questions(): void
    {
        [$agenda, $employees, $bankSoal] = $this->createDiklatWithQuizAnswers();

        $this->assertDatabaseCount('agenda_question_answers', 6);

        $this->updateAgenda($agenda, ['type' => 'pelatihan']);

        $this->assertDatabaseCount('agenda_question_answers', 0);
        $this->assertDatabaseCount('agenda_questions', 3);
        $this->assertEquals('pelatihan', $agenda->fresh()->type);
    }

    public function test_admin_show_still_shows_bank_soal_after_diklat_to_pelatihan(): void
    {
        [$agenda] = $this->createDiklatWithQuizAnswers();

        $this->updateAgenda($agenda, ['type' => 'pelatihan']);

        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('admin.agendas.show', $agenda));

        $response->assertOk();
        $response->assertSee('Hasil Pretest');
    }
}
