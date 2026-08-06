<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
