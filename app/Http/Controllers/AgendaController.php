<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\AgendaQuestionAnswer;
use App\Models\Employee;
use App\Models\Question;
use App\Models\Room;
use App\Models\Unit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Http\Requests\AgendaRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AgendaController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));
        $type = $request->input('type');
        $roomId = $request->input('room_id');
        $unitId = $request->input('unit_id');
        $eventLeaderId = $request->input('event_leader_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $operator = $this->searchOperator();

        $agendas = Agenda::with(['room', 'unit', 'eventLeader'])
            ->latest()
            ->when($q !== '', fn ($query) => $query->where('title', $operator, "%{$q}%"))
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($roomId, fn ($query) => $query->where('room_id', $roomId))
            ->when($unitId, fn ($query) => $query->where('unit_id', $unitId))
            ->when($eventLeaderId, fn ($query) => $query->where('event_leader_id', $eventLeaderId))
            ->when($dateFrom, fn ($query) => $query->whereDate('event_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('event_date', '<=', $dateTo))
            ->paginate(10)
            ->withQueryString();

        $typeLabels = ['rapat' => 'Rapat', 'diklat' => 'Diklat', 'pelatihan' => 'Pelatihan'];
        $typeLabel = $type ? ($typeLabels[$type] ?? null) : null;
        $selectedRoom = $roomId ? Room::find($roomId) : null;
        $selectedUnit = $unitId ? Unit::find($unitId) : null;
        $selectedEventLeader = $eventLeaderId ? Employee::find($eventLeaderId) : null;

        return view('admin.agendas.index', compact(
            'agendas', 'q', 'type', 'typeLabel', 'selectedRoom', 'selectedUnit',
            'selectedEventLeader', 'dateFrom', 'dateTo'
        ));
    }

    public function create()
    {
        $userUnit = auth()->user()->employee?->unit;
        return view("admin.agendas.create", compact('userUnit'));
    }

    public function searchTypes(Request $request)
    {
        $types = collect([
            ["id" => "rapat", "name" => "Rapat"],
            ["id" => "diklat", "name" => "Diklat"],
            ["id" => "pelatihan", "name" => "Pelatihan"],
        ]);

        if ($request->filled("id")) {
            $type = $types->firstWhere("id", (string) $request->id);

            return response()->json([
                "items" => $type ? [$type] : [],
                "has_more" => false,
            ]);
        }

        $search = strtolower(trim((string) $request->input("q")));

        $filtered = $types
            ->filter(function (array $type) use ($search) {
                if ($search === "") {
                    return true;
                }

                return str_contains(strtolower($type["id"]), $search) ||
                    str_contains(strtolower($type["name"]), $search);
            })
            ->values();

        $perPage = 10;
        $page = max(1, (int) $request->input("page", 1));
        $items = $filtered->forPage($page, $perPage)->values();

        return response()->json([
            "items" => $items,
            "has_more" => $filtered->count() > $page * $perPage,
        ]);
    }

    public function store(AgendaRequest $request)
    {
        $validated = $request->validated();
        $presenterIds = $this->extractPresenterIds($validated);
        $agendaData = $this->buildAgendaData($validated);

        DB::transaction(function () use ($agendaData, $presenterIds, $request) {
            $agenda = Agenda::create($agendaData);
            $agenda->update($this->storeAgendaFiles($request, $agenda));
            $this->syncAgendaPresenters($agenda, $presenterIds);
            $this->syncAgendaQuestionsFromTemplate($agenda);
        });

        return redirect()
            ->route("admin.agendas.index")
            ->with("success", "Agenda berhasil dibuat.");
    }

    public function show(Agenda $agenda)
    {
        $agenda->load([
            "room",
            "unit",
            "eventLeader",
            "presenters.unit",
            "employees.unit",
            "notes",
            "images",
            "agendaQuestions",
            "bankSoal",
        ]);

        $quizComparison = $this->buildQuizComparison($agenda);
        $quizStats = null;

        if ($quizComparison->isNotEmpty()) {
            $withPre = $quizComparison->filter(
                fn($c) => $c["pre_score"] !== null,
            );
            $withPost = $quizComparison->filter(
                fn($c) => $c["post_score"] !== null,
            );
            $withBoth = $quizComparison->filter(
                fn($c) => $c["improvement"] !== null,
            );

            $quizStats = [
                "pretest_count" => $withPre->count(),
                "posttest_count" => $withPost->count(),
                "avg_pretest" =>
                    $withPre->count() > 0
                        ? round($withPre->avg("pre_score"))
                        : 0,
                "avg_posttest" =>
                    $withPost->count() > 0
                        ? round($withPost->avg("post_score"))
                        : 0,
                "avg_improvement" =>
                    $withBoth->count() > 0
                        ? round($withBoth->avg("improvement"))
                        : null,
            ];
        }

        return view(
            "admin.agendas.show",
            compact("agenda", "quizComparison", "quizStats"),
        );
    }

    public function edit(Agenda $agenda)
    {
        $agenda->load([
            "room",
            "unit",
            "eventLeader",
            "presenters",
            "bankSoal",
        ]);

        $userUnit = auth()->user()->employee?->unit;
        return view("admin.agendas.edit", compact("agenda", "userUnit"));
    }

    public function update(AgendaRequest $request, Agenda $agenda)
    {
        $validated = $request->validated();
        $presenterIds = $this->extractPresenterIds($validated);
        $agendaData = $this->buildAgendaData($validated);
        $agendaData = array_merge(
            $agendaData,
            $this->storeAgendaFiles($request, $agenda, true),
        );

        $oldType = $agenda->type;
        $oldBankSoalId = $agenda->bank_soal_id;

        DB::transaction(function () use ($agenda, $agendaData, $presenterIds, $oldType, $oldBankSoalId) {
            $agenda->update($agendaData);

            if ($agenda->type !== "rapat" && $oldType === "rapat") {
                $agenda->notes()->delete();
            }

            $this->syncAgendaPresenters($agenda, $presenterIds);
            $this->syncAgendaQuestionsFromTemplate($agenda, $oldType, $oldBankSoalId);
        });

        return redirect()
            ->route("admin.agendas.index")
            ->with("success", "Agenda berhasil diperbarui.");
    }

    public function exportCsv()
    {
        $headers = [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => 'attachment; filename="agendas.csv"',
        ];

        $callback = function () {
            $file = fopen("php://output", "w");

            // BOM for Excel UTF-8 compatibility
            fwrite($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                "Judul",
                "Deskripsi",
                "Tipe",
                "Tanggal",
                "Pukul Mulai",
                "Pukul Selesai",
                "Ruangan",
                "Unit",
                "Pimpinan Agenda",
            ]);

            foreach (
                Agenda::with(["unit", "eventLeader"])
                    ->latest()
                    ->cursor()
                as $agenda
            ) {
                fputcsv($file, [
                    $agenda->title,
                    $this->sanitizeCsvText($agenda->description),
                    $agenda->type,
                    $agenda->event_date->format("Y-m-d"),
                    $agenda->event_time,
                    $agenda->event_end_time ?? "-",
                    $agenda->room?->name ?? "-",
                    $agenda->unit?->name ?? "-",
                    $agenda->eventLeader?->full_name ?? "-",
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Agenda $agenda)
    {
        $agenda->load([
            "room",
            "unit",
            "eventLeader",
            "presenters.unit",
            "employees.unit",
            "notes",
            "images",
            "agendaQuestions",
        ]);

        // Convert signature images to base64 for embedding in PDF
        $signatureImages = [];
        foreach ($agenda->employees as $employee) {
            if ($employee->pivot->signature_image_path) {
                $path = Storage::disk("public")->path(
                    $employee->pivot->signature_image_path,
                );
                if (file_exists($path)) {
                    $signatureImages[$employee->id] =
                        "data:image/png;base64," .
                        base64_encode(file_get_contents($path));
                }
            }
        }

        // Convert agenda images to base64 for embedding in PDF
        $agendaImages = [];
        foreach ($agenda->images as $image) {
            $path = Storage::disk("public")->path($image->image_path);
            if (file_exists($path)) {
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                $agendaImages[$image->id] =
                    "data:image/" .
                    $ext .
                    ";base64," .
                    base64_encode(file_get_contents($path));
            }
        }

        $quizComparison = $this->buildQuizComparison($agenda);

        // Generate separate PDFs for each content section
        $contentSections = [
            "attendance" => "Daftar Kehadiran",
        ];

        if ($agenda->allowsQuiz() && $agenda->agendaQuestions->count() > 0) {
            $contentSections["quiz"] = "Hasil Pretest & Posttest";
        }

        if ($agenda->allowsNotes()) {
            $contentSections["notes"] = "Notulensi Rapat";
        }

        $contentSections["photos"] = "Dokumentasi Foto";

        $tempPaths = [];
        foreach ($contentSections as $sectionKey => $sectionLabel) {
            $section = $sectionKey;
            $sectionPdf = Pdf::loadView(
                "admin.agendas.export-pdf",
                compact(
                    "agenda",
                    "signatureImages",
                    "agendaImages",
                    "section",
                    "quizComparison",
                ),
            )->setPaper("a4", "portrait");

            $tmpPath =
                tempnam(sys_get_temp_dir(), "agenda_{$sectionKey}_") . ".pdf";
            file_put_contents($tmpPath, $sectionPdf->output());
            $tempPaths[] = $tmpPath;
        }

        // Collect PDF files to merge in order:
        // Surat Undangan → Materi → Daftar Kehadiran → Notulensi → Dokumentasi
        $pdfFiles = [];

        if ($agenda->letter_file_path) {
            $letterPath = Storage::disk("public")->path(
                $agenda->letter_file_path,
            );
            if (
                file_exists($letterPath) &&
                strtolower(pathinfo($letterPath, PATHINFO_EXTENSION)) === "pdf"
            ) {
                $pdfFiles[] = ["path" => $letterPath, "type" => "letter"];
            }
        }

        if ($agenda->material_file_path) {
            $materialPath = Storage::disk("public")->path(
                $agenda->material_file_path,
            );
            if (
                file_exists($materialPath) &&
                strtolower(pathinfo($materialPath, PATHINFO_EXTENSION)) ===
                    "pdf"
            ) {
                $pdfFiles[] = ["path" => $materialPath, "type" => "material"];
            }
        }

        // Add content section PDFs
        $sectionKeys = array_keys($contentSections);
        foreach ($sectionKeys as $idx => $sectionKey) {
            $pdfFiles[] = ["path" => $tempPaths[$idx], "type" => $sectionKey];
        }

        // Prepare header info
        $headerTitle = $agenda->title;
        $headerDate = $agenda->event_date->translatedFormat("l, d F Y");
        $headerTime =
            $agenda->event_time .
            ($agenda->event_end_time ? " - " . $agenda->event_end_time : "") .
            " WIB";
        $headerRoom = $agenda->room->room_name ?? "-";
        $headerUnit = $agenda->unit?->name ?? "-";
        $headerLeader = $agenda->eventLeader?->full_name ?? "-";
        $headerType = ucfirst($agenda->type);

        // Sub-header labels per type
        $subHeaders = [
            "letter" => "Surat Undangan",
            "material" => "Materi Agenda",
            "attendance" => "Daftar Kehadiran",
            "quiz" => "Hasil Pretest & Posttest",
            "notes" => "Notulensi Rapat",
            "photos" => "Dokumentasi Foto",
        ];

        // Merge all PDFs using FPDI — every page gets the same header
        $merger = new \setasign\Fpdi\Fpdi();
        $merger->SetAutoPageBreak(false);

        foreach ($pdfFiles as $fileInfo) {
            $pageCount = $merger->setSourceFile($fileInfo["path"]);
            for ($p = 1; $p <= $pageCount; $p++) {
                $tpl = $merger->importPage($p);
                $size = $merger->getTemplateSize($tpl);

                $headerHeight = 36; // mm reserved for header + sub-header
                $pageWidth = $size["width"];
                $pageHeight = $size["height"];

                $merger->AddPage($size["orientation"], [
                    $pageWidth,
                    $pageHeight,
                ]);

                // -- Draw header --
                // Title
                $merger->SetFont("Helvetica", "B", 10);
                $merger->SetTextColor(0, 0, 0);
                $merger->SetXY(12, 5);
                $merger->Cell($pageWidth - 24, 5, $headerTitle, 0, 1, "L");

                // Info rows
                $merger->SetTextColor(80, 80, 80);
                $colWidth = ($pageWidth - 24) / 2;

                // Row 1: Tanggal + Unit
                $merger->SetXY(12, 12);
                $merger->SetFont("Helvetica", "B", 7.5);
                $merger->Cell(22, 3.5, "Tanggal", 0, 0);
                $merger->Cell(4, 3.5, ":", 0, 0);
                $merger->SetFont("Helvetica", "", 7.5);
                $merger->Cell($colWidth - 26, 3.5, $headerDate, 0, 0);

                $merger->SetFont("Helvetica", "B", 7.5);
                $merger->Cell(26, 3.5, "Unit", 0, 0);
                $merger->Cell(4, 3.5, ":", 0, 0);
                $merger->SetFont("Helvetica", "", 7.5);
                $merger->Cell($colWidth - 30, 3.5, $headerUnit, 0, 1);

                // Row 2: Waktu + Pimpinan Agenda
                $merger->SetXY(12, 16);
                $merger->SetFont("Helvetica", "B", 7.5);
                $merger->Cell(22, 3.5, "Waktu", 0, 0);
                $merger->Cell(4, 3.5, ":", 0, 0);
                $merger->SetFont("Helvetica", "", 7.5);
                $merger->Cell($colWidth - 26, 3.5, $headerTime, 0, 0);

                $merger->SetFont("Helvetica", "B", 7.5);
                $merger->Cell(26, 3.5, "Pimpinan Agenda", 0, 0);
                $merger->Cell(4, 3.5, ":", 0, 0);
                $merger->SetFont("Helvetica", "", 7.5);
                $merger->Cell($colWidth - 30, 3.5, $headerLeader, 0, 1);

                // Row 3: Ruangan + Tipe Agenda
                $merger->SetXY(12, 20);
                $merger->SetFont("Helvetica", "B", 7.5);
                $merger->Cell(22, 3.5, "Ruangan", 0, 0);
                $merger->Cell(4, 3.5, ":", 0, 0);
                $merger->SetFont("Helvetica", "", 7.5);
                $merger->Cell($colWidth - 26, 3.5, $headerRoom, 0, 0);

                $merger->SetFont("Helvetica", "B", 7.5);
                $merger->Cell(26, 3.5, "Tipe Agenda", 0, 0);
                $merger->Cell(4, 3.5, ":", 0, 0);
                $merger->SetFont("Helvetica", "", 7.5);
                $merger->Cell($colWidth - 30, 3.5, $headerType, 0, 1);

                // Separator line
                $merger->SetDrawColor(30, 30, 30);
                $merger->SetLineWidth(0.4);
                $merger->Line(12, 27, $pageWidth - 12, 27);

                // -- Sub-header (section label) --
                $subLabel = $subHeaders[$fileInfo["type"]] ?? "";
                $merger->SetFont("Helvetica", "B", 11);
                $merger->SetTextColor(0, 0, 0);
                $merger->SetXY(12, 29);
                $merger->Cell($pageWidth - 24, 5, $subLabel, 0, 1, "L");

                // -- Place imported page below header + sub-header, scaled to fit --
                $availableHeight = $pageHeight - $headerHeight;
                $scale = min(
                    $pageWidth / $size["width"],
                    $availableHeight / $size["height"],
                );
                $scaledWidth = $size["width"] * $scale;
                $scaledHeight = $size["height"] * $scale;
                $xOffset = ($pageWidth - $scaledWidth) / 2;
                $merger->useTemplate(
                    $tpl,
                    $xOffset,
                    $headerHeight,
                    $scaledWidth,
                    $scaledHeight,
                );
            }
        }

        $filename = "Agenda - " . $agenda->title . ".pdf";

        // Clean up temp files
        foreach ($tempPaths as $tmp) {
            @unlink($tmp);
        }

        return response($merger->Output("S", $filename), 200, [
            "Content-Type" => "application/pdf",
            "Content-Disposition" => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportQuizCsv(Agenda $agenda)
    {
        $agenda->load(['agendaQuestions', 'bankSoal', 'unit', 'room', 'eventLeader']);

        $quizComparison = $this->buildQuizComparison($agenda);

        $agendaTitle = preg_replace('/[^A-Za-z0-9\-_ ]/', '', $agenda->title);
        $filename = "Hasil Pretest Posttest - {$agendaTitle}.csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($agenda, $quizComparison) {
            $file = fopen('php://output', 'w');

            // BOM for Excel UTF-8 compatibility
            fwrite($file, "\xEF\xBB\xBF");

            // Meta rows
            fputcsv($file, ['Judul Agenda', $agenda->title]);
            fputcsv($file, ['Tanggal', $agenda->event_date->translatedFormat('d F Y')]);
            fputcsv($file, ['Waktu', \Carbon\Carbon::parse($agenda->event_time)->format('H:i') . ($agenda->event_end_time ? ' - ' . \Carbon\Carbon::parse($agenda->event_end_time)->format('H:i') : '') . ' WIB']);
            fputcsv($file, ['Unit', $agenda->unit?->name ?? '-']);
            fputcsv($file, ['Bank Soal', $agenda->bankSoal?->title ?? 'Template telah dihapus']);
            fputcsv($file, ['Jumlah Soal', $agenda->agendaQuestions->count()]);
            fputcsv($file, []); // blank spacer

            // Column headers
            fputcsv($file, [
                'No',
                'Nama',
                'Unit',
                'Jabatan',
                'Pretest Benar',
                'Pretest Nilai',
                'Posttest Benar',
                'Posttest Nilai',
                'Perubahan',
            ]);

            foreach ($quizComparison as $index => $row) {
                $employee = $row['employee'];

                fputcsv($file, [
                    $index + 1,
                    $employee->full_name,
                    $employee->unit->name ?? '-',
                    $employee->job_position,
                    $row['pre_correct'] !== null ? $row['pre_correct'] . '/' . $row['pre_total'] : '-',
                    $row['pre_score']   !== null ? $row['pre_score']   : '-',
                    $row['post_correct'] !== null ? $row['post_correct'] . '/' . $row['post_total'] : '-',
                    $row['post_score']  !== null ? $row['post_score']  : '-',
                    $row['improvement'] !== null ? $row['improvement'] : '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function destroy(Agenda $agenda)
    {
        if ($agenda->letter_file_path) {
            Storage::disk("public")->delete($agenda->letter_file_path);
        }
        if ($agenda->material_file_path) {
            Storage::disk("public")->delete($agenda->material_file_path);
        }

        // Clean up agenda images from disk
        foreach ($agenda->images as $image) {
            Storage::disk("public")->delete($image->image_path);
        }

        // Clean up signatures from disk
        foreach ($agenda->employees as $employee) {
            if ($employee->pivot->signature_image_path) {
                Storage::disk("public")->delete(
                    $employee->pivot->signature_image_path,
                );
            }
        }

        $agenda->delete();

        return redirect()
            ->route("admin.agendas.index")
            ->with("success", "Agenda berhasil dihapus.");
    }

    private function sanitizeCsvText(?string $value): string
    {
        if ($value === null || $value === "") {
            return "";
        }

        $value = preg_replace("/\\r\\n|\\r|\\n/", " ", $value);

        return preg_replace('/[ \\t]+/', " ", trim($value)) ?? "";
    }

    private function buildQuizComparison(Agenda $agenda): Collection
    {
        if ($agenda->agendaQuestions->count() === 0) {
            return collect();
        }

        $totalQuestions = $agenda->agendaQuestions->count();

        $buildResults = function (string $quizType) use (
            $agenda,
            $totalQuestions,
        ) {
            return AgendaQuestionAnswer::where("agenda_id", $agenda->id)
                ->where("quiz_type", $quizType)
                ->select("employee_id")
                ->selectRaw("SUM(CAST(is_correct AS INTEGER)) as correct_count")
                ->selectRaw("COUNT(*) as answered_count")
                ->selectRaw("MIN(created_at) as answered_at")
                ->groupBy("employee_id")
                ->with("employee.unit")
                ->get()
                ->keyBy("employee_id")
                ->map(function ($row) use ($totalQuestions) {
                    return [
                        "employee" => $row->employee,
                        "correct" => (int) $row->correct_count,
                        "total" => $totalQuestions,
                        "score" =>
                            $totalQuestions > 0
                                ? round(
                                    ($row->correct_count / $totalQuestions) *
                                        100,
                                )
                                : 0,
                        "answered_at" => $row->answered_at,
                    ];
                });
        };

        $pretestMap = $buildResults("pretest");
        $posttestMap = $buildResults("posttest");
        $allEmployeeIds = $pretestMap
            ->keys()
            ->merge($posttestMap->keys())
            ->unique();

        return $allEmployeeIds
            ->map(function ($empId) use ($pretestMap, $posttestMap) {
                $pre = $pretestMap->get($empId);
                $post = $posttestMap->get($empId);

                return [
                    "employee" => $pre ? $pre["employee"] : $post["employee"],
                    "pre_correct" => $pre ? $pre["correct"] : null,
                    "pre_total" => $pre ? $pre["total"] : null,
                    "pre_score" => $pre ? $pre["score"] : null,
                    "post_correct" => $post ? $post["correct"] : null,
                    "post_total" => $post ? $post["total"] : null,
                    "post_score" => $post ? $post["score"] : null,
                    "improvement" =>
                        $pre && $post ? $post["score"] - $pre["score"] : null,
                ];
            })
            ->sortBy("employee.full_name")
            ->values();
    }

    private function validateAgenda(Request $request): array
    {
        return $request->validate([
            "title" => "required|string|max:255",
            "description" => "nullable|string",
            "event_date" => "required|date",
            "event_time" => "required|date_format:H:i",
            "event_end_time" =>
                "nullable|required_if:type,diklat|required_if:type,pelatihan|date_format:H:i|after:event_time",
            "unit_id" => "required|exists:units,id",
            "event_leader_id" => "required|exists:employees,id",
            "room_id" => "required|exists:rooms,id",
            "type" => "required|in:diklat,pelatihan,rapat",
            "bank_soal_id" => "nullable|exists:bank_soals,id",
            "presenter_ids" => "nullable|array",
            "presenter_ids.*" => "nullable|distinct|exists:employees,id",
            "letter_file" => "nullable|file|mimes:pdf|max:5120",
            "material_file" => "nullable|file|mimes:pdf|max:10240",
        ]);
    }

    private function buildAgendaData(array $validated): array
    {
        $agendaData = collect($validated)
            ->except(["letter_file", "material_file", "presenter_ids"])
            ->toArray();

        if ($agendaData["type"] === "rapat") {
            $agendaData["bank_soal_id"] = null;
            $agendaData["event_end_time"] = null;
        }

        return $agendaData;
    }

    private function extractPresenterIds(array $validated): array
    {
        return collect($validated["presenter_ids"] ?? [])
            ->filter(fn($id) => $id !== null && $id !== "")
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function storeAgendaFiles(
        Request $request,
        Agenda $agenda,
        bool $replaceExisting = false,
    ): array {
        $fileData = [];

        if ($request->hasFile("letter_file")) {
            if ($replaceExisting && $agenda->letter_file_path) {
                Storage::disk("public")->delete($agenda->letter_file_path);
            }

            $fileData["letter_file_path"] = $request
                ->file("letter_file")
                ->store("agenda-files/" . $agenda->id, "public");
        }

        if ($request->hasFile("material_file")) {
            if ($replaceExisting && $agenda->material_file_path) {
                Storage::disk("public")->delete($agenda->material_file_path);
            }

            $fileData["material_file_path"] = $request
                ->file("material_file")
                ->store("agenda-files/" . $agenda->id, "public");
        }

        return $fileData;
    }

    private function syncAgendaPresenters(
        Agenda $agenda,
        array $presenterIds,
    ): void {
        if ($agenda->type === "rapat") {
            $agenda->presenters()->sync([]);

            return;
        }

        $syncData = collect($presenterIds)
            ->values()
            ->mapWithKeys(
                fn($employeeId, $index) => [
                    $employeeId => ["sort_order" => $index + 1],
                ],
            )
            ->all();

        $agenda->presenters()->sync($syncData);
    }

    private function searchOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    private function syncAgendaQuestionsFromTemplate(
        Agenda $agenda,
        ?string $oldType = null,
        ?int $oldBankSoalId = null,
    ): void {
        $isQuizAgenda = in_array($agenda->type, ["diklat", "pelatihan"], true);

        if (!$isQuizAgenda || !$agenda->bank_soal_id) {
            $agenda->agendaQuestions()->delete();
            AgendaQuestionAnswer::where("agenda_id", $agenda->id)->delete();

            return;
        }

        $shouldRefreshQuestions =
            $oldType === null ||
            $oldType !== $agenda->type ||
            $oldBankSoalId !== $agenda->bank_soal_id;

        if (!$shouldRefreshQuestions) {
            return;
        }

        $agenda->agendaQuestions()->delete();
        AgendaQuestionAnswer::where("agenda_id", $agenda->id)->delete();

        $questions = Question::where(
            "bank_soal_id",
            $agenda->bank_soal_id,
        )->get();
        $agenda
            ->agendaQuestions()
            ->createMany(
                $questions->map
                    ->only([
                        "question_text",
                        "option_a",
                        "option_b",
                        "option_c",
                        "option_d",
                        "option_e",
                        "correct_option",
                    ])
                    ->toArray(),
            );
    }
}
