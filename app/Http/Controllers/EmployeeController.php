<?php

namespace App\Http\Controllers;

use App\Helpers\NameConverter;
use App\Models\Employee;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function search(Request $request)
    {
        if ($request->filled('id')) {
            $employee = Employee::find($request->id);

            return response()->json([
                'items' => $employee ? [[
                    'id' => $employee->id,
                    'name' => $employee->full_name,
                ]] : [],
                'has_more' => false,
            ]);
        }

        $search = trim((string) $request->input('q'));
        $operator = $this->searchOperator();

        $employees = Employee::query()
            ->orderBy('full_name')
            ->when($search !== '', function ($query) use ($search, $operator) {
                $query->where(function ($query) use ($search, $operator) {
                    $query->where('full_name', $operator, "%{$search}%")
                        ->orWhere('nip', $operator, "%{$search}%");
                });
            })
            ->simplePaginate(10);

        return response()->json([
            'items' => collect($employees->items())
                ->map(fn (Employee $employee) => [
                    'id' => $employee->id,
                    'name' => $employee->full_name,
                ])
                ->values(),
            'has_more' => $employees->hasMorePages(),
        ]);
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));
        $unitId = $request->input('unit_id');
        $operator = $this->searchOperator();

        $employees = Employee::with('unit')
            ->orderBy('full_name')
            ->when($q !== '', fn ($query) => $query->where(function ($query) use ($q, $operator) {
                $query->where('full_name', $operator, "%{$q}%")
                    ->orWhere('nip', $operator, "%{$q}%");
            }))
            ->when($unitId, fn ($query) => $query->where('unit_id', $unitId))
            ->paginate(15)
            ->withQueryString();

        $selectedUnit = $unitId ? Unit::find($unitId) : null;

        return view("admin.employees.index", compact("employees", "q", "selectedUnit"));
    }

    public function create()
    {
        return view("admin.employees.create");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            "nip" => "required|string|max:255|unique:employees,nip",
            "full_name" => "required|string|max:255",
            "unit_id" => "required|exists:units,id",
            "job_position" => "required|string|max:255",
            "structural_role" => "required|string|max:255",
            "profession" => "required|string|max:255",
        ]);

        $user = $this->syncUser(null, $validated['full_name']);
        $validated['user_id'] = $user->id;

        Employee::create($validated);

        return redirect()
            ->route("admin.employees.index")
            ->with("success", "Pegawai berhasil ditambahkan.");
    }

    public function edit(Employee $employee)
    {
        $employee->load('unit');

        return view("admin.employees.edit", compact("employee"));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            "nip" =>
                "required|string|max:255|unique:employees,nip," . $employee->id,
            "full_name" => "required|string|max:255",
            "unit_id" => "required|exists:units,id",
            "job_position" => "required|string|max:255",
            "structural_role" => "required|string|max:255",
            "profession" => "required|string|max:255",
        ]);

        $this->syncUser($employee->user_id, $validated['full_name']);

        $employee->update($validated);

        return redirect()
            ->route("admin.employees.index")
            ->with("success", "Pegawai berhasil diperbarui.");
    }

    public function destroy(Employee $employee)
    {
        $userId = $employee->user_id;

        $employee->delete();

        if ($userId) {
            User::destroy($userId);
        }

        return redirect()
            ->route("admin.employees.index")
            ->with("success", "Pegawai berhasil dihapus.");
    }

    private function searchOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    /**
     * Create or update the linked User record.
     * Delegates name/email derivation to NameConverter.
     */
    private function syncUser(?int $userId, string $fullName): User
    {
        $converted = NameConverter::convert($fullName, 'rsazra.co.id');

        $name      = $converted['name'] ?: 'user';
        $baseEmail = $converted['email'];
        $email     = $baseEmail;
        $counter   = 1;

        // Make email unique if already taken by a different user
        while (
            User::where('email', $email)
                ->when($userId, fn ($q) => $q->where('id', '!=', $userId))
                ->exists()
        ) {
            $local = substr($baseEmail, 0, strrpos($baseEmail, '@'));
            $email = $local . $counter . '@rsazra.co.id';
            $counter++;
        }

        if ($userId) {
            $user = User::findOrFail($userId);
            $user->update([
                'name'  => $name,
                'email' => $email,
            ]);

            return $user;
        }

        return User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make('rsazra2026'),
        ]);
    }
}
