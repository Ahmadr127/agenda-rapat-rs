<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-extrabold text-gray-900 tracking-tight">Beranda</h2>
            <p class="text-sm text-gray-400 mt-0.5">Ringkasan agenda, unit, dan aktivitas terkini</p>
        </div>
    </x-slot>

    @php
        $totalPegawai = \App\Models\Employee::count();
        $totalUnit = \App\Models\Unit::count();
        $totalRuangan = \App\Models\Room::count();
        $totalAgenda = \App\Models\Agenda::count();

        $agendaByType = \App\Models\Agenda::query()
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $rapatCount = (int) ($agendaByType['rapat'] ?? 0);
        $diklatCount = (int) ($agendaByType['diklat'] ?? 0);
        $pelatihanCount = (int) ($agendaByType['pelatihan'] ?? 0);
        $largestTypeCount = max($rapatCount, $diklatCount, $pelatihanCount, 1);

        $recentAgendas = \App\Models\Agenda::with(['room', 'unit', 'eventLeader'])
            ->latest()
            ->take(5)
            ->get();
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 auto-rows-min">
        <div class="group relative bg-white rounded-3xl border border-gray-100 p-6 hover:shadow-xl hover:shadow-primary/5 hover:-translate-y-0.5 transition-all duration-300 overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-primary-50 to-transparent rounded-bl-[60px] opacity-60"></div>
            <div class="relative">
                <div class="w-11 h-11 rounded-2xl bg-primary-50 flex items-center justify-center mb-4">
                    <svg class="w-5.5 h-5.5 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                </div>
                <p class="text-sm font-medium text-gray-400 mb-1">Total Pegawai</p>
                <p class="text-3xl font-extrabold text-gray-900">{{ number_format($totalPegawai) }}</p>
            </div>
        </div>

        <div class="group relative bg-white rounded-3xl border border-gray-100 p-6 hover:shadow-xl hover:shadow-emerald-500/5 hover:-translate-y-0.5 transition-all duration-300 overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-emerald-50 to-transparent rounded-bl-[60px] opacity-60"></div>
            <div class="relative">
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 flex items-center justify-center mb-4">
                    <svg class="w-5.5 h-5.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15l-1.5 12h-12L4.5 3zm4.5 4.5h6m-5.25 3h4.5"/></svg>
                </div>
                <p class="text-sm font-medium text-gray-400 mb-1">Total Unit</p>
                <p class="text-3xl font-extrabold text-gray-900">{{ number_format($totalUnit) }}</p>
            </div>
        </div>

        <div class="group relative bg-white rounded-3xl border border-gray-100 p-6 hover:shadow-xl hover:shadow-secondary/5 hover:-translate-y-0.5 transition-all duration-300 overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-secondary-50 to-transparent rounded-bl-[60px] opacity-60"></div>
            <div class="relative">
                <div class="w-11 h-11 rounded-2xl bg-secondary-50 flex items-center justify-center mb-4">
                    <svg class="w-5.5 h-5.5 text-secondary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z"/></svg>
                </div>
                <p class="text-sm font-medium text-gray-400 mb-1">Total Ruangan</p>
                <p class="text-3xl font-extrabold text-gray-900">{{ number_format($totalRuangan) }}</p>
            </div>
        </div>

        <div class="group relative bg-white rounded-3xl border border-gray-100 p-6 hover:shadow-xl hover:shadow-blue-500/5 hover:-translate-y-0.5 transition-all duration-300 overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-blue-50 to-transparent rounded-bl-[60px] opacity-60"></div>
            <div class="relative">
                <div class="w-11 h-11 rounded-2xl bg-blue-50 flex items-center justify-center mb-4">
                    <svg class="w-5.5 h-5.5 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                </div>
                <p class="text-sm font-medium text-gray-400 mb-1">Total Agenda</p>
                <p class="text-3xl font-extrabold text-gray-900">{{ number_format($totalAgenda) }}</p>
            </div>
        </div>

        <div class="md:col-span-2 bg-white rounded-3xl border border-gray-100 p-6 hover:shadow-xl hover:shadow-gray-100/50 transition-all duration-300">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Distribusi Agenda</h3>
                    <p class="text-sm text-gray-400 mt-0.5">Berdasarkan tipe agenda yang aktif dipakai saat ini</p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-gray-50 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z"/></svg>
                </div>
            </div>

            <div class="space-y-5">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            <span class="text-sm font-semibold text-gray-700">Rapat</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900">{{ $rapatCount }}</span>
                    </div>
                    <div class="h-2.5 rounded-full bg-blue-50 overflow-hidden">
                        <div class="h-full rounded-full bg-blue-500" style="width: {{ ($rapatCount / $largestTypeCount) * 100 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-primary"></span>
                            <span class="text-sm font-semibold text-gray-700">Diklat</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900">{{ $diklatCount }}</span>
                    </div>
                    <div class="h-2.5 rounded-full bg-primary-50 overflow-hidden">
                        <div class="h-full rounded-full bg-primary" style="width: {{ ($diklatCount / $largestTypeCount) * 100 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-secondary"></span>
                            <span class="text-sm font-semibold text-gray-700">Pelatihan</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900">{{ $pelatihanCount }}</span>
                    </div>
                    <div class="h-2.5 rounded-full bg-secondary-50 overflow-hidden">
                        <div class="h-full rounded-full bg-secondary" style="width: {{ ($pelatihanCount / $largestTypeCount) * 100 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="md:col-span-2 bg-white rounded-3xl border border-gray-100 p-6 hover:shadow-xl hover:shadow-gray-100/50 transition-all duration-300">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Sorotan Struktur Agenda</h3>
                    <p class="text-sm text-gray-400 mt-0.5">Dashboard mengikuti struktur agenda baru tanpa status</p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-gray-50 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 3.75H9A2.25 2.25 0 006.75 6v12A2.25 2.25 0 009 20.25h6A2.25 2.25 0 0017.25 18V9.75L11.25 3.75z"/><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 3.75V9.75h6"/></svg>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Field Utama</p>
                    <p class="text-sm font-bold text-gray-900 mt-1">Unit & Pimpinan Acara</p>
                    <p class="text-xs text-gray-500 mt-1">Agenda sekarang menonjolkan unit penyelenggara dan pimpinan acara sebagai identitas utama.</p>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Tipe Rapat</p>
                    <p class="text-sm font-bold text-gray-900 mt-1">Tanpa Batas Pukul Selesai</p>
                    <p class="text-xs text-gray-500 mt-1">Rapat tidak lagi bergantung pada jam selesai dan notulensi dikelola setelah agenda berjalan.</p>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Tipe Diklat/Pelatihan</p>
                    <p class="text-sm font-bold text-gray-900 mt-1">Bisa Banyak Pemateri</p>
                    <p class="text-xs text-gray-500 mt-1">Diklat dan pelatihan mendukung pemateri dari data karyawan dan jam selesai agenda.</p>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Evaluasi</p>
                    <p class="text-sm font-bold text-gray-900 mt-1">Bank Soal Opsional</p>
                    <p class="text-xs text-gray-500 mt-1">Template bank soal dapat dipakai untuk diklat atau pelatihan tanpa wajib diisi.</p>
                </div>
            </div>
        </div>

        <div class="md:col-span-2 xl:col-span-1 bg-gradient-to-br from-gray-900 to-gray-800 rounded-3xl p-6 overflow-hidden relative hover:shadow-2xl hover:shadow-gray-900/20 transition-all duration-300">
            <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-bl from-primary/20 to-transparent rounded-bl-[80px]"></div>
            <div class="relative">
                <h3 class="text-base font-bold text-white mb-1">Aksi Cepat</h3>
                <p class="text-sm text-gray-400 mb-5">Buat data baru</p>

                <div class="space-y-2.5">
                    <a href="{{ route('admin.agendas.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-white/10 hover:bg-white/15 text-white transition-all duration-200 group">
                        <div class="w-8 h-8 rounded-xl bg-primary/30 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        </div>
                        <span class="text-sm font-semibold">Buat Agenda Baru</span>
                        <svg class="w-4 h-4 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </a>
                    <a href="{{ route('admin.employees.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-white/10 hover:bg-white/15 text-white transition-all duration-200 group">
                        <div class="w-8 h-8 rounded-xl bg-secondary/30 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
                        </div>
                        <span class="text-sm font-semibold">Tambah Pegawai</span>
                        <svg class="w-4 h-4 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </a>
                    <a href="{{ route('admin.rooms.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-white/10 hover:bg-white/15 text-white transition-all duration-200 group">
                        <div class="w-8 h-8 rounded-xl bg-blue-500/30 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z"/></svg>
                        </div>
                        <span class="text-sm font-semibold">Tambah Ruangan</span>
                        <svg class="w-4 h-4 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="md:col-span-2 xl:col-span-3 bg-white rounded-3xl border border-gray-100 p-6 hover:shadow-xl hover:shadow-gray-100/50 transition-all duration-300">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Aktivitas Terbaru</h3>
                    <p class="text-sm text-gray-400 mt-0.5">Agenda yang baru ditambahkan dengan struktur terbaru</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="pb-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Judul</th>
                            <th class="pb-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Tipe</th>
                            <th class="pb-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Unit</th>
                            <th class="pb-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Pimpinan Acara</th>
                            <th class="pb-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Waktu</th>
                            <th class="pb-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($recentAgendas as $agenda)
                            <tr class="group hover:bg-gray-50/50 transition-colors">
                                <td class="py-3.5 pr-4">
                                    <p class="text-sm font-semibold text-gray-800 group-hover:text-primary transition-colors">{{ $agenda->title }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $agenda->room->room_name ?? '-' }}</p>
                                </td>
                                <td class="py-3.5 pr-4">
                                    @if($agenda->type === 'rapat')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-600">Rapat</span>
                                    @elseif($agenda->type === 'diklat')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-primary-50 text-primary">Diklat</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-secondary-50 text-secondary-700">Pelatihan</span>
                                    @endif
                                </td>
                                <td class="py-3.5 pr-4">
                                    <span class="text-sm text-gray-500">{{ $agenda->unit->name ?? '-' }}</span>
                                </td>
                                <td class="py-3.5 pr-4">
                                    <span class="text-sm text-gray-500">{{ $agenda->eventLeader->full_name ?? '-' }}</span>
                                </td>
                                <td class="py-3.5 pr-4">
                                    <div class="text-sm text-gray-500">
                                        <div>{{ $agenda->event_date->translatedFormat('d M Y') }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            {{ \Carbon\Carbon::parse($agenda->event_time)->format('H:i') }}
                                            @if($agenda->event_end_time)
                                                - {{ \Carbon\Carbon::parse($agenda->event_end_time)->format('H:i') }}
                                            @endif
                                            WIB
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 text-right">
                                    <a href="{{ route('admin.agendas.show', $agenda) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:text-primary-700 transition-colors">
                                        Detail
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-sm text-gray-400">Belum ada data agenda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
