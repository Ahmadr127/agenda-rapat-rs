<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaQuestionAnswer extends Model
{
    protected $fillable = [
        'agenda_id',
        'employee_id',
        'agenda_question_id',
        'selected_option',
        'is_correct',
        'quiz_type',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
        ];
    }

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function agendaQuestion(): BelongsTo
    {
        return $this->belongsTo(AgendaQuestion::class);
    }
}
