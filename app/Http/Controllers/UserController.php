<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Services\SignatureStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', User::class);

        $q = trim((string) $request->input('q'));
        $roleId = $request->input('role_id');
        $unitId = $request->input('unit_id');
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        $users = User::with(['employee.unit', 'roles'])
            ->orderBy('name')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                        ->orWhere('username', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->when($roleId, fn ($query) => $query->whereHas('roles', fn ($query) => $query->where('roles.id', $roleId)))
            ->when($unitId, fn ($query) => $query->whereHas('employee', fn ($query) => $query->where('unit_id', $unitId)))
            ->paginate($perPage)
            ->withQueryString();

        $selectedRole = $roleId ? Role::find($roleId) : null;
        $selectedUnit = $unitId ? Unit::find($unitId) : null;

        return view('admin.users.index', compact('users', 'q', 'perPage', 'selectedRole', 'selectedUnit'));
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        $roles = Role::orderBy('name')->get();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request, SignatureStorageService $signatures): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'lowercase', 'max:255', 'regex:/^[a-z0-9._-]+$/', Rule::unique('users', 'username')],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['nullable', 'confirmed', Password::min(6)],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'signature' => ['nullable', 'file', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'signature_data' => ['nullable', 'string', 'max:700000'],
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        if ($employee->user_id) {
            return back()->withInput()->withErrors(['employee_id' => 'Pegawai ini sudah memiliki akun.']);
        }

        DB::transaction(function () use ($validated, $employee, $request, $signatures) {
            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password'] ?? 'rsazra2026'),
                'signature_path' => $signatures->resolveOfficialSignature($request, null, 'user-signatures'),
            ]);

            $user->roles()->sync($validated['role_ids'] ?? []);

            $employee->update(['user_id' => $user->id]);
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Akun \"{$validated['name']}\" berhasil dibuat.");
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        $user->load('employee.unit');
        $roles = Role::orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user, SignatureStorageService $signatures): RedirectResponse
    {
        Gate::authorize('update', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'lowercase', 'max:255', 'regex:/^[a-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'unit_id' => ['nullable', 'exists:units,id'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'signature' => ['nullable', 'file', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'signature_data' => ['nullable', 'string', 'max:700000'],
            'remove_signature' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($user, $validated, $request, $signatures) {
            $user->update(Arr::only($validated, ['name', 'username', 'email']));
            $user->roles()->sync($validated['role_ids'] ?? []);
            $user->update([
                'signature_path' => $signatures->resolveOfficialSignature($request, $user->signature_path, 'user-signatures'),
            ]);

            if ($user->employee && ! empty($validated['unit_id'])) {
                $user->employee->update(['unit_id' => $validated['unit_id']]);
            }
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Akun \"{$user->name}\" berhasil diperbarui.");
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        abort_if($user->is(auth()->user()), 403, 'Tidak dapat menghapus akun sendiri.');

        if ($user->signature_path) {
            Storage::disk('public')->delete($user->signature_path);
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Akun \"{$user->name}\" berhasil dihapus. Data pegawai tetap tersimpan.");
    }

    public function editPassword(User $user)
    {
        Gate::authorize('update', $user);

        $user->load('employee');

        return view('admin.users.change-password', compact('user'));
    }

    public function updatePassword(Request $request, User $user)
    {
        Gate::authorize('update', $user);

        $request->validate([
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Password akun \"{$user->name}\" berhasil diubah.");
    }
}
