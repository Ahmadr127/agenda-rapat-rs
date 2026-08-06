<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\AgendaQuestionAnswer;
use App\Models\BankSoal;
use App\Models\Employee;
use App\Models\Question;
use App\Models\Room;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AgendaCrudTest extends TestCase
{
    use RefreshDatabase;

    private function validRapatData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Rapat Koordinasi',
            'event_date' => '2026-05-10',
            'event_time' => '09:00',
            'unit_id' => Unit::factory()->create()->id,
            'event_leader_id' => Employee::factory()->create()->id,
            'room_id' => Room::factory()->create()->id,
            'type' => 'rapat',
        ], $overrides);
    }

    private function validDiklatData(BankSoal $bankSoal, array $overrides = []): array
    {
        return array_merge([
            'title' => 'Diklat Kompetensi',
            'event_date' => '2026-05-10',
            'event_time' => '09:00',
            'event_end_time' => '12:00',
            'unit_id' => Unit::factory()->create()->id,
            'event_leader_id' => Employee::factory()->create()->id,
            'room_id' => Room::factory()->create()->id,
            'type' => 'diklat',
            'bank_soal_id' => $bankSoal->id,
        ], $overrides);
    }

    private function makeBankSoal(int $count = 3): BankSoal
    {
        $bankSoal = BankSoal::factory()->create();
        Question::factory()->count($count)->create(['bank_soal_id' => $bankSoal->id]);
        return $bankSoal;
    }

    private function snapshotQuestions(Agenda $agenda): void
    {
        $questions = Question::where('bank_soal_id', $agenda->bank_soal_id)->get();
        $agenda->agendaQuestions()->createMany(
            $questions->map->only([
                'question_text', 'option_a', 'option_b', 'option_c',
                'option_d', 'option_e', 'correct_option',
            ])->toArray()
        );
    }

    private function submitAnswers(Agenda $agenda, Employee $employee): void
    {
        foreach ($agenda->agendaQuestions as $question) {
            AgendaQuestionAnswer::create([
                'agenda_id' => $agenda->id,
                'employee_id' => $employee->id,
                'agenda_question_id' => $question->id,
                'selected_option' => 'a',
                'is_correct' => $question->correct_option === 'a',
                'quiz_type' => 'pretest',
            ]);
        }
    }

    // ===== Basic CRUD =====

    public function test_index_displays_agendas(): void
    {
        $user = User::factory()->create();
        Agenda::factory()->create(['title' => 'Rapat Bulanan']);

        $response = $this->actingAs($user)->get(route('admin.agendas.index'));

        $response->assertOk();
        $response->assertSee('Rapat Bulanan');
    }

    public function test_create_page_is_accessible(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.agendas.create'));

        $response->assertOk();
    }

    public function test_rapat_agenda_can_be_stored(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(
            route('admin.agendas.store'),
            $this->validRapatData(['title' => 'Rapat Baru'])
        );

        $response->assertRedirect(route('admin.agendas.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('agendas', ['title' => 'Rapat Baru', 'type' => 'rapat']);
    }

    public function test_diklat_agenda_can_be_stored(): void
    {
        $user = User::factory()->create();
        $bankSoal = $this->makeBankSoal(2);

        $response = $this->actingAs($user)->post(
            route('admin.agendas.store'),
            $this->validDiklatData($bankSoal, ['title' => 'Diklat Baru'])
        );

        $response->assertRedirect(route('admin.agendas.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('agendas', ['title' => 'Diklat Baru', 'type' => 'diklat']);
    }

    public function test_store_requires_title(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(
            route('admin.agendas.store'),
            $this->validRapatData(['title' => ''])
        );

        $response->assertSessionHasErrors('title');
    }

    public function test_edit_page_is_accessible(): void
    {
        $user = User::factory()->create();
        $agenda = Agenda::factory()->create(['title' => 'Agenda Edit Test']);

        $response = $this->actingAs($user)->get(route('admin.agendas.edit', $agenda));

        $response->assertOk();
        $response->assertSee('Agenda Edit Test');
    }

    public function test_agenda_can_be_updated(): void
    {
        $user = User::factory()->create();
        $agenda = Agenda::factory()->create(['type' => 'rapat', 'event_time' => '09:00']);

        $response = $this->actingAs($user)->put(route('admin.agendas.update', $agenda), [
            'title' => 'Judul Diperbarui',
            'event_date' => $agenda->event_date->format('Y-m-d'),
            'event_time' => '09:00',
            'unit_id' => $agenda->unit_id,
            'event_leader_id' => $agenda->event_leader_id,
            'room_id' => $agenda->room_id,
            'type' => 'rapat',
        ]);

        $response->assertRedirect(route('admin.agendas.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('agendas', ['id' => $agenda->id, 'title' => 'Judul Diperbarui']);
    }

    public function test_agenda_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $agenda = Agenda::factory()->create(['title' => 'Agenda Hapus']);

        $response = $this->actingAs($user)->delete(route('admin.agendas.destroy', $agenda));

        $response->assertRedirect(route('admin.agendas.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('agendas', ['id' => $agenda->id]);
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $response = $this->get(route('admin.agendas.index'));

        $response->assertRedirect(route('login'));
    }

    // ===== Case 1: Update dengan bank_soal_id sama — jawaban yang sudah diisi tidak tertimpa =====

    public function test_update_with_same_bank_soal_preserves_existing_answers(): void
    {
        $user = User::factory()->create();
        $bankSoal = $this->makeBankSoal(3);
        $agenda = Agenda::factory()->create([
            'type' => 'diklat',
            'bank_soal_id' => $bankSoal->id,
            'event_time' => '09:00',
            'event_end_time' => '12:00',
        ]);
        $this->snapshotQuestions($agenda);

        $employee = Employee::factory()->create();
        $this->submitAnswers($agenda, $employee);

        $this->assertDatabaseCount('agenda_questions', 3);
        $this->assertDatabaseCount('agenda_question_answers', 3);

        // Update hanya judul, tipe dan bank_soal_id tetap sama
        $response = $this->actingAs($user)->put(route('admin.agendas.update', $agenda), [
            'title' => 'Judul Baru Saja',
            'event_date' => $agenda->event_date->format('Y-m-d'),
            'event_time' => '09:00',
            'event_end_time' => '12:00',
            'unit_id' => $agenda->unit_id,
            'event_leader_id' => $agenda->event_leader_id,
            'room_id' => $agenda->room_id,
            'type' => 'diklat',
            'bank_soal_id' => $bankSoal->id,
        ]);

        $response->assertRedirect(route('admin.agendas.index'));
        $this->assertDatabaseCount('agenda_questions', 3);
        $this->assertDatabaseCount('agenda_question_answers', 3);
    }

    public function test_update_with_same_bank_soal_on_pelatihan_preserves_answers(): void
    {
        $user = User::factory()->create();
        $bankSoal = $this->makeBankSoal(2);
        $agenda = Agenda::factory()->create([
            'type' => 'pelatihan',
            'bank_soal_id' => $bankSoal->id,
            'event_time' => '09:00',
            'event_end_time' => '12:00',
        ]);
        $this->snapshotQuestions($agenda);

        $employee = Employee::factory()->create();
        $this->submitAnswers($agenda, $employee);

        $this->assertDatabaseCount('agenda_question_answers', 2);

        $response = $this->actingAs($user)->put(route('admin.agendas.update', $agenda), [
            'title' => 'Pelatihan Diperbarui',
            'event_date' => $agenda->event_date->format('Y-m-d'),
            'event_time' => '09:00',
            'event_end_time' => '12:00',
            'unit_id' => $agenda->unit_id,
            'event_leader_id' => $agenda->event_leader_id,
            'room_id' => $agenda->room_id,
            'type' => 'pelatihan',
            'bank_soal_id' => $bankSoal->id,
        ]);

        $response->assertRedirect(route('admin.agendas.index'));
        $this->assertDatabaseCount('agenda_questions', 2);
        $this->assertDatabaseCount('agenda_question_answers', 2);
    }

    // ===== Case 4: Mengubah unit — data bank soal / notulensi tidak berubah =====

    public function test_update_unit_on_diklat_preserves_questions_and_answers(): void
    {
        $user = User::factory()->create();
        $bankSoal = $this->makeBankSoal(3);
        $agenda = Agenda::factory()->create([
            'type' => 'diklat',
            'bank_soal_id' => $bankSoal->id,
            'event_time' => '09:00',
            'event_end_time' => '12:00',
        ]);
        $this->snapshotQuestions($agenda);

        $employee = Employee::factory()->create();
        $this->submitAnswers($agenda, $employee);

        $newUnit = Unit::factory()->create();

        $response = $this->actingAs($user)->put(route('admin.agendas.update', $agenda), [
            'title' => $agenda->title,
            'event_date' => $agenda->event_date->format('Y-m-d'),
            'event_time' => '09:00',
            'event_end_time' => '12:00',
            'unit_id' => $newUnit->id,
            'event_leader_id' => $agenda->event_leader_id,
            'room_id' => $agenda->room_id,
            'type' => 'diklat',
            'bank_soal_id' => $bankSoal->id,
        ]);

        $response->assertRedirect(route('admin.agendas.index'));
        $this->assertEquals($newUnit->id, $agenda->fresh()->unit_id);
        $this->assertDatabaseCount('agenda_questions', 3);
        $this->assertDatabaseCount('agenda_question_answers', 3);
    }

    public function test_update_unit_on_rapat_preserves_notulensi(): void
    {
        $user = User::factory()->create();
        $agenda = Agenda::factory()->create(['type' => 'rapat', 'event_time' => '09:00']);
        $agenda->notes()->createMany([
            ['topic' => 'Topik A', 'decision' => 'Keputusan A'],
            ['topic' => 'Topik B', 'decision' => 'Keputusan B'],
        ]);

        $this->assertDatabaseCount('agenda_notes', 2);

        $newUnit = Unit::factory()->create();

        $response = $this->actingAs($user)->put(route('admin.agendas.update', $agenda), [
            'title' => $agenda->title,
            'event_date' => $agenda->event_date->format('Y-m-d'),
            'event_time' => '09:00',
            'unit_id' => $newUnit->id,
            'event_leader_id' => $agenda->event_leader_id,
            'room_id' => $agenda->room_id,
            'type' => 'rapat',
        ]);

        $response->assertRedirect(route('admin.agendas.index'));
        $this->assertEquals($newUnit->id, $agenda->fresh()->unit_id);
        $this->assertDatabaseCount('agenda_notes', 2);
    }

    // ===== Case 5: Update tanpa upload file — file lama tidak terhapus =====

    public function test_update_without_new_letter_file_preserves_existing_path(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $storedPath = UploadedFile::fake()
            ->create('surat.pdf', 100, 'application/pdf')
            ->store('agenda-files/1', 'public');

        $agenda = Agenda::factory()->create([
            'type' => 'rapat',
            'event_time' => '09:00',
            'letter_file_path' => $storedPath,
        ]);

        $response = $this->actingAs($user)->put(route('admin.agendas.update', $agenda), [
            'title' => 'Judul Tanpa Ganti File',
            'event_date' => $agenda->event_date->format('Y-m-d'),
            'event_time' => '09:00',
            'unit_id' => $agenda->unit_id,
            'event_leader_id' => $agenda->event_leader_id,
            'room_id' => $agenda->room_id,
            'type' => 'rapat',
        ]);

        $response->assertRedirect(route('admin.agendas.index'));
        $this->assertEquals($storedPath, $agenda->fresh()->letter_file_path);
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_update_without_new_material_file_preserves_existing_path(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $storedPath = UploadedFile::fake()
            ->create('materi.pdf', 200, 'application/pdf')
            ->store('agenda-files/1', 'public');

        $agenda = Agenda::factory()->create([
            'type' => 'rapat',
            'event_time' => '09:00',
            'material_file_path' => $storedPath,
        ]);

        $response = $this->actingAs($user)->put(route('admin.agendas.update', $agenda), [
            'title' => 'Judul Tanpa Ganti Materi',
            'event_date' => $agenda->event_date->format('Y-m-d'),
            'event_time' => '09:00',
            'unit_id' => $agenda->unit_id,
            'event_leader_id' => $agenda->event_leader_id,
            'room_id' => $agenda->room_id,
            'type' => 'rapat',
        ]);

        $response->assertRedirect(route('admin.agendas.index'));
        $this->assertEquals($storedPath, $agenda->fresh()->material_file_path);
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_update_without_files_on_diklat_preserves_both_file_paths(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $bankSoal = $this->makeBankSoal(2);

        $letterPath = UploadedFile::fake()
            ->create('surat.pdf', 100, 'application/pdf')
            ->store('agenda-files/1', 'public');

        $materialPath = UploadedFile::fake()
            ->create('materi.pdf', 200, 'application/pdf')
            ->store('agenda-files/1', 'public');

        $agenda = Agenda::factory()->create([
            'type' => 'diklat',
            'bank_soal_id' => $bankSoal->id,
            'event_time' => '09:00',
            'event_end_time' => '12:00',
            'letter_file_path' => $letterPath,
            'material_file_path' => $materialPath,
        ]);
        $this->snapshotQuestions($agenda);

        $response = $this->actingAs($user)->put(route('admin.agendas.update', $agenda), [
            'title' => 'Diklat Tanpa Ganti File',
            'event_date' => $agenda->event_date->format('Y-m-d'),
            'event_time' => '09:00',
            'event_end_time' => '12:00',
            'unit_id' => $agenda->unit_id,
            'event_leader_id' => $agenda->event_leader_id,
            'room_id' => $agenda->room_id,
            'type' => 'diklat',
            'bank_soal_id' => $bankSoal->id,
        ]);

        $response->assertRedirect(route('admin.agendas.index'));
        $fresh = $agenda->fresh();
        $this->assertEquals($letterPath, $fresh->letter_file_path);
        $this->assertEquals($materialPath, $fresh->material_file_path);
        Storage::disk('public')->assertExists($letterPath);
        Storage::disk('public')->assertExists($materialPath);
    }

    // ===== Transactional integrity: store() =====

    public function test_store_diklat_creates_agenda_questions_atomically(): void
    {
        $user = User::factory()->create();
        $bankSoal = $this->makeBankSoal(4);

        $response = $this->actingAs($user)->post(
            route('admin.agendas.store'),
            $this->validDiklatData($bankSoal, ['title' => 'Diklat Atomik'])
        );

        $response->assertRedirect(route('admin.agendas.index'));
        $agenda = \App\Models\Agenda::where('title', 'Diklat Atomik')->firstOrFail();
        $this->assertDatabaseCount('agenda_questions', 4);
        $this->assertEquals(4, $agenda->agendaQuestions()->count());
    }

    public function test_store_rapat_creates_no_agenda_questions(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(
            route('admin.agendas.store'),
            $this->validRapatData(['title' => 'Rapat Tanpa Soal'])
        );

        $response->assertRedirect(route('admin.agendas.index'));
        $this->assertDatabaseCount('agenda_questions', 0);
    }

    public function test_store_diklat_with_presenters_creates_pivot_rows(): void
    {
        $user = User::factory()->create();
        $bankSoal = $this->makeBankSoal(2);
        $presenter1 = Employee::factory()->create();
        $presenter2 = Employee::factory()->create();

        $response = $this->actingAs($user)->post(
            route('admin.agendas.store'),
            $this->validDiklatData($bankSoal, [
                'title' => 'Diklat Dengan Pemateri',
                'presenter_ids' => [$presenter1->id, $presenter2->id],
            ])
        );

        $response->assertRedirect(route('admin.agendas.index'));
        $agenda = \App\Models\Agenda::where('title', 'Diklat Dengan Pemateri')->firstOrFail();
        $this->assertDatabaseCount('agenda_presenters', 2);
        $this->assertDatabaseHas('agenda_presenters', [
            'agenda_id' => $agenda->id,
            'employee_id' => $presenter1->id,
            'sort_order' => 1,
        ]);
        $this->assertDatabaseHas('agenda_presenters', [
            'agenda_id' => $agenda->id,
            'employee_id' => $presenter2->id,
            'sort_order' => 2,
        ]);
    }

    // ===== Transactional integrity: update() =====

    public function test_update_changing_bank_soal_replaces_questions_and_clears_answers(): void
    {
        $user = User::factory()->create();
        $oldBankSoal = $this->makeBankSoal(3);
        $newBankSoal = $this->makeBankSoal(5);

        $agenda = Agenda::factory()->create([
            'type' => 'diklat',
            'bank_soal_id' => $oldBankSoal->id,
            'event_time' => '09:00',
            'event_end_time' => '12:00',
        ]);
        $this->snapshotQuestions($agenda);

        $employee = Employee::factory()->create();
        $this->submitAnswers($agenda, $employee);

        $this->assertDatabaseCount('agenda_questions', 3);
        $this->assertDatabaseCount('agenda_question_answers', 3);

        $response = $this->actingAs($user)->put(route('admin.agendas.update', $agenda), [
            'title' => $agenda->title,
            'event_date' => $agenda->event_date->format('Y-m-d'),
            'event_time' => '09:00',
            'event_end_time' => '12:00',
            'unit_id' => $agenda->unit_id,
            'event_leader_id' => $agenda->event_leader_id,
            'room_id' => $agenda->room_id,
            'type' => 'diklat',
            'bank_soal_id' => $newBankSoal->id,
        ]);

        $response->assertRedirect(route('admin.agendas.index'));
        $this->assertDatabaseCount('agenda_questions', 5);
        $this->assertDatabaseCount('agenda_question_answers', 0);
    }

    public function test_update_diklat_to_rapat_deletes_questions_and_nulls_bank_soal(): void
    {
        $user = User::factory()->create();
        $bankSoal = $this->makeBankSoal(3);

        $agenda = Agenda::factory()->create([
            'type' => 'diklat',
            'bank_soal_id' => $bankSoal->id,
            'event_time' => '09:00',
            'event_end_time' => '12:00',
        ]);
        $this->snapshotQuestions($agenda);

        $employee = Employee::factory()->create();
        $this->submitAnswers($agenda, $employee);

        $this->assertDatabaseCount('agenda_questions', 3);

        $response = $this->actingAs($user)->put(route('admin.agendas.update', $agenda), [
            'title' => $agenda->title,
            'event_date' => $agenda->event_date->format('Y-m-d'),
            'event_time' => '09:00',
            'unit_id' => $agenda->unit_id,
            'event_leader_id' => $agenda->event_leader_id,
            'room_id' => $agenda->room_id,
            'type' => 'rapat',
        ]);

        $response->assertRedirect(route('admin.agendas.index'));
        $this->assertDatabaseCount('agenda_questions', 0);
        $this->assertDatabaseCount('agenda_question_answers', 0);
        $this->assertNull($agenda->fresh()->bank_soal_id);
    }

    public function test_update_rapat_to_diklat_creates_questions_from_template(): void
    {
        $user = User::factory()->create();
        $bankSoal = $this->makeBankSoal(4);

        $agenda = Agenda::factory()->create([
            'type' => 'rapat',
            'event_time' => '09:00',
        ]);

        $this->assertDatabaseCount('agenda_questions', 0);

        $response = $this->actingAs($user)->put(route('admin.agendas.update', $agenda), [
            'title' => $agenda->title,
            'event_date' => $agenda->event_date->format('Y-m-d'),
            'event_time' => '09:00',
            'event_end_time' => '12:00',
            'unit_id' => $agenda->unit_id,
            'event_leader_id' => $agenda->event_leader_id,
            'room_id' => $agenda->room_id,
            'type' => 'diklat',
            'bank_soal_id' => $bankSoal->id,
        ]);

        $response->assertRedirect(route('admin.agendas.index'));
        $this->assertDatabaseCount('agenda_questions', 4);
        $this->assertEquals(4, $agenda->fresh()->agendaQuestions()->count());
    }

    public function test_update_leaving_rapat_deletes_notes(): void
    {
        $user = User::factory()->create();
        $bankSoal = $this->makeBankSoal(2);

        $agenda = Agenda::factory()->create([
            'type' => 'rapat',
            'event_time' => '09:00',
        ]);
        $agenda->notes()->createMany([
            ['topic' => 'Topik A', 'decision' => 'Keputusan A'],
            ['topic' => 'Topik B', 'decision' => 'Keputusan B'],
        ]);

        $this->assertDatabaseCount('agenda_notes', 2);

        $response = $this->actingAs($user)->put(route('admin.agendas.update', $agenda), [
            'title' => $agenda->title,
            'event_date' => $agenda->event_date->format('Y-m-d'),
            'event_time' => '09:00',
            'event_end_time' => '12:00',
            'unit_id' => $agenda->unit_id,
            'event_leader_id' => $agenda->event_leader_id,
            'room_id' => $agenda->room_id,
            'type' => 'diklat',
            'bank_soal_id' => $bankSoal->id,
        ]);

        $response->assertRedirect(route('admin.agendas.index'));
        $this->assertDatabaseCount('agenda_notes', 0);
    }
}
