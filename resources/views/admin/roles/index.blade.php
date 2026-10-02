<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-extrabold text-gray-900 tracking-tight">Role & Izin</h2>
            <p class="text-sm text-gray-400 mt-0.5">Kelola peran dan hak akses pengguna</p>
        </div>
    </x-slot>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-2">
            <div class="w-10 h-10 rounded-2xl bg-primary-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-500">{{ $roles->count() }} role</span>
        </div>
        <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-md shadow-primary/20 hover:bg-primary-700 hover:shadow-lg active:scale-[0.98] transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Tambah Role
        </a>
    </div>

    <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-primary-700 text-white">
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider w-14">No</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Izin</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider">Akun</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($roles as $index => $role)
                        <tr class="group transition-colors hover:bg-primary-50/40 {{ $index % 2 === 0 ? 'bg-white' : 'bg-slate-50' }}">
                            <td class="px-6 py-4 text-sm text-gray-400 font-medium">{{ $index + 1 }}</td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-gray-800">{{ $role->name }}</p>
                                @if($role->description)
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $role->description }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5 max-w-md">
                                    @forelse($role->permissions as $permission)
                                        <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-semibold bg-primary-50 text-primary">{{ $permission->label }}</span>
                                    @empty
                                        <span class="text-gray-400 text-sm">Hanya lihat</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-600">{{ $role->users_count }} akun</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="p-2 rounded-xl hover:bg-primary-50 text-gray-400 hover:text-primary transition-colors" title="Ubah">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus role {{ $role->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 rounded-xl hover:bg-rose-50 text-gray-400 hover:text-rose-500 transition-colors" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <p class="text-sm text-gray-400">Belum ada role. Jalankan <span class="font-mono">php artisan db:seed --class=RbacSeeder</span>.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
