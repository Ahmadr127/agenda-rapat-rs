<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnitController extends Controller
{
    public function search(Request $request)
    {
        if ($request->filled('id')) {
            $unit = Unit::find($request->id);

            return response()->json([
                'items' => $unit ? [['id' => $unit->id, 'name' => $unit->name]] : [],
                'has_more' => false,
            ]);
        }

        $search = trim((string) $request->input('q'));
        $operator = $this->searchOperator();

        $query = Unit::query()->orderBy('name');

        if ($search !== '') {
            $query->where('name', $operator, "%{$search}%");
        }

        $units = $query->simplePaginate(10);

        return response()->json([
            'items' => collect($units->items())
                ->map(fn (Unit $unit) => ['id' => $unit->id, 'name' => $unit->name])
                ->values(),
            'has_more' => $units->hasMorePages(),
        ]);
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));
        $operator = $this->searchOperator();

        $units = Unit::query()
            ->orderBy('name')
            ->when($q !== '', fn ($query) => $query->where('name', $operator, "%{$q}%"))
            ->paginate(15)
            ->withQueryString();

        return view('admin.units.index', compact('units', 'q'));
    }

    public function create()
    {
        return view('admin.units.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
        ]);

        Unit::create($validated);

        return redirect()->route('admin.units.index')
            ->with('success', 'Unit berhasil ditambahkan.');
    }

    public function edit(Unit $unit)
    {
        return view('admin.units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name,' . $unit->id,
        ]);

        $unit->update($validated);

        return redirect()->route('admin.units.index')
            ->with('success', 'Unit berhasil diperbarui.');
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();

        return redirect()->route('admin.units.index')
            ->with('success', 'Unit berhasil dihapus.');
    }

    private function searchOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }
}
