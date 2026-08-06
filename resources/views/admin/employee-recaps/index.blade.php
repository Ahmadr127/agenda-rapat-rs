<x-app-layout>
    @php
        $selectedUnit = request('unit_id') ? $units->firstWhere('id', (int) request('unit_id')) : null;
    @endphp

    <x-slot name="header">
        <div>
            <h2 class="text-xl font-extrabold text-gray-900 tracking-tight">Rekap Karyawan</h2>
            <p class="text-sm text-gray-400 mt-0.5">Total keikutsertaan agenda dan akumulasi jam per pegawai</p>
        </div>
    </x-slot>

    <div class="bg-white rounded-3xl border border-gray-100 p-5 mb-6">
        <form method="GET" action="{{ route('admin.employee-recaps.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <label for="search" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Cari</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Nama, NIP, jabatan, atau unit"
                    class="w-full rounded-2xl border-gray-200 focus:border-primary focus:ring-primary"
                >
            </div>
            <div>
                <label for="unit_id" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Unit</label>
                <x-searchable-select
                    name="unit_id"
                    :search-url="route('admin.units.search')"
                    :selected-id="request('unit_id')"
                    :selected-label="$selectedUnit?->name"
                    placeholder="Semua Unit"
                />
            </div>
            <div>
                <label for="date_from" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Dari Tanggal</label>
                <input
                    type="date"
                    id="date_from"
                    name="date_from"
                    value="{{ request('date_from') }}"
                    class="w-full rounded-2xl border-gray-200 focus:border-primary focus:ring-primary"
                >
            </div>
            <div>
                <label for="date_to" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Sampai Tanggal</label>
                <input
                    type="date"
                    id="date_to"
                    name="date_to"
                    value="{{ request('date_to') }}"
                    class="w-full rounded-2xl border-gray-200 focus:border-primary focus:ring-primary"
                >
            </div>

            <div class="md:col-span-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                <p class="text-sm text-gray-400">Total jam dihitung dari agenda yang memiliki jam mulai dan jam selesai.</p>

                <div class="flex items-center gap-2">
                    <a
                        href="{{ route('admin.employee-recaps.export-csv', request()->query()) }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-white border border-gray-200 text-gray-700 text-sm font-bold shadow-sm hover:bg-gray-50 hover:shadow active:scale-[0.98] transition-all duration-200"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Export CSV
                    </a>
                    <a
                        href="{{ route('admin.employee-recaps.index') }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-white border border-gray-200 text-gray-700 text-sm font-bold shadow-sm hover:bg-gray-50 hover:shadow active:scale-[0.98] transition-all duration-200"
                    >
                        Reset
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-md shadow-primary/20 hover:bg-primary-700 hover:shadow-lg active:scale-[0.98] transition-all duration-200"
                    >
                        Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-primary-700 text-white">
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">NIP</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Nama Pegawai</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Unit</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Jabatan</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider">Ikut Agenda</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider">Jam Rapat</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider">Jam Diklat/Pelatihan</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($employees as $index => $employee)
                        <tr class="group transition-colors hover:bg-primary-50/40 {{ $index % 2 === 0 ? 'bg-white' : 'bg-slate-50' }}">
                            <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ $employee->nip }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-primary-50 flex items-center justify-center text-xs font-bold text-primary">
                                        {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.employee-recaps.agendas.index', $employee->id) }}" class="text-sm font-semibold text-gray-800 hover:text-primary transition-colors">
                                            {{ $employee->full_name }}
                                        </a>
                                        <p class="text-xs text-gray-400">{{ $employee->profession }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $employee->unit_name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $employee->job_position }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-primary-50 text-primary">
                                    {{ number_format($employee->attendance_count) }} kali
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-semibold text-gray-800">
                                {{ number_format($employee->rapat_hours, 2, ',', '.') }} jam
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-semibold text-gray-800">
                                {{ number_format($employee->training_hours, 2, ',', '.') }} jam
                            </td>
                            <td class="px-6 py-4 text-right flex justify-center">
                                <a
                                    href="{{ route('admin.employee-recaps.agendas.index', $employee->id) }}"
                                    class="p-2 rounded-xl hover:bg-blue-50 text-gray-400 hover:text-blue-500 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.503 3.503 12 4.125 12h2.25c.622 0 1.125.503 1.125 1.125V20.25H3v-7.125zM9.75 8.625c0-.622.503-1.125 1.125-1.125h2.25c.622 0 1.125.503 1.125 1.125v11.625H9.75V8.625zM16.5 4.125C16.5 3.503 17.003 3 17.625 3h2.25C20.497 3 21 3.503 21 4.125V20.25h-4.5V4.125z"/></svg>
                                </div>
                                <p class="text-sm text-gray-400">Belum ada data rekap yang sesuai filter.</p>
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
</x-app-layout>
