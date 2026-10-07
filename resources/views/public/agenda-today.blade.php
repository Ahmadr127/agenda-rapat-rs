<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agenda Hari Ini - RS AZRA</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-tab.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 min-h-screen font-sans text-slate-800">
    <div x-data="agendaSearch" class="pb-10">
        {{-- Hero header --}}
        <div class="bg-gradient-to-br from-primary to-primary-700 text-white px-5 md:px-8 pt-8 pb-6 shadow-lg">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 bg-white/20 rounded-2xl flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-base md:text-xl font-extrabold leading-tight tracking-tight">Agenda Hari Ini</h1>
                    <p class="text-primary-100/80 text-[11px] md:text-sm font-medium mt-0.5">{{ now()->translatedFormat('l, d F Y') }}</p>
                </div>
                <span class="ml-auto inline-flex items-center gap-1.5 bg-white/15 backdrop-blur-sm text-white text-[11px] md:text-xs font-bold px-3 py-1.5 rounded-full flex-shrink-0">
                    {{ $agendas->count() }} agenda
                </span>
            </div>

            {{-- Search --}}
            <div class="mt-5 relative">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 md:w-5 md:h-5 text-white/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" x-model="search" placeholder="Cari judul, pimpinan, ruangan, atau unit..."
                    class="w-full rounded-2xl border-0 bg-white/15 text-white placeholder-white/50 shadow-sm focus:ring-2 focus:ring-white/40 pl-11 md:pl-12 pr-10 py-3 md:py-3.5 text-xs md:text-sm font-medium">
                <button x-show="search" @click="search = ''" aria-label="Hapus pencarian"
                    class="absolute right-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Type filter chips --}}
            <div class="mt-3.5 flex items-center gap-2 overflow-x-auto pb-0.5">
                <template x-for="f in filters" :key="f.id">
                    <button type="button" @click="typeFilter = f.id"
                        class="flex-shrink-0 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-[11px] md:text-xs font-bold transition active:scale-95"
                        :class="typeFilter === f.id ? 'bg-white text-primary shadow-md' : 'bg-white/15 text-white/85 hover:bg-white/25'">
                        <span class="w-1.5 h-1.5 rounded-full" :class="f.dot"></span>
                        <span x-text="f.label"></span>
                    </button>
                </template>
            </div>
        </div>

        <main class="px-4 md:px-8 mt-5">
            @if($agendas->count())
                <div class="grid gap-3 xl:grid-cols-2 items-start">
                    @foreach($agendas as $agenda)
                        @php
                            $typeBadge = ['rapat' => 'bg-blue-50 text-blue-600', 'diklat' => 'bg-violet-50 text-violet-600', 'pelatihan' => 'bg-amber-50 text-amber-600'][$agenda->type] ?? 'bg-slate-100 text-slate-500';
                        @endphp
                        <article data-id="{{ $agenda->id }}"
                            x-show="isVisible({{ $agenda->id }})"
                            x-transition.opacity
                            onclick="window.location='{{ route('attendance.show', $agenda) }}'"
                            class="group bg-white rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md hover:border-primary/25 cursor-pointer transition-all active:scale-[0.99] overflow-hidden">
                            <div class="p-4 md:p-5 flex gap-3.5">
                                {{-- Time block --}}
                                <div class="flex-shrink-0 w-14 md:w-16 text-center rounded-2xl bg-slate-50 border border-slate-100 py-2.5 px-1 self-start">
                                    <p class="text-sm md:text-base font-extrabold text-slate-800 leading-none">{{ \Carbon\Carbon::parse($agenda->event_time)->format('H:i') }}</p>
                                    <p class="text-[9px] md:text-[10px] font-bold text-slate-400 mt-1 leading-none">
                                        @if($agenda->event_end_time)
                                            s/d {{ \Carbon\Carbon::parse($agenda->event_end_time)->format('H:i') }}
                                        @else
                                            WIB
                                        @endif
                                    </p>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h2 class="text-[13px] md:text-[15px] font-extrabold text-slate-900 leading-snug group-hover:text-primary transition-colors">{{ $agenda->title }}</h2>
                                    <div class="mt-2 space-y-1">
                                        <p class="flex items-center gap-1.5 text-[10px] md:text-xs text-slate-500 font-medium">
                                            <svg class="w-3.5 h-3.5 text-slate-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21"/></svg>
                                            <span class="truncate">{{ $agenda->room->room_name ?? '-' }} &middot; {{ $agenda->unit->name ?? '-' }}</span>
                                        </p>
                                        @if($agenda->eventLeader)
                                            <p class="flex items-center gap-1.5 text-[10px] md:text-xs text-slate-500 font-medium">
                                                <svg class="w-3.5 h-3.5 text-slate-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                <span class="truncate">{{ $agenda->eventLeader->full_name }}</span>
                                            </p>
                                        @endif
                                    </div>
                                    <div class="mt-2.5 flex items-center justify-end gap-1.5 flex-wrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] md:text-[10px] font-extrabold uppercase tracking-wide {{ $typeBadge }}">{{ ucfirst($agenda->type) }}</span>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[9px] md:text-[10px] font-bold bg-emerald-50 text-emerald-600">
                                            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                            {{ $agenda->signed_count }} hadir
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 self-center">
                                    <span class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 group-hover:bg-primary group-hover:text-white flex items-center justify-center transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                    </span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div x-show="(search || typeFilter !== 'semua') && !hasVisibleAgendas()" x-cloak class="text-center py-14">
                    <span class="w-14 h-14 rounded-3xl bg-white border border-slate-200 flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <p class="text-slate-600 font-bold text-sm">Agenda tidak ditemukan</p>
                    <p class="text-xs text-slate-400 mt-1">Coba kata kunci atau filter lain</p>
                    <button @click="search = ''; typeFilter = 'semua'" class="mt-4 px-5 py-2.5 rounded-2xl bg-white border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 active:scale-95 transition">
                        Atur ulang pencarian
                    </button>
                </div>
            @else
                <div class="text-center py-16">
                    <span class="w-16 h-16 rounded-3xl bg-white border border-slate-200 flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <p class="text-slate-600 font-bold text-sm">Tidak ada agenda hari ini</p>
                    <p class="text-xs text-slate-400 mt-1">Silakan periksa kembali nanti</p>
                </div>
            @endif
        </main>

        <footer class="text-center mt-8 px-4">
            <p class="text-[10px] md:text-xs text-slate-300 font-medium">D-ASSA &middot; Digital Agenda &amp; Attendance System</p>
        </footer>
    </div>

    @php
        $agendaItems = $agendas->map(function ($a) {
            return [
                'id' => $a->id,
                'title' => $a->title,
                'type' => $a->type,
                'organizer' => $a->eventLeader?->full_name ?? '',
                'room' => $a->room->room_name ?? '',
                'unit' => $a->unit?->name ?? '',
            ];
        });
    @endphp
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('agendaSearch', () => ({
                search: '',
                typeFilter: 'semua',
                filters: [
                    { id: 'semua', label: 'Semua', dot: 'bg-white' },
                    { id: 'rapat', label: 'Rapat', dot: 'bg-blue-300' },
                    { id: 'diklat', label: 'Diklat', dot: 'bg-violet-300' },
                    { id: 'pelatihan', label: 'Pelatihan', dot: 'bg-amber-300' },
                ],
                agendas: @json($agendaItems),

                matches(item, q) {
                    return (item.title || '').toLowerCase().includes(q)
                        || (item.organizer || '').toLowerCase().includes(q)
                        || (item.room || '').toLowerCase().includes(q)
                        || (item.unit || '').toLowerCase().includes(q);
                },

                isVisible(id) {
                    const a = this.agendas.find(item => item.id === id);
                    if (!a) return false;
                    if (this.typeFilter !== 'semua' && a.type !== this.typeFilter) return false;
                    if (!this.search) return true;
                    return this.matches(a, this.search.toLowerCase());
                },

                hasVisibleAgendas() {
                    const q = this.search.toLowerCase();
                    return this.agendas.some(a =>
                        (this.typeFilter === 'semua' || a.type === this.typeFilter)
                        && (!this.search || this.matches(a, q))
                    );
                }
            }));
        });
    </script>
</body>

</html>
