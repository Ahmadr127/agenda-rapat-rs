<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-extrabold text-gray-900 tracking-tight">Agenda</h2>
            <p class="text-sm text-gray-400 mt-0.5">Kelola agenda rapat dan kegiatan</p>
        </div>
    </x-slot>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-2">
            <div class="w-10 h-10 rounded-2xl bg-primary-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-500">{{ $agendas->total() }} agenda</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.agendas.export-csv') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-white border border-gray-200 text-gray-700 text-sm font-bold shadow-sm hover:bg-gray-50 hover:shadow active:scale-[0.98] transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Export CSV
            </a>
            <a href="{{ route('admin.agendas.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-md shadow-primary/20 hover:bg-primary-700 hover:shadow-lg active:scale-[0.98] transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Buat Agenda
            </a>
        </div>
    </div>

    <div class="mb-6">
        <form method="GET" action="{{ route('admin.agendas.index') }}" class="flex flex-col gap-3">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input
                        type="text"
                        name="q"
                        value="{{ $q }}"
                        placeholder="Cari judul agenda..."
                        class="block w-full px-4 py-3 rounded-2xl border border-gray-200 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none"
                    >
                </div>
                <div class="flex gap-2 shrink-0">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-md shadow-primary/20 hover:bg-primary-700 hover:shadow-lg active:scale-[0.98] transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                        Cari
                    </button>
                    <a href="{{ route('admin.agendas.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-gray-100 text-gray-600 text-sm font-bold hover:bg-gray-200 active:scale-[0.98] transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        Reset
                    </a>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <div class="w-40">
                    <x-searchable-select
                        name="type"
                        search-url="{{ route('admin.agendas.types.search') }}"
                        :selected-id="$type"
                        :selected-label="$typeLabel"
                        placeholder="Filter tipe..."
                        bg-color="bg-white"
                    />
                </div>
                <div class="w-52">
                    <x-searchable-select
                        name="room_id"
                        search-url="{{ route('admin.rooms.search') }}"
                        :selected-id="$selectedRoom?->id"
                        :selected-label="$selectedRoom?->room_name"
                        placeholder="Filter ruangan..."
                        bg-color="bg-white"
                    />
                </div>
                <div class="w-52">
                    <x-searchable-select
                        name="unit_id"
                        search-url="{{ route('admin.units.search') }}"
                        :selected-id="$selectedUnit?->id"
                        :selected-label="$selectedUnit?->name"
                        placeholder="Filter unit..."
                        bg-color="bg-white"
                    />
                </div>
                <div class="w-52">
                    <x-searchable-select
                        name="event_leader_id"
                        search-url="{{ route('admin.employees.search') }}"
                        :selected-id="$selectedEventLeader?->id"
                        :selected-label="$selectedEventLeader?->full_name"
                        placeholder="Filter pimpinan..."
                        bg-color="bg-white"
                    />
                </div>
                <x-date-range-picker :date-from="$dateFrom" :date-to="$dateTo" />
            </div>
        </form>
    </div>

    <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-primary-700 text-white">
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Judul</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Deskripsi</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Jam</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Ruangan</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Unit</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Pimpinan Agenda</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($agendas as $index => $agenda)
                        <tr class="group transition-colors hover:bg-primary-50/40 {{ $index % 2 === 0 ? 'bg-white' : 'bg-slate-50' }}">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.agendas.show', $agenda) }}" class="text-sm font-semibold text-gray-800 group-hover:text-primary transition-colors">{{ $agenda->title }}</a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-pre-wrap">{{ $agenda->description ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $agenda->event_date->translatedFormat('d M Y') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ \Carbon\Carbon::parse($agenda->event_time)->format('H:i') }} - {{\Carbon\Carbon::parse($agenda->event_time)->format('H:i') ?? "Selesai"}} </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $agenda->room->room_name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $agenda->unit?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $agenda->eventLeader?->full_name ?? '-' }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.agendas.show', $agenda) }}" class="p-2 rounded-xl hover:bg-blue-50 text-gray-400 hover:text-blue-500 transition-colors" title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.agendas.edit', $agenda) }}" class="p-2 rounded-xl hover:bg-primary-50 text-gray-400 hover:text-primary transition-colors" title="Ubah">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.agendas.export-pdf', $agenda) }}" class="p-2 rounded-xl hover:bg-amber-50 text-gray-400 hover:text-amber-500 transition-colors" title="Export PDF">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                    </a>
                                    <form action="{{ route('admin.agendas.destroy', $agenda) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')">
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
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                </div>
                                <p class="text-sm text-gray-400">Belum ada agenda</p>
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
</x-app-layout>
