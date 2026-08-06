<?php

namespace Tests\Unit;

use App\Models\Agenda;
use App\Models\AgendaQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendaQuestionModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_agenda_has_many_agenda_questions(): void
    {
        $agenda = Agenda::factory()->create();
        AgendaQuestion::factory()->count(3)->create(['agenda_id' => $agenda->id]);

        $this->assertCount(3, $agenda->agendaQuestions);
    }

    public function test_deleting_agenda_cascades_to_agenda_questions(): void
    {
        $agenda = Agenda::factory()->create();
        AgendaQuestion::factory()->count(2)->create(['agenda_id' => $agenda->id]);

        $agenda->delete();

        $this->assertDatabaseCount('agenda_questions', 0);
    }

    public function test_agenda_question_belongs_to_agenda(): void
    {
        $agenda = Agenda::factory()->create();
        $question = AgendaQuestion::factory()->create(['agenda_id' => $agenda->id]);

        $this->assertInstanceOf(Agenda::class, $question->agenda);
        $this->assertEquals($agenda->id, $question->agenda->id);
    }
}
