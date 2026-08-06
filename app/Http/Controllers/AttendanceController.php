<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\AgendaQuestionAnswer;
use App\Models\Employee;
use App\Services\SignatureStorageService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function show(Agenda $agenda)
    {
        abort_unless($agenda->event_date->isToday(), 404);

        $agenda->load(["room", "agendaQuestions"]);

        $signedEmployees = $agenda
            ->employees()
            ->whereNotNull("agenda_employee.signature_image_path")
            ->get();

        $signedEmployeeIds = $signedEmployees->pluck("id")->toArray();

        $allEmployees = Employee::with("unit")->orderBy("full_name")->get();

        $employeesJson = $allEmployees->map(function ($e) use (
            $signedEmployeeIds,
        ) {
            return [
                "id" => $e->id,
                "name" => $e->full_name,
                "position" => $e->job_position,
                "organization" => $e->unit->name ?? "-",
                "signed_at" => in_array($e->id, $signedEmployeeIds),
            ];
        });

        $attendeesJson = $signedEmployees->load("unit")->map(function ($e) {
            return [
                "id" => $e->id,
                "name" => $e->full_name,
                "position" => $e->job_position,
                "organization" => $e->unit->name ?? "-",
                "signature_url" => asset(
                    "storage/" . $e->pivot->signature_image_path,
                ),
                "signed_at" => $e->pivot->created_at?->format("H:i"),
            ];
        });

        // Pretest data for diklat/pelatihan agendas
        $questionsJson = collect();
        $pretestCompletedIds = [];

        if ($agenda->allowsQuiz() && $agenda->agendaQuestions->count() > 0) {
            $questionsJson = $agenda->agendaQuestions->map(
                fn($q) => [
                    "id" => $q->id,
                    "question_text" => $q->question_text,
                    "option_a" => $q->option_a,
                    "option_b" => $q->option_b,
                    "option_c" => $q->option_c,
                    "option_d" => $q->option_d,
                    "option_e" => $q->option_e,
                ],
            );

            $pretestCompletedIds = AgendaQuestionAnswer::where(
                "agenda_id",
                $agenda->id,
            )
                ->where("quiz_type", "pretest")
                ->select("employee_id")
                ->distinct()
                ->pluck("employee_id")
                ->toArray();
        }

        return view(
            "attendance.show",
            compact(
                "agenda",
                "employeesJson",
                "attendeesJson",
                "questionsJson",
                "pretestCompletedIds",
            ),
        );
    }

    public function sign(
        Request $request,
        Agenda $agenda,
        SignatureStorageService $signatureService,
    ) {
        abort_unless($agenda->event_date->isToday(), 404);

        $request->validate([
            "employee_id" => "required|exists:employees,id",
            "signature" => "required|string",
        ]);

        $pivot = $agenda
            ->employees()
            ->where("employee_id", $request->employee_id)
            ->first();

        if ($pivot && $pivot->pivot->signature_image_path) {
            return response()->json(
                [
                    "message" => "Anda sudah melakukan absensi.",
                ],
                422,
            );
        }

        $signaturePath = $signatureService->storeBase64($request->signature);

        if ($pivot) {
            $agenda->employees()->updateExistingPivot($request->employee_id, [
                "signature_image_path" => $signaturePath,
            ]);
        } else {
            $agenda->employees()->attach($request->employee_id, [
                "signature_image_path" => $signaturePath,
            ]);
        }

        $employee = Employee::with("unit")->find($request->employee_id);

        // Check if this agenda has quiz (diklat/pelatihan) and pretest not yet done
        $showPretest = false;
        if ($agenda->allowsQuiz() && $agenda->agendaQuestions()->count() > 0) {
            $pretestDone = AgendaQuestionAnswer::where("agenda_id", $agenda->id)
                ->where("employee_id", $request->employee_id)
                ->where("quiz_type", "pretest")
                ->exists();
            $showPretest = !$pretestDone;
        }

        return response()->json([
            "message" => "Absensi berhasil disimpan.",
            "show_pretest" => $showPretest,
            "attendee" => [
                "id" => $employee->id,
                "name" => $employee->full_name,
                "position" => $employee->job_position,
                "organization" => $employee->unit->name ?? "-",
                "signature_url" => asset("storage/" . $signaturePath),
                "signed_at" => now()->format("H:i"),
            ],
        ]);
    }

    public function storePretest(Request $request, Agenda $agenda)
    {
        abort_unless($agenda->event_date->isToday(), 404);
        abort_unless($agenda->allowsQuiz(), 403);
        abort_if($agenda->agendaQuestions()->count() === 0, 404);

        $request->validate([
            "employee_id" => "required|exists:employees,id",
            "answers" => "required|array",
            "answers.*" => "required|in:a,b,c,d,e",
        ]);

        $employeeId = $request->employee_id;

        $hasAttendance = $agenda
            ->employees()
            ->wherePivot("employee_id", $employeeId)
            ->wherePivotNotNull("signature_image_path")
            ->exists();
        abort_unless($hasAttendance, 403);

        // Check if pretest already answered
        $existing = AgendaQuestionAnswer::where("agenda_id", $agenda->id)
            ->where("employee_id", $employeeId)
            ->where("quiz_type", "pretest")
            ->exists();

        if ($existing) {
            return response()->json(
                [
                    "message" => "Anda sudah mengerjakan pretest.",
                ],
                422,
            );
        }

        $questions = $agenda->agendaQuestions()->get()->keyBy("id");
        $correct = 0;
        $total = $questions->count();
        $rows = [];

        foreach ($request->answers as $questionId => $selectedOption) {
            $question = $questions->get($questionId);
            if (!$question) {
                continue;
            }

            $isCorrect = $question->correct_option === $selectedOption;
            if ($isCorrect) {
                $correct++;
            }

            $rows[] = [
                "agenda_id" => $agenda->id,
                "employee_id" => $employeeId,
                "agenda_question_id" => $question->id,
                "selected_option" => $selectedOption,
                "is_correct" => $isCorrect,
                "quiz_type" => "pretest",
                "created_at" => now(),
                "updated_at" => now(),
            ];
        }

        try {
            AgendaQuestionAnswer::insert($rows);
        } catch (QueryException $e) {
            if (
                str_contains($e->getMessage(), "unique") ||
                str_contains($e->getMessage(), "Unique")
            ) {
                return response()->json(
                    ["message" => "Anda sudah mengerjakan pretest."],
                    422,
                );
            }
            throw $e;
        }

        return response()->json([
            "message" => "Pretest berhasil disimpan.",
            "correct" => $correct,
            "total" => $total,
        ]);
    }
}
