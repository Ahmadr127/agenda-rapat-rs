<?php

namespace App\Http\Controllers;

use App\Models\BankSoal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankSoalController extends Controller
{
    public function search(Request $request)
    {
        if ($request->filled('id')) {
            $bankSoal = BankSoal::find($request->id);

            return response()->json([
                'items' => $bankSoal ? [[
                    'id' => $bankSoal->id,
                    'name' => $bankSoal->title,
                ]] : [],
                'has_more' => false,
            ]);
        }

        $search = trim((string) $request->input('q'));
        $operator = $this->searchOperator();

        $bankSoals = BankSoal::query()
            ->orderBy('title')
            ->when($search !== '', function ($query) use ($search, $operator) {
                $query->where('title', $operator, "%{$search}%");
            })
            ->simplePaginate(10);

        return response()->json([
            'items' => collect($bankSoals->items())
                ->map(fn (BankSoal $bankSoal) => [
                    'id' => $bankSoal->id,
                    'name' => $bankSoal->title,
                ])
                ->values(),
            'has_more' => $bankSoals->hasMorePages(),
        ]);
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $operator = $this->searchOperator();

        $bankSoals = BankSoal::withCount('questions')
            ->when($q !== '', function ($query) use ($q, $operator) {
                $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);
                $query->where('title', $operator, "%{$escaped}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.bank-soals.index', compact('bankSoals', 'q'));
    }

    public function create()
    {
        return view('admin.bank-soals.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.option_a' => 'required|string|max:255',
            'questions.*.option_b' => 'required|string|max:255',
            'questions.*.option_c' => 'required|string|max:255',
            'questions.*.option_d' => 'nullable|string|max:255',
            'questions.*.option_e' => 'nullable|string|max:255',
            'questions.*.correct_option' => 'required|in:a,b,c,d,e',
        ]);

        $bankSoal = BankSoal::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
        ]);

        $bankSoal->questions()->createMany($validated['questions']);

        return redirect()
            ->route('admin.bank-soals.index')
            ->with('success', 'Bank soal berhasil ditambahkan.');
    }

    public function show(BankSoal $bankSoal)
    {
        $bankSoal->load('questions');

        return view('admin.bank-soals.show', compact('bankSoal'));
    }

    public function edit(BankSoal $bankSoal)
    {
        $bankSoal->load('questions');

        return view('admin.bank-soals.edit', compact('bankSoal'));
    }

    public function update(Request $request, BankSoal $bankSoal)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.option_a' => 'required|string|max:255',
            'questions.*.option_b' => 'required|string|max:255',
            'questions.*.option_c' => 'required|string|max:255',
            'questions.*.option_d' => 'nullable|string|max:255',
            'questions.*.option_e' => 'nullable|string|max:255',
            'questions.*.correct_option' => 'required|in:a,b,c,d,e',
        ]);

        $bankSoal->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
        ]);

        $bankSoal->questions()->delete();
        $bankSoal->questions()->createMany($validated['questions']);

        return redirect()
            ->route('admin.bank-soals.index')
            ->with('success', 'Bank soal berhasil diperbarui.');
    }

    public function destroy(BankSoal $bankSoal)
    {
        $bankSoal->delete();

        return redirect()
            ->route('admin.bank-soals.index')
            ->with('success', 'Bank soal berhasil dihapus.');
    }

    private function searchOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }
}
