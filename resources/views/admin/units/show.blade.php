<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.units.index') }}" class="text-gray-400 hover:text-primary transition-colors">Unit</a>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            <span class="font-semibold text-gray-700">Detail Unit</span>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- ===== INFO UNIT ===== --}}
        <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
            <div class="p-8">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-primary-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-extrabold text-gray-900">{{ $unit->name }}</h2>
                            <p class="text-sm text-gray-400 mt-0.5">{{ $employees->total() }} pegawai di unit ini</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a href="{{ route('admin.units.edit', $unit) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-gray-100 text-gray-700 text-sm font-bold hover:bg-gray-200 active:scale-[0.98] transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                            Edit
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== SEARCH ===== --}}
        <div>
            <x-search-filter
                :action="route('admin.units.show', $unit)"
                :q="$q"
                placeholder="Cari nama, NIP, atau email pegawai..."
            />
        </div>

        {{-- ===== LIST PEGAWAI ===== --}}
        <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Daftar Pegawai</h3>
                    <p class="text-sm text-gray-400 mt-0.5">{{ $employees->total() }} pegawai</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary-50 text-xs font-bold text-primary">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    {{ $employees->total() }} Pegawai
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-primary-700 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider w-12">No</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">NIP</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Nama Lengkap</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Posisi Pekerjaan</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Jabatan</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Profesi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($employees as $index => $employee)
                            <tr class="group transition-colors hover:bg-primary-50/40 {{ $index % 2 === 0 ? 'bg-white' : 'bg-slate-50' }}">
                                <td class="px-6 py-4 text-sm text-gray-400 font-medium">{{ $employees->firstItem() + $index }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ $employee->nip }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-primary-50 flex items-center justify-center text-xs font-bold text-primary">{{ strtoupper(substr($employee->full_name, 0, 1)) }}</div>
                                        <span class="text-sm font-semibold text-gray-800">{{ $employee->full_name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500 font-mono">{{ $employee->user->email ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $employee->job_position }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $employee->structural_role }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $employee->profession }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                                    </div>
                                    <p class="text-sm text-gray-400">Belum ada pegawai di unit ini</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($employees->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">{{ $employees->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
