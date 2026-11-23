<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaExternalMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'agenda_id',
        'name',
        'organization',
        'signature_image_path',
    ];

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class);
    }
}
