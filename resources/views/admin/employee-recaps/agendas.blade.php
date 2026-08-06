<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.employee-recaps.index') }}" class="text-gray-400 hover:text-primary transition-colors">Rekap Karyawan</a>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
            <span class="font-semibold text-gray-700">Agenda Diikuti</span>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="bg-white rounded-3xl border border-gray-100 p-6">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-primary-50 flex items-center justify-center text-lg font-bold text-primary">
                        {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="text-xl font-extrabold text-gray-900 tracking-tight">{{ $employee->full_name }}</h2>
                        <p class="text-sm text-gray-400 mt-0.5">
                            {{ $employee->nip }} - {{ $employee->job_position }}
                        </p>
                        <p class="text-sm text-gray-500 mt-2">
                            Unit: <span class="font-semibold text-gray-700">{{ $employee->unit?->name ?? '-' }}</span>
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 lg:min-w-[360px]">
                    <div class="rounded-2xl bg-gray-50 border border-gray-100 px-4 py-3">
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total Agenda</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ number_format($summary['agenda_count']) }}</p>
                    </div>
                    <div class="rounded-2xl bg-gray-50 border border-gray-100 px-4 py-3">
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Periode Data</p>
                        <p class="text-sm font-semibold text-gray-800 mt-1">
                            @if($summary['first_event_date'] && $summary['last_event_date'])
                                {{ \Carbon\Carbon::parse($summary['first_event_date'])->translatedFormat('d M Y') }}
                                -
                                {{ \Carbon\Carbon::parse($summary['last_event_date'])->translatedFormat('d M Y') }}
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-gray-100 p-5">
            <form method="GET" action="{{ route('admin.employee-recaps.agendas.index', $employee) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label for="search" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Cari</label>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Judul, deskripsi, penyelenggara, unit, ruangan"
                        class="w-full rounded-2xl border-gray-200 focus:border-primary focus:ring-primary"
                    >
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

                <div class="md:col-span-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                    <p class="text-sm text-gray-400">Daftar hanya menampilkan agenda yang sudah dihadiri peserta.</p>

                    <div class="flex items-center gap-2">
                        <a
                            href="{{ route('admin.employee-recaps.agendas.export-csv', array_merge(['employee' => $employee], request()->query())) }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-white border border-gray-200 text-gray-700 text-sm font-bold shadow-sm hover:bg-gray-50 hover:shadow active:scale-[0.98] transition-all duration-200"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Export CSV
                        </a>
                        <a
                            href="{{ route('admin.employee-recaps.agendas.index', $employee) }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-white border border-gray-200 text-gray-700 text-sm font-bold shadow-sm hover:bg-gray-50 hover:shadow active:scale-[0.98] transition-all duration-200"
                        >
                            Reset
                        </a>
                        <a
                            href="{{ route('admin.employee-recaps.index') }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-white border border-gray-200 text-gray-700 text-sm font-bold shadow-sm hover:bg-gray-50 hover:shadow active:scale-[0.98] transition-all duration-200"
                        >
                            Kembali
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
                <table class="w-full min-w-[1100px]">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Judul Agenda</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Deskripsi</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Waktu</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Unit</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Pimpinan Acara</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Ruangan</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($agendas as $agenda)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="text-sm font-semibold text-gray-800">{{ $agenda->title }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-sm">
                                    {{ \Illuminate\Support\Str::limit($agenda->description ?? '-', 110) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($agenda->event_date)->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                    {{ substr($agenda->event_time, 0, 5) }}
                                    @if($agenda->event_end_time)
                                        - {{ substr($agenda->event_end_time, 0, 5) }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $agenda->unit_name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $agenda->event_leader_name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $agenda->room_name ?? '-' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a
                                        href="{{ route('admin.agendas.show', $agenda->agenda_id) }}"
                                        class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-primary-50 text-primary text-xs font-bold hover:bg-primary-100 transition-colors"
                                    >
                                        Lihat
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center">
                                    <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                    </div>
                                    <p class="text-sm text-gray-400">Belum ada agenda yang sesuai filter untuk peserta ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($agendas->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">{{ $agendas->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
