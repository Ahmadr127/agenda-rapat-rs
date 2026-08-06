<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\AgendaQuestion;
use App\Models\AgendaQuestionAnswer;
use App\Models\Employee;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicQuizTest extends TestCase
{
    use RefreshDatabase;

    private function createActiveAgendaWithQuestions(int $questionCount = 3): Agenda
    {
        $agenda = Agenda::factory()->create([
            'event_date' => today(),
            'type' => 'diklat',
        ]);

        AgendaQuestion::factory()->count($questionCount)->create([
            'agenda_id' => $agenda->id,
            'correct_option' => 'a',
        ]);

        return $agenda;
    }

    /**
     * Helper: submit pretest for an employee so posttest can be done.
     */
    private function submitPretest(Agenda $agenda, Employee $employee): void
    {
        $agenda->employees()->syncWithoutDetaching([
            $employee->id => ['signature_image_path' => 'signatures/test.png'],
        ]);

        $answers = [];
        foreach ($agenda->agendaQuestions as $q) {
            $answers[$q->id] = 'a';
        }

        $this->postJson(route('attendance.pretest.store', $agenda), [
            'employee_id' => $employee->id,
            'answers' => $answers,
        ])->assertOk();
    }

    public function test_quiz_page_loads_for_agenda_with_questions(): void
    {
        $agenda = $this->createActiveAgendaWithQuestions();

        $response = $this->get(route('attendance.quiz', $agenda));

        $response->assertOk();
        $response->assertSee('Identifikasi Peserta');
    }

    public function test_quiz_page_404_for_agenda_without_questions(): void
    {
        $agenda = Agenda::factory()->create([
            'type' => 'rapat',
        ]);

        $response = $this->get(route('attendance.quiz', $agenda));

        $response->assertNotFound();
    }

    public function test_quiz_page_404_for_non_active_agenda(): void
    {
        $agenda = Agenda::factory()->create([
            'type' => 'diklat',
        ]);
        AgendaQuestion::factory()->create(['agenda_id' => $agenda->id]);

        $response = $this->get(route('attendance.quiz', $agenda));

        $response->assertNotFound();
    }

    public function test_employee_can_submit_posttest_after_pretest(): void
    {
        $agenda = $this->createActiveAgendaWithQuestions(2);
        $employee = Employee::factory()->create();

        // Must do pretest first
        $this->submitPretest($agenda, $employee);

        $questions = $agenda->agendaQuestions;
        $answers = [];
        foreach ($questions as $q) {
            $answers[$q->id] = 'a'; // correct_option is 'a'
        }

        $response = $this->postJson(route('attendance.quiz.store', $agenda), [
            'employee_id' => $employee->id,
            'answers' => $answers,
        ]);

        $response->assertOk();
        $response->assertJson([
            'correct' => 2,
            'total' => 2,
        ]);

        // 2 pretest + 2 posttest = 4
        $this->assertDatabaseCount('agenda_question_answers', 4);
        $this->assertDatabaseHas('agenda_question_answers', [
            'agenda_id' => $agenda->id,
            'employee_id' => $employee->id,
            'selected_option' => 'a',
            'is_correct' => true,
            'quiz_type' => 'posttest',
        ]);
    }

    public function test_posttest_cannot_be_submitted_without_pretest(): void
    {
        $agenda = $this->createActiveAgendaWithQuestions(1);
        $employee = Employee::factory()->create();
        $question = $agenda->agendaQuestions->first();

        $response = $this->postJson(route('attendance.quiz.store', $agenda), [
            'employee_id' => $employee->id,
            'answers' => [$question->id => 'a'],
        ]);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Anda harus mengerjakan pretest terlebih dahulu.']);
    }

    public function test_wrong_answers_are_tracked(): void
    {
        $agenda = $this->createActiveAgendaWithQuestions(2);
        $employee = Employee::factory()->create();

        // Must do pretest first
        $this->submitPretest($agenda, $employee);

        $questions = $agenda->agendaQuestions;
        $answers = [];
        foreach ($questions as $q) {
            $answers[$q->id] = 'b'; // correct is 'a', so this is wrong
        }

        $response = $this->postJson(route('attendance.quiz.store', $agenda), [
            'employee_id' => $employee->id,
            'answers' => $answers,
        ]);

        $response->assertOk();
        $response->assertJson([
            'correct' => 0,
            'total' => 2,
        ]);

        $this->assertDatabaseHas('agenda_question_answers', [
            'employee_id' => $employee->id,
            'is_correct' => false,
            'quiz_type' => 'posttest',
        ]);
    }

    public function test_employee_cannot_submit_posttest_twice(): void
    {
        $agenda = $this->createActiveAgendaWithQuestions(1);
        $employee = Employee::factory()->create();
        $question = $agenda->agendaQuestions->first();

        // Do pretest first
        $this->submitPretest($agenda, $employee);

        // First posttest submission
        $this->postJson(route('attendance.quiz.store', $agenda), [
            'employee_id' => $employee->id,
            'answers' => [$question->id => 'a'],
        ])->assertOk();

        // Second posttest submission
        $response = $this->postJson(route('attendance.quiz.store', $agenda), [
            'employee_id' => $employee->id,
            'answers' => [$question->id => 'b'],
        ]);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Anda sudah mengerjakan posttest.']);
        // 1 pretest + 1 posttest = 2
        $this->assertDatabaseCount('agenda_question_answers', 2);
    }

    public function test_employee_cannot_submit_pretest_twice(): void
    {
        $agenda = $this->createActiveAgendaWithQuestions(1);
        $employee = Employee::factory()->create();
        $agenda->employees()->attach($employee->id, ['signature_image_path' => 'signatures/test.png']);
        $question = $agenda->agendaQuestions->first();

        // First pretest submission
        $this->postJson(route('attendance.pretest.store', $agenda), [
            'employee_id' => $employee->id,
            'answers' => [$question->id => 'a'],
        ])->assertOk();

        // Second pretest submission
        $response = $this->postJson(route('attendance.pretest.store', $agenda), [
            'employee_id' => $employee->id,
            'answers' => [$question->id => 'b'],
        ]);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Anda sudah mengerjakan pretest.']);
        $this->assertDatabaseCount('agenda_question_answers', 1);
    }

    public function test_pretest_submission_stores_correct_quiz_type(): void
    {
        $agenda = $this->createActiveAgendaWithQuestions(1);
        $employee = Employee::factory()->create();
        $agenda->employees()->attach($employee->id, ['signature_image_path' => 'signatures/test.png']);
        $question = $agenda->agendaQuestions->first();

        $response = $this->postJson(route('attendance.pretest.store', $agenda), [
            'employee_id' => $employee->id,
            'answers' => [$question->id => 'a'],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('agenda_question_answers', [
            'agenda_id' => $agenda->id,
            'employee_id' => $employee->id,
            'quiz_type' => 'pretest',
            'is_correct' => true,
        ]);
    }

    public function test_submit_requires_valid_employee(): void
    {
        $agenda = $this->createActiveAgendaWithQuestions(1);
        $question = $agenda->agendaQuestions->first();

        $response = $this->postJson(route('attendance.quiz.store', $agenda), [
            'employee_id' => 9999,
            'answers' => [$question->id => 'a'],
        ]);

        $response->assertUnprocessable();
    }

    public function test_submit_requires_valid_options(): void
    {
        $agenda = $this->createActiveAgendaWithQuestions(1);
        $employee = Employee::factory()->create();
        $question = $agenda->agendaQuestions->first();

        $response = $this->postJson(route('attendance.quiz.store', $agenda), [
            'employee_id' => $employee->id,
            'answers' => [$question->id => 'z'],
        ]);

        $response->assertUnprocessable();
    }

    public function test_attendance_page_shows_posttest_link_for_diklat(): void
    {
        $agenda = $this->createActiveAgendaWithQuestions();

        $response = $this->get(route('attendance.show', $agenda));

        $response->assertOk();
        $response->assertSee('Posttest');
    }

    public function test_attendance_page_hides_posttest_link_for_rapat(): void
    {
        $agenda = Agenda::factory()->create([
            'type' => 'rapat',
            'event_date' => today(),
        ]);

        $response = $this->get(route('attendance.show', $agenda));

        $response->assertOk();
        $response->assertDontSee('Posttest');
    }

    public function test_deleting_agenda_cascades_to_answers(): void
    {
        $agenda = $this->createActiveAgendaWithQuestions(1);
        $employee = Employee::factory()->create();
        $question = $agenda->agendaQuestions->first();

        AgendaQuestionAnswer::create([
            'agenda_id' => $agenda->id,
            'employee_id' => $employee->id,
            'agenda_question_id' => $question->id,
            'selected_option' => 'a',
            'is_correct' => true,
            'quiz_type' => 'pretest',
        ]);

        $agenda->delete();

        $this->assertDatabaseCount('agenda_question_answers', 0);
    }
}
