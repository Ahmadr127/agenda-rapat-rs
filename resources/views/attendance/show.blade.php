<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Absensi - {{ $agenda->title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-tab.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="bg-slate-100 min-h-screen font-sans text-slate-800">
    <div x-data="attendanceApp()" x-init="init()" class="pb-10">
        {{-- Header agenda --}}
        <div class="bg-gradient-to-br from-primary to-primary-700 text-white px-5 md:px-8 pt-5 pb-6 shadow-lg">
            <div class="flex items-center justify-between gap-3 mb-3">
                <a href="{{ route('home') }}"
                    class="inline-flex items-center gap-1.5 text-white/70 text-[11px] md:text-xs font-bold hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Semua Agenda
                </a>
                @php
                    $showTypeBadge = ['rapat' => 'bg-blue-400/25 text-blue-50', 'diklat' => 'bg-violet-400/25 text-violet-50', 'pelatihan' => 'bg-amber-400/25 text-amber-50'][$agenda->type] ?? 'bg-white/15 text-white';
                @endphp
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] md:text-[11px] font-extrabold uppercase tracking-wide {{ $showTypeBadge }}">{{ ucfirst($agenda->type) }}</span>
            </div>
            <h1 class="text-base md:text-xl font-extrabold text-white leading-snug tracking-tight">{{ $agenda->title }}</h1>
            <dl class="mt-3.5 grid grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-2.5 text-[11px] md:text-xs">
                <div class="flex items-center gap-2 min-w-0">
                    <dt class="text-white/60 font-bold uppercase tracking-wide text-[9px] md:text-[10px] flex-shrink-0 w-14">Tanggal</dt>
                    <dd class="text-white font-bold truncate">{{ $agenda->event_date->translatedFormat('d M Y') }}</dd>
                </div>
                <div class="flex items-center gap-2 min-w-0">
                    <dt class="text-white/60 font-bold uppercase tracking-wide text-[9px] md:text-[10px] flex-shrink-0 w-14">Waktu</dt>
                    <dd class="text-white font-bold truncate">{{ \Carbon\Carbon::parse($agenda->event_time)->format('H:i') }}@if($agenda->event_end_time) – {{ \Carbon\Carbon::parse($agenda->event_end_time)->format('H:i') }}@endif</dd>
                </div>
                <div class="flex items-center gap-2 min-w-0">
                    <dt class="text-white/60 font-bold uppercase tracking-wide text-[9px] md:text-[10px] flex-shrink-0 w-14">Ruangan</dt>
                    <dd class="text-white font-bold truncate">{{ $agenda->room->room_name ?? '-' }}</dd>
                </div>
                <div class="flex items-center gap-2 min-w-0">
                    <dt class="text-white/60 font-bold uppercase tracking-wide text-[9px] md:text-[10px] flex-shrink-0 w-14">Unit</dt>
                    <dd class="text-white font-bold truncate">{{ $agenda->unit?->name ?? '-' }}</dd>
                </div>
                <div class="flex items-center gap-2 min-w-0 col-span-2 lg:col-span-1">
                    <dt class="text-white/60 font-bold uppercase tracking-wide text-[9px] md:text-[10px] flex-shrink-0 w-14">Pimpinan</dt>
                    <dd class="text-white font-bold truncate">{{ $agenda->eventLeader?->full_name ?? '-' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Menu cepat: full-width & responsif --}}
        <nav aria-label="Menu agenda" class="px-4 md:px-8 mt-4 grid grid-cols-1 sm:grid-cols-3 gap-2.5">
            @if($agenda->allowsNotes())
                <a href="{{ route('agenda.note.index', $agenda) }}"
                    class="group flex items-center gap-3 w-full p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:border-violet-300 hover:shadow-md active:scale-[0.99] transition-all">
                    <span class="w-11 h-11 rounded-2xl bg-violet-50 text-violet-500 flex items-center justify-center flex-shrink-0 group-hover:bg-violet-500 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1 text-left">
                        <span class="block text-xs md:text-sm font-extrabold text-slate-800">Notulensi</span>
                        <span class="block text-[10px] md:text-[11px] text-slate-400 font-medium mt-0.5">Catat pembahasan rapat</span>
                    </span>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-violet-400 group-hover:translate-x-0.5 transition-all flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
            @endif
            <a href="{{ route('agenda.image.index', $agenda) }}"
                class="group flex items-center gap-3 w-full p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:border-emerald-300 hover:shadow-md active:scale-[0.99] transition-all">
                <span class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M2.25 18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V6a2.25 2.25 0 00-2.25-2.25h-15A2.25 2.25 0 002.25 6v12z" />
                    </svg>
                </span>
                <span class="min-w-0 flex-1 text-left">
                    <span class="block text-xs md:text-sm font-extrabold text-slate-800">Foto Dokumentasi</span>
                    <span class="block text-[10px] md:text-[11px] text-slate-400 font-medium mt-0.5">Unggah foto kegiatan</span>
                </span>
                <svg class="w-4 h-4 text-slate-300 group-hover:text-emerald-400 group-hover:translate-x-0.5 transition-all flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </a>
            @if($agenda->allowsQuiz() && $agenda->agendaQuestions->count() > 0)
                <a href="{{ route('attendance.quiz', $agenda) }}"
                    class="group flex items-center gap-3 w-full p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:border-amber-300 hover:shadow-md active:scale-[0.99] transition-all">
                    <span class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center flex-shrink-0 group-hover:bg-amber-500 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1 text-left">
                        <span class="block text-xs md:text-sm font-extrabold text-slate-800">Posttest</span>
                        <span class="block text-[10px] md:text-[11px] text-slate-400 font-medium mt-0.5">Kerjakan soal akhir</span>
                    </span>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-amber-400 group-hover:translate-x-0.5 transition-all flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
            @endif
        </nav>

        <div class="px-4 md:px-8 mt-6 space-y-5">
            {{-- Stepper --}}
            <ol class="flex items-center gap-1.5 md:gap-2 text-[10px] md:text-xs font-bold">
                <template x-for="(s, i) in attendanceSteps" :key="i">
                    <li class="flex items-center gap-1.5 md:gap-2 flex-1 last:flex-none">
                        <span class="flex items-center gap-1.5 md:gap-2">
                            <span class="w-5 h-5 md:w-6 md:h-6 rounded-full flex items-center justify-center text-[10px] md:text-[11px] transition-colors"
                                :class="attendanceStepIndex > i ? 'bg-emerald-500 text-white' : (attendanceStepIndex === i ? 'bg-primary text-white shadow-md shadow-primary/30' : 'bg-slate-200 text-slate-400')">
                                <svg x-show="attendanceStepIndex > i" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span x-show="attendanceStepIndex <= i" x-text="i + 1"></span>
                            </span>
                            <span :class="attendanceStepIndex >= i ? 'text-slate-700' : 'text-slate-400'" x-text="s"></span>
                        </span>
                        <span x-show="i < attendanceSteps.length - 1" class="flex-1 h-0.5 rounded-full mx-1" :class="attendanceStepIndex > i ? 'bg-emerald-400' : 'bg-slate-200'"></span>
                    </li>
                </template>
            </ol>

            {{-- ===== ATTENDANCE STEP ===== --}}
            <div x-show="currentStep === 'attendance'" class="space-y-5">
                {{-- Search card --}}
                <section class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <div class="px-5 md:px-6 pt-5 pb-4 border-b border-slate-100 flex items-center gap-3.5">
                        <span class="w-10 h-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-sm md:text-base font-extrabold text-slate-900">Absen Kehadiran</h2>
                            <p class="text-[11px] md:text-xs text-slate-400 font-medium mt-0.5">Cari nama Anda, lalu bubuhkan tanda tangan</p>
                        </div>
                    </div>
                    <div class="p-5 md:p-6">
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4 md:w-5 md:h-5 text-slate-300 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" x-model="search" placeholder="Ketik minimal 2 huruf nama Anda..."
                                class="w-full rounded-2xl border-slate-200 bg-slate-50/60 shadow-sm focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none pl-11 md:pl-12 pr-10 py-3 md:py-3.5 text-xs md:text-sm font-medium transition">
                            <button x-show="search" @click="search = ''" aria-label="Hapus pencarian"
                                class="absolute right-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-slate-200 hover:bg-slate-300 text-slate-500 flex items-center justify-center transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        {{-- Search Results --}}
                        <div x-show="search.length >= 2" x-cloak class="mt-3 space-y-2 max-h-72 overflow-y-auto pr-0.5">
                            <template x-for="p in filteredEmployees" :key="p.id">
                                <button type="button" @click="openSignModal(p)" :disabled="p.signed_at && !needsPretest(p)"
                                    class="w-full rounded-2xl p-3.5 border text-left transition-all duration-200 flex items-center gap-3"
                                    :class="(p.signed_at && !needsPretest(p))
                                        ? 'bg-slate-50 border-slate-100 opacity-70'
                                        : (needsPretest(p)
                                            ? 'bg-amber-50/60 border-amber-300 cursor-pointer hover:shadow-md active:scale-[0.99]'
                                            : 'bg-white border-slate-200 cursor-pointer hover:border-primary/40 hover:shadow-md hover:shadow-primary/5 active:scale-[0.99]')">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-extrabold flex-shrink-0"
                                        :class="p.signed_at ? 'bg-slate-100 text-slate-400' : 'bg-primary/10 text-primary'" x-text="p.name.charAt(0).toUpperCase()"></span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block font-bold text-slate-900 text-xs md:text-sm truncate" x-text="p.name"></span>
                                        <span class="block text-[10px] md:text-xs text-slate-400 font-medium mt-0.5 truncate" x-text="p.position + ' • ' + p.organization"></span>
                                    </span>
                                    <span class="flex-shrink-0">
                                        <template x-if="needsPretest(p)">
                                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-[10px] md:text-xs font-bold bg-amber-500 text-white shadow-md shadow-amber-500/25">
                                                Lanjutkan Pretest
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                                </svg>
                                            </span>
                                        </template>
                                        <template x-if="p.signed_at && !needsPretest(p)">
                                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-[10px] md:text-xs font-bold bg-emerald-50 text-emerald-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Sudah Hadir
                                            </span>
                                        </template>
                                        <template x-if="!p.signed_at">
                                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-[10px] md:text-xs font-bold bg-primary text-white shadow-md shadow-primary/25">
                                                Absen
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                                </svg>
                                            </span>
                                        </template>
                                    </span>
                                </button>
                            </template>

                            <template x-if="filteredEmployees.length === 0 && search.length >= 2">
                                <div class="text-center py-8">
                                    <span class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-300 flex items-center justify-center mx-auto mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </span>
                                    <p class="text-slate-500 text-xs md:text-sm font-bold">Nama tidak ditemukan</p>
                                    <p class="text-slate-400 text-[11px] md:text-xs mt-1">Periksa ejaan atau hubungi panitia</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </section>

                {{-- Attendee list --}}
                <section class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <div class="px-5 md:px-6 pt-5 pb-4 border-b border-slate-100 flex items-center gap-3.5">
                        <span class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-sm md:text-base font-extrabold text-slate-900">
                                Sudah Hadir
                                <span class="ml-1.5 inline-flex items-center px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-600 text-[10px] md:text-[11px] font-extrabold align-middle" x-text="attendees.length"></span>
                            </h2>
                            <p class="text-[11px] md:text-xs text-slate-400 font-medium mt-0.5">Daftar peserta yang telah mengisi absensi</p>
                        </div>
                    </div>

                    <div x-show="attendees.length > 0" class="p-4 md:p-5 grid sm:grid-cols-2 xl:grid-cols-3 gap-2.5">
                        <template x-for="(a, index) in attendees" :key="a.id">
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-3 flex items-center gap-3">
                                <span class="w-10 h-10 rounded-xl bg-white border border-slate-100 text-primary flex items-center justify-center text-sm font-extrabold flex-shrink-0" x-text="a.name.charAt(0).toUpperCase()"></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs md:text-[13px] font-bold text-slate-800 truncate" x-text="a.name"></p>
                                    <p class="text-[10px] md:text-[11px] text-slate-400 font-medium truncate" x-text="a.position"></p>
                                    <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-lg bg-white border border-slate-100 text-slate-400 text-[10px] font-bold" x-text="'Hadir pukul ' + a.signed_at"></span>
                                </div>
                                <button type="button"
                                    @click="$dispatch('open-signature-preview', { name: a.name, url: a.signature_url })"
                                    class="flex-shrink-0 rounded-xl overflow-hidden border border-slate-200 bg-white cursor-zoom-in transition hover:ring-2 hover:ring-primary/40 active:scale-95"
                                    :title="'Lihat tanda tangan ' + a.name">
                                    <img :src="a.signature_url" :alt="'TTD ' + a.name" class="h-10 w-16 object-contain pointer-events-none">
                                </button>
                            </div>
                        </template>
                    </div>

                    <div x-show="attendees.length === 0" class="py-10 px-6 text-center">
                        <span class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-300 flex items-center justify-center mx-auto mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-1.053M18 8.625a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4.5 11.25a3.375 3.375 0 116.75 0 3.375 3.375 0 01-6.75 0z" />
                            </svg>
                        </span>
                        <p class="text-slate-500 text-xs md:text-sm font-bold">Belum ada peserta yang hadir</p>
                        <p class="text-slate-400 text-[11px] md:text-xs mt-1">Gunakan pencarian di atas untuk melakukan absensi</p>
                    </div>
                </section>
            </div>

            {{-- ===== PRETEST STEP ===== --}}
            <div x-show="currentStep === 'pretest'" x-cloak class="space-y-5">
                {{-- Peserta + progress --}}
                <section class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <div class="p-4 md:p-5 flex items-center gap-3">
                        <span class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-extrabold flex-shrink-0"
                            x-text="(selectedEmployee?.name || '?').charAt(0).toUpperCase()"></span>
                        <div class="min-w-0 flex-1">
                            <p class="font-extrabold text-slate-900 text-xs md:text-sm truncate" x-text="selectedEmployee?.name"></p>
                            <p class="text-[10px] md:text-xs text-slate-400 font-medium truncate" x-text="selectedEmployee?.position + ' • ' + selectedEmployee?.organization"></p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-[10px] md:text-xs font-bold bg-blue-50 text-blue-600">
                                Pretest
                            </span>
                            <button type="button" @click="backToAttendance()"
                                class="text-[10px] md:text-xs text-slate-400 hover:text-slate-600 font-bold px-2 py-1.5 transition">
                                Kembali
                            </button>
                        </div>
                    </div>
                    <div class="px-4 md:px-5 pb-4">
                        <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 transition-all duration-200"
                                :style="'width: ' + (questions.length ? Math.round(pretestAnsweredCount / questions.length * 100) : 0) + '%'"></div>
                        </div>
                        <p class="text-[10px] md:text-xs text-slate-400 font-semibold text-center mt-1.5">
                            <span class="text-slate-700 font-bold" x-text="pretestAnsweredCount + ' dari ' + questions.length"></span> soal dijawab
                        </p>
                    </div>
                </section>

                {{-- Info --}}
                <div class="rounded-3xl border border-blue-100 bg-gradient-to-r from-blue-50 to-indigo-50 p-4 md:p-5 flex items-start gap-3">
                    <span class="w-9 h-9 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-xs md:text-sm font-extrabold text-blue-900">Pretest — Sebelum Pelatihan</h3>
                        <p class="text-[11px] md:text-xs text-blue-700/80 font-medium mt-0.5">Jawab untuk mengukur pemahaman awal Anda. Soal yang sama akan diberikan kembali di akhir pelatihan (posttest).</p>
                    </div>
                </div>

                {{-- Questions --}}
                <div class="grid gap-3.5 xl:grid-cols-2 items-start">
                    <template x-for="(q, index) in questions" :key="q.id">
                        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5"
                            :class="pretestAnswers[q.id] && 'border-emerald-200'">
                            <div class="flex items-start gap-3 mb-4">
                                <span class="shrink-0 w-8 h-8 rounded-xl flex items-center justify-center text-xs font-extrabold transition-colors"
                                    :class="pretestAnswers[q.id] ? 'bg-emerald-500 text-white' : 'bg-blue-50 text-blue-600'"
                                    x-text="index + 1"></span>
                                <p class="text-xs md:text-sm text-slate-800 font-semibold leading-relaxed" x-text="q.question_text"></p>
                            </div>
                            <div class="space-y-2 sm:ml-11">
                                <template x-for="opt in ['a','b','c','d','e']" :key="opt">
                                    <label x-show="q['option_' + opt]"
                                        class="flex items-start gap-3 p-3 rounded-2xl border cursor-pointer transition-all duration-200"
                                        :class="pretestAnswers[q.id] === opt
                                            ? 'border-blue-400 bg-blue-50/60 ring-1 ring-blue-200'
                                            : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50/60'">
                                        <input type="radio" :name="'pretest_q_' + q.id" :value="opt"
                                            x-model="pretestAnswers[q.id]"
                                            class="mt-1 w-4 h-4 text-blue-600 focus:ring-blue-500 border-slate-300">
                                        <span class="min-w-0 text-[11px] md:text-sm text-slate-700 font-medium"><span class="font-extrabold text-slate-400 uppercase mr-1.5" x-text="opt + '.'"></span><span x-text="q['option_' + opt]"></span></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <button @click="submitPretest()" :disabled="pretestSubmitting || !allPretestAnswered"
                    class="w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-xs md:text-sm font-extrabold rounded-2xl hover:from-blue-600 hover:to-indigo-700 transition disabled:opacity-50 active:scale-[0.99] shadow-lg shadow-blue-500/25">
                    <svg x-show="pretestSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span x-show="!pretestSubmitting">Kirim Pretest</span>
                    <span x-show="pretestSubmitting">Mengirim...</span>
                </button>
                <p class="text-center text-[11px] md:text-xs font-semibold -mt-2" :class="allPretestAnswered ? 'text-emerald-500' : 'text-amber-500'">
                    <span x-show="!allPretestAnswered" x-text="pretestAnsweredCount + ' dari ' + questions.length + ' soal dijawab'"></span>
                    <span x-show="allPretestAnswered">Semua soal sudah dijawab — siap dikirim</span>
                </p>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center mt-8 px-4">
            <p class="text-[10px] md:text-xs text-slate-300 font-medium">D-ASSA &middot; Digital Agenda &amp; Attendance System</p>
        </div>

        {{-- Signature Modal --}}
        <div x-show="showModal" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 flex items-end sm:items-center justify-center p-4"
            @click.self="closeModal()">
            <div x-show="showModal" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="translate-y-8 opacity-0 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-y-0 opacity-100 sm:scale-100"
                x-transition:leave-end="translate-y-8 opacity-0 sm:translate-y-0 sm:scale-95"
                class="bg-white rounded-3xl w-full max-w-md p-5 md:p-6 shadow-2xl" @click.stop>
                <div class="flex items-center gap-3 mb-1">
                    <span class="w-10 h-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center text-sm font-extrabold flex-shrink-0"
                        x-text="(selectedEmployee?.name || '?').charAt(0).toUpperCase()"></span>
                    <div class="min-w-0">
                        <h3 class="text-sm md:text-base font-extrabold text-slate-900">Tanda Tangan</h3>
                        <p class="text-[11px] md:text-xs text-slate-400 font-medium truncate" x-text="selectedEmployee?.name"></p>
                    </div>
                </div>
                <p class="text-[11px] md:text-xs text-slate-400 font-medium my-3">Bubuhkan tanda tangan pada kolom di bawah ini</p>

                <div class="border-2 border-dashed border-slate-200 rounded-2xl mb-4 bg-slate-50 overflow-hidden focus-within:border-primary/40 transition-colors">
                    <canvas id="signature-canvas" class="w-full bg-white"
                        style="height: 200px; touch-action: none;"></canvas>
                </div>

                <div class="flex gap-2.5">
                    <button @click="clearPad()"
                        class="flex-1 px-4 py-3 border border-slate-200 text-slate-600 text-xs md:text-sm font-bold rounded-2xl hover:bg-slate-50 transition active:scale-[0.98]">
                        Hapus
                    </button>
                    <button @click="submitSignature()" :disabled="submitting"
                        class="flex-[2] inline-flex items-center justify-center gap-2 px-4 py-3 bg-primary text-white text-xs md:text-sm font-extrabold rounded-2xl hover:bg-primary-700 shadow-lg shadow-primary/25 transition disabled:opacity-50 active:scale-[0.98]">
                        <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span x-show="!submitting">Simpan Absensi</span>
                        <span x-show="submitting">Menyimpan...</span>
                    </button>
                </div>

                <button @click="closeModal()"
                    class="mt-2.5 w-full text-center text-[11px] md:text-xs text-slate-400 hover:text-slate-600 font-semibold transition py-1">
                    Batal
                </button>
            </div>
        </div>

        {{-- Success Toast --}}
        <div x-show="showToast" x-transition x-cloak
            class="fixed top-4 left-1/2 -translate-x-1/2 bg-emerald-600 text-white pl-4 pr-5 py-3 rounded-2xl shadow-xl z-50 text-xs md:text-sm font-bold flex items-center gap-2.5 max-w-[calc(100vw-2rem)]">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span x-text="toastMessage">Absensi berhasil disimpan!</span>
        </div>

        {{-- Error Toast --}}
        <div x-show="showError" x-transition x-cloak
            class="fixed top-4 left-1/2 -translate-x-1/2 bg-rose-500 text-white pl-4 pr-5 py-3 rounded-2xl shadow-xl z-50 text-xs md:text-sm font-bold flex items-center gap-2.5 max-w-[calc(100vw-2rem)]">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span x-text="errorMessage"></span>
        </div>

        {{-- Reusable modal preview tanda tangan --}}
        <x-signature-preview-modal />
    </div>

    <script>
        function attendanceApp() {
            return {
                currentStep: 'attendance', // 'attendance' | 'pretest'
                attendanceSteps: ['Absensi', 'Tanda Tangan', 'Pretest'],
                search: '',
                employees: @json($employeesJson),
                attendees: @json($attendeesJson),
                questions: @json($questionsJson),
                pretestCompletedIds: @json($pretestCompletedIds),
                showModal: false,
                selectedEmployee: null,
                signaturePad: null,
                submitting: false,
                showToast: false,
                toastMessage: '',
                showError: false,
                errorMessage: '',
                agendaId: {{ $agenda->id }},
                hasQuiz: {{ $agenda->allowsQuiz() && $agenda->agendaQuestions->count() > 0 ? 'true' : 'false' }},

                // Pretest state
                pretestAnswers: {},
                pretestSubmitting: false,
                stopDraftWatch: null,

                get attendanceStepIndex() {
                    if (this.currentStep === 'pretest') return 2;
                    return this.showModal ? 1 : 0;
                },

                init() {
                    @if(Session::has('success'))
                        this.showSuccessToast('{{ Session::get('success') }}');
                    @endif
                },

                get filteredEmployees() {
                    if (this.search.length < 2) return [];
                    const q = this.search.toLowerCase();
                    return this.employees.filter(p => p.name.toLowerCase().includes(q));
                },

                get pretestAnsweredCount() {
                    return Object.keys(this.pretestAnswers).filter(k => this.pretestAnswers[k]).length;
                },

                get allPretestAnswered() {
                    return this.pretestAnsweredCount === this.questions.length;
                },

                openSignModal(employee) {
                    if (employee.signed_at) {
                        // Sudah absen tapi pretest belum dikerjakan
                        // (mis. browser crash setelah TTD) → lanjutkan pretest.
                        if (this.needsPretest(employee)) {
                            this.resumePretest(employee);
                        }
                        return;
                    }
                    this.selectedEmployee = employee;
                    this.showModal = true;
                    this.$nextTick(() => {
                        setTimeout(() => {
                            const canvas = document.getElementById('signature-canvas');
                            const rect = canvas.getBoundingClientRect();
                            canvas.width = rect.width || canvas.parentElement.offsetWidth;
                            canvas.height = 200;
                            this.signaturePad = new window.SignaturePad(canvas, {
                                backgroundColor: 'rgb(255, 255, 255)',
                                penColor: 'rgb(0, 0, 0)',
                            });
                        }, 50);
                    });
                },

                closeModal() {
                    this.showModal = false;
                    if (this.signaturePad) {
                        this.signaturePad.clear();
                        this.signaturePad = null;
                    }
                },

                clearPad() {
                    if (this.signaturePad) this.signaturePad.clear();
                },

                async submitSignature() {
                    if (!this.signaturePad || this.signaturePad.isEmpty()) {
                        this.showErrorToast('Silakan tanda tangan terlebih dahulu.');
                        return;
                    }

                    this.submitting = true;
                    const blob = await (await fetch(this.signaturePad.toDataURL('image/png'))).blob();

                    const form = new FormData();
                    form.append('employee_id', this.selectedEmployee.id);
                    form.append('signature', blob, 'signature.png');

                    try {
                        const response = await fetch(`/absen/${this.agendaId}/sign`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                            body: form,
                        });

                        const data = await response.json();

                        if (response.ok) {
                            // Mark employee as signed
                            this.selectedEmployee.signed_at = true;

                            // Add to attendees list
                            if (data.attendee) {
                                this.attendees.push(data.attendee);
                            }

                            this.closeModal();
                            this.search = '';

                            // If agenda has quiz and pretest not yet done, go to pretest
                            if (this.hasQuiz && data.show_pretest) {
                                this.initPretest();
                                this.currentStep = 'pretest';
                                this.showSuccessToast('Absensi berhasil! Silakan kerjakan pretest.');
                            } else {
                                this.showSuccessToast('Absensi berhasil disimpan!');
                            }
                        } else {
                            this.showErrorToast(data.message || 'Terjadi kesalahan.');
                        }
                    } catch (e) {
                        this.showErrorToast('Gagal menghubungi server.');
                    } finally {
                        this.submitting = false;
                    }
                },

                needsPretest(employee) {
                    return this.hasQuiz
                        && !!employee.signed_at
                        && !this.pretestCompletedIds.includes(employee.id);
                },

                resumePretest(employee) {
                    this.selectedEmployee = employee;
                    const restored = this.initPretest();
                    this.currentStep = 'pretest';
                    if (restored > 0) {
                        this.showSuccessToast('Jawaban tersimpan (' + restored + ' soal) dipulihkan.');
                    }
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                // --- Draf jawaban tersimpan di browser (per agenda + peserta) ---
                draftKey() {
                    return 'pretest-draft-' + this.agendaId + '-' + this.selectedEmployee?.id;
                },

                storeGet(key) {
                    try {
                        const raw = localStorage.getItem(key);
                        return raw ? JSON.parse(raw) : null;
                    } catch { return null; }
                },

                storeSet(key, value) {
                    try { localStorage.setItem(key, JSON.stringify(value)); } catch { /* abaikan */ }
                },

                storeDel(key) {
                    try { localStorage.removeItem(key); } catch { /* abaikan */ }
                },

                watchDraft(getter, initial) {
                    if (this.stopDraftWatch) this.stopDraftWatch();
                    this.stopDraftWatch = this.$watch(
                        getter,
                        (value) => this.storeSet(this.draftKey(), value),
                    );
                    this.storeSet(this.draftKey(), initial);
                },

                initPretest() {
                    this.pretestAnswers = {};
                    this.questions.forEach(q => {
                        this.pretestAnswers[q.id] = '';
                    });
                    // Pulihkan draf bila browser sempat crash saat mengisi.
                    const saved = this.storeGet(this.draftKey()) || {};
                    let restored = 0;
                    Object.keys(this.pretestAnswers).forEach(id => {
                        if (saved[id]) {
                            this.pretestAnswers[id] = saved[id];
                            restored++;
                        }
                    });
                    this.watchDraft(
                        () => JSON.stringify(this.pretestAnswers),
                        { ...this.pretestAnswers },
                    );
                    return restored;
                },

                async submitPretest() {
                    if (!this.allPretestAnswered || this.pretestSubmitting) return;

                    this.pretestSubmitting = true;

                    try {
                        const response = await fetch(`/absen/${this.agendaId}/pretest`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                employee_id: this.selectedEmployee.id,
                                answers: this.pretestAnswers,
                            }),
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.pretestCompletedIds.push(this.selectedEmployee.id);
                            this.storeDel(this.draftKey());
                            if (this.stopDraftWatch) {
                                this.stopDraftWatch();
                                this.stopDraftWatch = null;
                            }
                            this.currentStep = 'attendance';
                            this.showSuccessToast('Pretest berhasil disimpan!');
                        } else {
                            this.showErrorToast(data.message || 'Terjadi kesalahan.');
                        }
                    } catch (e) {
                        this.showErrorToast('Gagal menghubungi server.');
                    } finally {
                        this.pretestSubmitting = false;
                    }
                },

                backToAttendance() {
                    this.currentStep = 'attendance';
                    this.selectedEmployee = null;
                    this.pretestAnswers = {};
                },

                showSuccessToast(msg) {
                    this.toastMessage = msg;
                    this.showToast = true;
                    setTimeout(() => this.showToast = false, 3000);
                },

                showErrorToast(msg) {
                    this.errorMessage = msg;
                    this.showError = true;
                    setTimeout(() => this.showError = false, 3000);
                },
            };
        }
    </script>
</body>

</html>
