<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));

        $users = User::with('employee')
            ->orderBy('name')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                        ->orWhere('username', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'q'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'lowercase', 'max:255', 'regex:/^[a-z0-9._-]+$/', Rule::unique('users', 'username')],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['nullable', 'confirmed', Password::min(6)],
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        if ($employee->user_id) {
            return back()->withInput()->withErrors(['employee_id' => 'Pegawai ini sudah memiliki akun.']);
        }

        DB::transaction(function () use ($validated, $employee) {
            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password'] ?? 'rsazra2026'),
            ]);

            $employee->update(['user_id' => $user->id]);
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Akun \"{$validated['name']}\" berhasil dibuat.");
    }

    public function edit(User $user): View
    {
        $user->load('employee.unit');

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'lowercase', 'max:255', 'regex:/^[a-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'unit_id' => ['nullable', 'exists:units,id'],
        ]);

        DB::transaction(function () use ($user, $validated) {
            $user->update(Arr::only($validated, ['name', 'username', 'email']));

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
        abort_if($user->is(auth()->user()), 403, 'Tidak dapat menghapus akun sendiri.');

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Akun \"{$user->name}\" berhasil dihapus. Data pegawai tetap tersimpan.");
    }

    public function editPassword(User $user)
    {
        $user->load('employee');

        return view('admin.users.change-password', compact('user'));
    }

    public function updatePassword(Request $request, User $user)
    {
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
