<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Agenda extends Model
{
    use HasFactory;

    protected $fillable = [
        "title",
        "description",
        "unit_id",
        "event_date",
        "event_time",
        "event_end_time",
        "event_leader_id",
        "room_id",
        "letter_file_path",
        "material_file_path",
        "letter_number",
        "letter_recipients",
        "type",
        "bank_soal_id",
    ];

    protected function casts(): array
    {
        return [
            "event_date" => "date",
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function eventLeader(): BelongsTo
    {
        return $this->belongsTo(Employee::class, "event_leader_id");
    }

    public function presenters(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, "agenda_presenters")
            ->withPivot("sort_order")
            ->withTimestamps()
            ->orderBy("agenda_presenters.sort_order");
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, "agenda_employee")
            ->withPivot("signature_image_path")
            ->withTimestamps();
    }

    public function notes(): HasMany
    {
        return $this->hasMany(AgendaNote::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(AgendaImage::class);
    }

    public function agendaQuestions(): HasMany
    {
        return $this->hasMany(AgendaQuestion::class);
    }

    public function bankSoal(): BelongsTo
    {
        return $this->belongsTo(BankSoal::class);
    }

    public function allowsNotes(): bool
    {
        return $this->type === "rapat";
    }

    public function allowsQuiz(): bool
    {
        return in_array($this->type, ["diklat", "pelatihan"]);
    }

    /**
     * Daftar hadir boleh diubah (tambah/hapus) selama agenda belum selesai.
     * Selesai = tanggal lewat, atau di hari-H jam selesai sudah lewat.
     * Rapat tanpa jam selesai tetap bisa diubah sampai akhir hari.
     */
    public function isAttendanceEditable(): bool
    {
        if (! $this->event_date) {
            return true;
        }

        $today = now()->startOfDay();

        if ($this->event_date->isBefore($today)) {
            return false;
        }

        if ($this->event_date->isAfter($today)) {
            return true;
        }

        if (! empty($this->event_end_time)) {
            $end = Carbon::parse($this->event_date->toDateString().' '.$this->event_end_time);

            return now()->lessThanOrEqualTo($end);
        }

        return true;
    }
}
