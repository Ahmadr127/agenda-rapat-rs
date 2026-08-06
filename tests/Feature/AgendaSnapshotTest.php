<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\AgendaQuestion;
use App\Models\BankSoal;
use App\Models\Employee;
use App\Models\Question;
use App\Models\Room;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendaSnapshotTest extends TestCase
{
    use RefreshDatabase;

    private function validAgendaData(array $overrides = []): array
    {
        $unit = Unit::factory()->create();
        $room = Room::factory()->create();
        $leader = Employee::factory()->create();

        return array_merge([
            'title' => 'Test Agenda',
            'event_date' => '2026-05-01',
            'event_time' => '10:00',
            'event_end_time' => '11:00',
            'unit_id' => $unit->id,
            'event_leader_id' => $leader->id,
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

    public function test_store_rapat_has_no_questions(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.agendas.store'), $this->validAgendaData([
            'type' => 'rapat',
        ]));

        $response->assertRedirect(route('admin.agendas.index'));
        $agenda = Agenda::first();
        $this->assertEquals('rapat', $agenda->type);
        $this->assertNull($agenda->bank_soal_id);
        $this->assertDatabaseCount('agenda_questions', 0);
    }

    public function test_store_diklat_copies_questions(): void
    {
        $user = User::factory()->create();
        $bankSoal = $this->createBankSoalWithQuestions(3);

        $response = $this->actingAs($user)->post(route('admin.agendas.store'), $this->validAgendaData([
            'type' => 'diklat',
            'bank_soal_id' => $bankSoal->id,
        ]));

        $response->assertRedirect(route('admin.agendas.index'));
        $agenda = Agenda::first();
        $this->assertEquals('diklat', $agenda->type);
        $this->assertEquals($bankSoal->id, $agenda->bank_soal_id);
        $this->assertCount(3, $agenda->agendaQuestions);

        // Verify content was copied
        $sourceQuestion = $bankSoal->questions->first();
        $this->assertDatabaseHas('agenda_questions', [
            'agenda_id' => $agenda->id,
            'question_text' => $sourceQuestion->question_text,
            'option_a' => $sourceQuestion->option_a,
            'correct_option' => $sourceQuestion->correct_option,
        ]);
    }

    public function test_store_pelatihan_copies_questions(): void
    {
        $user = User::factory()->create();
        $bankSoal = $this->createBankSoalWithQuestions(2);

        $response = $this->actingAs($user)->post(route('admin.agendas.store'), $this->validAgendaData([
            'type' => 'pelatihan',
            'bank_soal_id' => $bankSoal->id,
        ]));

        $response->assertRedirect(route('admin.agendas.index'));
        $agenda = Agenda::first();
        $this->assertEquals('pelatihan', $agenda->type);
        $this->assertCount(2, $agenda->agendaQuestions);
    }

    public function test_store_requires_bank_soal_for_diklat(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.agendas.store'), $this->validAgendaData([
            'type' => 'diklat',
        ]));

        $response->assertSessionHasErrors('bank_soal_id');
    }

    public function test_store_requires_bank_soal_for_pelatihan(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.agendas.store'), $this->validAgendaData([
            'type' => 'pelatihan',
        ]));

        $response->assertSessionHasErrors('bank_soal_id');
    }

    public function test_update_diklat_to_rapat_clears_questions(): void
    {
        $user = User::factory()->create();
        $bankSoal = $this->createBankSoalWithQuestions(3);
        $room = Room::factory()->create();

        $agenda = Agenda::create([
            'title' => 'Diklat Agenda',
            'event_date' => '2026-05-01',
            'event_time' => '10:00',
            'unit_id' => Unit::factory()->create()->id,
            'event_leader_id' => Employee::factory()->create()->id,
            'room_id' => $room->id,
            'type' => 'diklat',
            'bank_soal_id' => $bankSoal->id,
        ]);
        // Manually snapshot
        foreach ($bankSoal->questions as $q) {
            $agenda->agendaQuestions()->create($q->only(['question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'option_e', 'correct_option']));
        }
        $this->assertCount(3, $agenda->agendaQuestions);

        $response = $this->actingAs($user)->put(route('admin.agendas.update', $agenda), [
            'title' => 'Updated Agenda',
            'event_date' => '2026-05-01',
            'event_time' => '10:00',
            'unit_id' => Unit::factory()->create()->id,
            'event_leader_id' => Employee::factory()->create()->id,
            'room_id' => $room->id,
            'type' => 'rapat',
        ]);

        $response->assertRedirect(route('admin.agendas.index'));
        $agenda->refresh();
        $this->assertEquals('rapat', $agenda->type);
        $this->assertNull($agenda->bank_soal_id);
        $this->assertCount(0, $agenda->agendaQuestions);
    }

    public function test_update_changes_bank_soal_resnaps(): void
    {
        $user = User::factory()->create();
        $bankSoalA = $this->createBankSoalWithQuestions(3);
        $bankSoalB = $this->createBankSoalWithQuestions(2);
        $room = Room::factory()->create();

        $agenda = Agenda::create([
            'title' => 'Diklat Agenda',
            'event_date' => '2026-05-01',
            'event_time' => '10:00',
            'unit_id' => Unit::factory()->create()->id,
            'event_leader_id' => Employee::factory()->create()->id,
            'room_id' => $room->id,
            'type' => 'diklat',
            'bank_soal_id' => $bankSoalA->id,
        ]);
        foreach ($bankSoalA->questions as $q) {
            $agenda->agendaQuestions()->create($q->only(['question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'option_e', 'correct_option']));
        }

        $response = $this->actingAs($user)->put(route('admin.agendas.update', $agenda), [
            'title' => 'Diklat Agenda',
            'event_date' => '2026-05-01',
            'event_time' => '10:00',
            'event_end_time' => '11:00',
            'unit_id' => Unit::factory()->create()->id,
            'event_leader_id' => Employee::factory()->create()->id,
            'room_id' => $room->id,
            'type' => 'diklat',
            'bank_soal_id' => $bankSoalB->id,
        ]);

        $response->assertRedirect(route('admin.agendas.index'));
        $agenda->refresh();
        $this->assertEquals($bankSoalB->id, $agenda->bank_soal_id);
        $this->assertCount(2, $agenda->agendaQuestions()->get());
    }

    public function test_deleting_bank_soal_keeps_agenda_questions(): void
    {
        $user = User::factory()->create();
        $bankSoal = $this->createBankSoalWithQuestions(3);

        $this->actingAs($user)->post(route('admin.agendas.store'), $this->validAgendaData([
            'type' => 'diklat',
            'bank_soal_id' => $bankSoal->id,
        ]));

        $agenda = Agenda::first();
        $this->assertCount(3, $agenda->agendaQuestions);

        // Delete the bank soal template
        $bankSoal->delete();

        // Agenda and its questions must survive
        $agenda->refresh();
        $this->assertNotNull($agenda->id);
        $this->assertNull($agenda->bank_soal_id); // nullOnDelete
        $this->assertCount(3, $agenda->agendaQuestions()->get());
    }

    public function test_editing_template_does_not_affect_snapshot(): void
    {
        $user = User::factory()->create();
        $bankSoal = BankSoal::factory()->create();
        $question = Question::factory()->create([
            'bank_soal_id' => $bankSoal->id,
            'question_text' => 'Original question text',
        ]);

        $this->actingAs($user)->post(route('admin.agendas.store'), $this->validAgendaData([
            'type' => 'diklat',
            'bank_soal_id' => $bankSoal->id,
        ]));

        $agenda = Agenda::first();
        $this->assertDatabaseHas('agenda_questions', [
            'agenda_id' => $agenda->id,
            'question_text' => 'Original question text',
        ]);

        // Edit the template question
        $question->update(['question_text' => 'Modified question text']);

        // Snapshot must remain unchanged
        $this->assertDatabaseHas('agenda_questions', [
            'agenda_id' => $agenda->id,
            'question_text' => 'Original question text',
        ]);
    }

    public function test_show_displays_bank_soal_summary(): void
    {
        $user = User::factory()->create();
        $bankSoal = BankSoal::factory()->create(['title' => 'Soal Biologi']);
        Question::factory()->count(3)->create(['bank_soal_id' => $bankSoal->id]);

        $this->actingAs($user)->post(route('admin.agendas.store'), $this->validAgendaData([
            'type' => 'diklat',
            'bank_soal_id' => $bankSoal->id,
        ]));

        $agenda = Agenda::first();
        $response = $this->actingAs($user)->get(route('admin.agendas.show', $agenda));

        $response->assertOk();
        $response->assertSee('Soal Biologi');
        $response->assertSee('3 soal');
        $response->assertSee('Lihat Soal');
    }

    public function test_index_shows_type_column(): void
    {
        $user = User::factory()->create();
        $bankSoal = $this->createBankSoalWithQuestions(1);

        $this->actingAs($user)->post(route('admin.agendas.store'), $this->validAgendaData([
            'type' => 'diklat',
            'bank_soal_id' => $bankSoal->id,
        ]));

        $response = $this->actingAs($user)->get(route('admin.agendas.index'));

        $response->assertOk();
        $response->assertSee('Diklat');
    }

    public function test_create_page_uses_searchable_select_endpoints(): void
    {
        $user = User::factory()->create();
        BankSoal::factory()->create(['title' => 'Soal Matematika']);

        $response = $this->actingAs($user)->get(route('admin.agendas.create'));

        $response->assertOk();
        $response->assertSee(str_replace('/', '\\/', route('admin.employees.search')), false);
        $response->assertSee(str_replace('/', '\\/', route('admin.rooms.search')), false);
        $response->assertSee(str_replace('/', '\\/', route('admin.bank-soals.search')), false);
        $response->assertDontSee('Soal Matematika');
    }

    public function test_employee_search_endpoint_returns_paginated_results(): void
    {
        $user = User::factory()->create();

        Employee::factory()->count(12)->create();

        $response = $this->actingAs($user)->getJson(route('admin.employees.search'));

        $response->assertOk()
            ->assertJsonCount(10, 'items')
            ->assertJsonPath('has_more', true);
    }

    public function test_room_search_endpoint_returns_paginated_results(): void
    {
        $user = User::factory()->create();

        Room::factory()->count(12)->create();

        $response = $this->actingAs($user)->getJson(route('admin.rooms.search'));

        $response->assertOk()
            ->assertJsonCount(10, 'items')
            ->assertJsonPath('has_more', true);
    }

    public function test_bank_soal_search_endpoint_returns_paginated_results(): void
    {
        $user = User::factory()->create();

        BankSoal::factory()->count(12)->create();

        $response = $this->actingAs($user)->getJson(route('admin.bank-soals.search'));

        $response->assertOk()
            ->assertJsonCount(10, 'items')
            ->assertJsonPath('has_more', true);
    }

    public function test_agenda_type_search_endpoint_returns_backend_results(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(route('admin.agendas.types.search', ['q' => 'dik']));

        $response->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.id', 'diklat')
            ->assertJsonPath('items.0.name', 'Diklat')
            ->assertJsonPath('has_more', false);
    }
}
