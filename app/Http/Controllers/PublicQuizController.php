<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\AgendaQuestionAnswer;
use App\Models\Employee;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PublicQuizController extends Controller
{
    public function show(Agenda $agenda)
    {
        abort_unless($agenda->event_date->isToday(), 404);
        abort_if($agenda->agendaQuestions()->count() === 0, 404);

        $agenda->load(['room', 'agendaQuestions']);

        $employees = Employee::with('unit')->orderBy('full_name')->get();

        $employeesJson = $employees->map(
            fn ($e) => [
                'id' => $e->id,
                'name' => $e->full_name,
                'position' => $e->job_position,
                'organization' => $e->unit->name ?? '-',
            ],
        );

        $questionsJson = $agenda->agendaQuestions->map(
            fn ($q) => [
                'id' => $q->id,
                'question_text' => $q->question_text,
                'option_a' => $q->option_a,
                'option_b' => $q->option_b,
                'option_c' => $q->option_c,
                'option_d' => $q->option_d,
                'option_e' => $q->option_e,
            ],
        );

        // Employee IDs that already completed pretest
        $pretestCompletedIds = AgendaQuestionAnswer::where(
            'agenda_id',
            $agenda->id,
        )
            ->where('quiz_type', 'pretest')
            ->select('employee_id')
            ->distinct()
            ->pluck('employee_id')
            ->toArray();

        // Employee IDs that already completed posttest
        $posttestCompletedIds = AgendaQuestionAnswer::where(
            'agenda_id',
            $agenda->id,
        )
            ->where('quiz_type', 'posttest')
            ->select('employee_id')
            ->distinct()
            ->pluck('employee_id')
            ->toArray();

        return view(
            'attendance.quiz',
            compact(
                'agenda',
                'employeesJson',
                'questionsJson',
                'pretestCompletedIds',
                'posttestCompletedIds',
            ),
        );
    }

    public function store(Request $request, Agenda $agenda)
    {
        abort_unless($agenda->event_date->isToday(), 404);
        abort_if($agenda->agendaQuestions()->count() === 0, 404);

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'answers' => 'required|array',
            'answers.*' => 'required|in:a,b,c,d,e',
        ]);

        $employeeId = $request->employee_id;

        // Must have completed pretest first
        $pretestDone = AgendaQuestionAnswer::where('agenda_id', $agenda->id)
            ->where('employee_id', $employeeId)
            ->where('quiz_type', 'pretest')
            ->exists();

        if (! $pretestDone) {
            return response()->json(
                [
                    'message' => 'Anda harus mengerjakan pretest terlebih dahulu.',
                ],
                422,
            );
        }

        // Check if posttest already answered
        $existing = AgendaQuestionAnswer::where('agenda_id', $agenda->id)
            ->where('employee_id', $employeeId)
            ->where('quiz_type', 'posttest')
            ->exists();

        if ($existing) {
            return response()->json(
                [
                    'message' => 'Anda sudah mengerjakan posttest.',
                ],
                422,
            );
        }

        $questions = $agenda->agendaQuestions()->get()->keyBy('id');
        $correct = 0;
        $total = $questions->count();
        $rows = [];

        foreach ($request->answers as $questionId => $selectedOption) {
            $question = $questions->get($questionId);
            if (! $question) {
                continue;
            }

            $isCorrect = $question->correct_option === $selectedOption;
            if ($isCorrect) {
                $correct++;
            }

            $rows[] = [
                'agenda_id' => $agenda->id,
                'employee_id' => $employeeId,
                'agenda_question_id' => $question->id,
                'selected_option' => $selectedOption,
                'is_correct' => $isCorrect,
                'quiz_type' => 'posttest',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        try {
            AgendaQuestionAnswer::insert($rows);
        } catch (QueryException $e) {
            if (
                str_contains($e->getMessage(), 'unique') ||
                str_contains($e->getMessage(), 'Unique')
            ) {
                return response()->json(
                    ['message' => 'Anda sudah mengerjakan posttest.'],
                    422,
                );
            }
            throw $e;
        }

        Session::flash('success', 'Posttest berhasil disimpan!');

        return redirect()->route('attendance.show', $agenda);
    }
}
