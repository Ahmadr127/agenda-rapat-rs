<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agenda Hari Ini - RS AZRA</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100/60 min-h-screen font-sans">
    <div x-data="agendaSearch" class="max-w-6xl mx-auto pb-8">
        {{-- Header --}}
        <div class="bg-gradient-to-br from-primary to-primary-700 text-white px-6 pt-8 pb-5 rounded-b-3xl shadow-lg">
            <div class="flex items-center gap-3 mb-1">
                <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-sm md:text-base font-bold leading-tight">Agenda Hari Ini</h1>
                    <p class="text-primary-100/70 text-[10px] md:text-xs mt-0.5">{{ now()->translatedFormat('l, d F Y') }}</p>
                </div>
            </div>

            {{-- Search --}}
            <div class="mt-4 relative">
                <div class="absolute left-3 md:left-4 top-1/2 -translate-y-1/2 pointer-events-none z-10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 md:w-5 md:h-5 text-white/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" x-model="search" placeholder="Cari agenda..."
                    class="w-full rounded-2xl border-0 bg-white/15 text-white placeholder-white/50 shadow-sm focus:ring-2 focus:ring-white/30 pl-9 py-1.5 md:pl-12 pr-4 md:py-3 text-[11px] md:text-xs">
            </div>
        </div>

        {{-- Agenda Count --}}
        <div class="px-5 mt-3 md:mt-5 md:mb-3">
            <span class="text-[10px] md:text-xs font-semibold text-gray-400">{{ $agendas->count() }} agenda hari ini</span>
        </div>

        <main class="px-4 py-2">
            @if($agendas->count())
                <div class="overflow-hidden rounded-lg border border-gray-200 shadow-sm">
                    <table class="w-full table-fixed text-[11px]">
                        <thead
                            class="bg-gradient-to-r from-[#0F766E] to-[#0D9488]">
                            <tr>
                                <th class="px-1 py-2 text-center text-[6px] md:text-[9px] font-semibold uppercase tracking-wider text-white border-r border-primary-500" style="width:30px">No</th>
                                <th class="px-1 py-2 text-left text-[6px] md:text-[9px] font-semibold uppercase tracking-wider text-white border-r border-primary-500">Agenda</th>
                                <th class="px-1 py-2 text-left text-[6px] md:text-[9px] font-semibold uppercase tracking-wider text-white border-r border-primary-500">Waktu</th>
                                <th class="px-1 py-2 text-left text-[6px] md:text-[9px] font-semibold uppercase tracking-wider text-white border-r border-primary-500">Ruangan</th>
                                <th class="px-1 py-2 text-left text-[6px] md:text-[9px] font-semibold uppercase tracking-wider text-white border-r border-primary-500">Penyelenggara</th>
                                <th class="px-1 py-2 text-center text-[6px] md:text-[9px] font-semibold uppercase tracking-wider text-white" style="width:50px">Hadir</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($agendas as $index => $agenda)
                                <tr data-id="{{ $agenda->id }}"
                                    x-show="isVisible({{ $agenda->id }})"
                                    onclick="window.location='{{ route('attendance.show', $agenda) }}'"
                                    class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-primary-50 cursor-pointer transition-colors">
                                    <td class="px-1.5 py-1 md:py-1.5 text-center text-gray-400 text-[7px] md:text-[9px]">{{ $index + 1 }}</td>
                                    <td class="px-1 py-1 md:py-1.5">
                                        <div class="text-[8px] md:text-[10px] font-semibold text-gray-900 break-words">{{ $agenda->title }}</div>
                                    </td>
                                    <td class="px-1.5 py-1 md:py-1.5 text-gray-600 text-[7px] md:text-[9px]">
                                        {{ \Carbon\Carbon::parse($agenda->event_time)->format('H.i') }}
                                        @if($agenda->event_end_time)
                                            <span class="text-gray-400">- {{ \Carbon\Carbon::parse($agenda->event_end_time)->format('H.i') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-1.5 py-1 md:py-1.5 text-gray-600 text-[7px] md:text-[9px]">{{ $agenda->room->room_name ?? '-' }}</td>
                                    <td class="px-1.5 py-1 md:py-1.5 text-gray-600 text-[7px] md:text-[9px]">{{ $agenda->unit->name ?? '-' }}</td>
                                    <td class="px-1.5 py-1 md:py-1.5 text-center">
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-lg font-bold bg-secondary-50 text-secondary-700 text-[7px] md:text-[9px]">
                                            {{ $agenda->signed_count }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div x-show="search && !hasVisibleAgendas()" x-cloak class="text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <p class="text-gray-600 font-medium">Agenda tidak ditemukan</p>
                    <p class="text-sm text-gray-400 mt-1">Coba kata kunci lain</p>
                </div>
            @else
                <div class="text-center py-16">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-gray-600 font-medium">Tidak ada agenda hari ini</p>
                    <p class="text-sm text-gray-400 mt-1">Silakan periksa kembali nanti</p>
                </div>
            @endif
        </main>

        <footer class="text-center py-6 px-4 border-t border-gray-100">
            <p class="text-xs text-gray-400">D-ASSA &middot; Digital Agenda & Attendance System</p>
        </footer>
    </div>

    @php
        $agendaItems = $agendas->map(function ($a) {
            return [
                'id' => $a->id,
                'title' => $a->title,
                'organizer' => $a->organizer?->full_name ?? '',
                'room' => $a->room->room_name ?? '',
                'unit' => $a->unit?->name ?? '',
            ];
        });
    @endphp
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('agendaSearch', () => ({
                search: '',
                agendas: @json($agendaItems),

                isVisible(id) {
                    if (!this.search) return true;
                    const q = this.search.toLowerCase();
                    const a = this.agendas.find(item => item.id === id);
                    if (!a) return false;
                    return (a.title || '').toLowerCase().includes(q)
                        || (a.organizer || '').toLowerCase().includes(q)
                        || (a.room || '').toLowerCase().includes(q)
                        || (a.unit || '').toLowerCase().includes(q);
                },

                hasVisibleAgendas() {
                    if (!this.search) return true;
                    const q = this.search.toLowerCase();
                    return this.agendas.some(a =>
                        (a.title || '').toLowerCase().includes(q)
                        || (a.organizer || '').toLowerCase().includes(q)
                        || (a.room || '').toLowerCase().includes(q)
                        || (a.unit || '').toLowerCase().includes(q)
                    );
                }
            }));
        });
    </script>
</body>

</html>
