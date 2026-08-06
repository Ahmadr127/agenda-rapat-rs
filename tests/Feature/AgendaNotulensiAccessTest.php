<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\AgendaQuestion;
use App\Models\BankSoal;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AgendaNotulensiAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createActiveAgenda(string $type): Agenda
    {
        $attrs = ['type' => $type, 'event_date' => today()];

        if (in_array($type, ['diklat', 'pelatihan'])) {
            $bankSoal = BankSoal::factory()->create();
            Question::factory()->count(3)->create(['bank_soal_id' => $bankSoal->id]);
            $attrs['bank_soal_id'] = $bankSoal->id;
        }

        $agenda = Agenda::factory()->create($attrs);

        if ($agenda->bank_soal_id) {
            $questions = Question::where('bank_soal_id', $agenda->bank_soal_id)->get();
            $agenda->agendaQuestions()->createMany(
                $questions->map->only(['question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'option_e', 'correct_option'])->toArray()
            );
        }

        return $agenda;
    }

    private function noteData(): array
    {
        return [
            'topic' => 'Topik Pembahasan',
            'decision' => 'Keputusan Rapat',
            'remarks' => 'Keterangan tambahan',
        ];
    }

    // -- Public storeNote endpoint --

    public function test_rapat_agenda_allows_note_creation(): void
    {
        $agenda = $this->createActiveAgenda('rapat');

        $response = $this->post(route('agenda.input.note', $agenda), $this->noteData());

        $response->assertRedirect(route('agenda.input', $agenda));
        $this->assertDatabaseHas('agenda_notes', [
            'agenda_id' => $agenda->id,
            'topic' => 'Topik Pembahasan',
        ]);
    }

    public function test_diklat_agenda_rejects_note_creation(): void
    {
        $agenda = $this->createActiveAgenda('diklat');

        $response = $this->post(route('agenda.input.note', $agenda), $this->noteData());

        $response->assertForbidden();
        $this->assertDatabaseCount('agenda_notes', 0);
    }

    public function test_pelatihan_agenda_rejects_note_creation(): void
    {
        $agenda = $this->createActiveAgenda('pelatihan');

        $response = $this->post(route('agenda.input.note', $agenda), $this->noteData());

        $response->assertForbidden();
        $this->assertDatabaseCount('agenda_notes', 0);
    }

    // -- Public input page visibility --

    public function test_rapat_agenda_input_page_shows_notulensi_tab(): void
    {
        $agenda = $this->createActiveAgenda('rapat');

        $response = $this->get(route('agenda.input', $agenda));

        $response->assertOk();
        $response->assertSee('Notulensi');
    }

    public function test_diklat_agenda_input_page_hides_notulensi_tab(): void
    {
        $agenda = $this->createActiveAgenda('diklat');

        $response = $this->get(route('agenda.input', $agenda));

        $response->assertOk();
        $response->assertDontSee('Notulensi');
        $response->assertSee('Foto');
    }

    public function test_pelatihan_agenda_input_page_hides_notulensi_tab(): void
    {
        $agenda = $this->createActiveAgenda('pelatihan');

        $response = $this->get(route('agenda.input', $agenda));

        $response->assertOk();
        $response->assertDontSee('Notulensi');
    }

    // -- Image upload remains available for all types --

    public function test_diklat_agenda_allows_image_upload(): void
    {
        Storage::fake('public');
        $agenda = $this->createActiveAgenda('diklat');

        $response = $this->postJson(route('agenda.input.image', $agenda), [
            'images' => [UploadedFile::fake()->image('foto.jpg')],
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true, 'count' => 1]);
        $this->assertDatabaseCount('agenda_images', 1);
    }

    // -- Admin show page --

    public function test_admin_show_displays_notulensi_for_rapat(): void
    {
        $user = User::factory()->create();
        $agenda = $this->createActiveAgenda('rapat');

        $response = $this->actingAs($user)->get(route('admin.agendas.show', $agenda));

        $response->assertOk();
        $response->assertSee('Notulensi Rapat');
    }

    public function test_admin_show_hides_notulensi_for_diklat(): void
    {
        $user = User::factory()->create();
        $agenda = $this->createActiveAgenda('diklat');

        $response = $this->actingAs($user)->get(route('admin.agendas.show', $agenda));

        $response->assertOk();
        $response->assertDontSee('Notulensi Rapat');
    }

    public function test_admin_show_hides_notulensi_for_pelatihan(): void
    {
        $user = User::factory()->create();
        $agenda = $this->createActiveAgenda('pelatihan');

        $response = $this->actingAs($user)->get(route('admin.agendas.show', $agenda));

        $response->assertOk();
        $response->assertDontSee('Notulensi Rapat');
    }

    public function test_admin_show_displays_bank_soal_for_diklat(): void
    {
        $user = User::factory()->create();
        $agenda = $this->createActiveAgenda('diklat');

        $response = $this->actingAs($user)->get(route('admin.agendas.show', $agenda));

        $response->assertOk();
        $response->assertSee('Bank Soal');
    }
}
