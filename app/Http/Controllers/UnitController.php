<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

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
        Gate::authorize('viewAny', Unit::class);

        $q = trim((string) $request->input('q'));
        $operator = $this->searchOperator();
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        $units = Unit::query()
            ->orderBy('name')
            ->when($q !== '', fn ($query) => $query->where('name', $operator, "%{$q}%"))
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.units.index', compact('units', 'q', 'perPage'));
    }

    public function create()
    {
        Gate::authorize('create', Unit::class);

        return view('admin.units.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Unit::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
        ]);

        Unit::create($validated);

        return redirect()->route('admin.units.index')
            ->with('success', 'Unit berhasil ditambahkan.');
    }

    public function edit(Unit $unit)
    {
        Gate::authorize('update', $unit);

        return view('admin.units.edit', compact('unit'));
    }

    public function show(Request $request, Unit $unit)
    {
        Gate::authorize('view', $unit);

        $q = trim((string) $request->input('q'));
        $operator = $this->searchOperator();
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        $employees = $unit->employees()
            ->with(['user'])
            ->orderBy('full_name')
            ->when($q !== '', fn ($query) => $query->where(function ($query) use ($q, $operator) {
                $query->where('full_name', $operator, "%{$q}%")
                    ->orWhere('nip', $operator, "%{$q}%")
                    ->orWhereHas('user', fn ($q2) => $q2->where('email', $operator, "%{$q}%"));
            }))
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.units.show', compact('unit', 'employees', 'q', 'perPage'));
    }

    public function update(Request $request, Unit $unit)
    {
        Gate::authorize('update', $unit);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name,' . $unit->id,
        ]);

        $unit->update($validated);

        return redirect()->route('admin.units.index')
            ->with('success', 'Unit berhasil diperbarui.');
    }

    public function destroy(Unit $unit)
    {
        Gate::authorize('delete', $unit);

        $unit->delete();

        return redirect()->route('admin.units.index')
            ->with('success', 'Unit berhasil dihapus.');
    }

    private function searchOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }
}
