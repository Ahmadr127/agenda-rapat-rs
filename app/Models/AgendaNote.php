<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaNote extends Model
{
    use HasFactory;

    protected $fillable = ["agenda_id", "topic", "decision", "remarks", "pj"];

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class);
    }
}
