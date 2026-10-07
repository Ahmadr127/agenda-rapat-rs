<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\RbacSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function search(Request $request)
    {
        if ($request->filled('id')) {
            $role = Role::find($request->id);

            return response()->json([
                'items' => $role ? [['id' => $role->id, 'name' => $role->name]] : [],
                'has_more' => false,
            ]);
        }

        $search = trim((string) $request->input('q'));

        $query = Role::query()->orderBy('name');

        if ($search !== '') {
            $operator = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where('name', $operator, "%{$search}%");
        }

        $roles = $query->simplePaginate(10);

        return response()->json([
            'items' => collect($roles->items())
                ->map(fn (Role $role) => ['id' => $role->id, 'name' => $role->name])
                ->values(),
            'has_more' => $roles->hasMorePages(),
        ]);
    }

    public function index(): View
    {
        Gate::authorize('viewAny', Role::class);

        $roles = Role::withCount(['users', 'permissions'])->orderBy('name')->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        Gate::authorize('create', Role::class);

        return view('admin.roles.form', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Role::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);
        $role->permissions()->sync($validated['permission_ids'] ?? []);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', "Role \"{$role->name}\" berhasil dibuat.");
    }

    public function edit(Role $role): View
    {
        Gate::authorize('update', $role);

        $role->load('permissions');

        return view('admin.roles.form', $this->formData($role));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        Gate::authorize('update', $role);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name,'.$role->id],
            'description' => ['nullable', 'string', 'max:255'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);
        $role->permissions()->sync($validated['permission_ids'] ?? []);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', "Role \"{$role->name}\" berhasil diperbarui.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        Gate::authorize('delete', $role);

        abort_if($role->users()->exists(), 422, 'Role masih dipakai akun aktif, kosongkan dulu penetapannya.');

        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', "Role \"{$role->name}\" berhasil dihapus.");
    }

    /**
     * @return array<string, \Illuminate\Database\Eloquent\Collection<int, Permission>>
     */
    private function groupedPermissions(): array
    {
        return Permission::orderBy('group')
            ->orderBy('label')
            ->get()
            ->groupBy('group')
            ->all();
    }

    /**
     * Data untuk form interaktif: grup izin, template preset (dari
     * RbacSeeder), dan daftar role lain untuk fitur "salin dari".
     */
    private function formData(?Role $role = null): array
    {
        $permissions = Permission::orderBy('group')->orderBy('label')->get();

        $groups = [];
        foreach ($permissions as $permission) {
            $groups[$permission->group][] = [
                'id' => (string) $permission->id,
                'key' => $permission->key,
                'label' => $permission->label,
            ];
        }

        $groupList = [];
        foreach ($groups as $groupName => $items) {
            $groupList[] = ['group' => $groupName, 'items' => array_values($items)];
        }

        $keyToId = $permissions->pluck('id', 'key')
            ->map(fn ($id) => (string) $id)
            ->all();

        $presets = [];
        foreach (RbacSeeder::ROLE_PERMISSIONS as $name => $keys) {
            $presets[] = [
                'name' => $name,
                'description' => RbacSeeder::ROLE_DESCRIPTIONS[$name] ?? '',
                'permission_ids' => collect($keys)
                    ->map(fn ($key) => $keyToId[$key] ?? null)
                    ->filter()
                    ->values()
                    ->all(),
            ];
        }

        $copyRoles = Role::with('permissions:id')
            ->when($role, fn ($query) => $query->where('id', '!=', $role->id))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Role $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'permission_ids' => $item->permissions->pluck('id')->map(fn ($id) => (string) $id)->all(),
            ])
            ->all();

        $initialSelected = old(
            'permission_ids',
            $role ? $role->permissions->pluck('id')->map(fn ($id) => (string) $id)->all() : [],
        );

        return [
            'role' => $role,
            'permissionGroups' => $groupList,
            'totalPermissions' => $permissions->count(),
            'presets' => $presets,
            'copyRoles' => $copyRoles,
            'initialSelected' => collect($initialSelected)->map(fn ($id) => (string) $id)->values()->all(),
            'initialName' => old('name', $role?->name ?? ''),
        ];
    }
}
