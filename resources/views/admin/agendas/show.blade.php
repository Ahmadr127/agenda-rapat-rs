<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.agendas.index') }}"
                class="text-gray-400 hover:text-primary transition-colors">Agenda</a>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
            <span class="font-semibold text-gray-700">Detail Agenda</span>
        </div>
    </x-slot>

    <div class="space-y-6"
        x-data="{ showSignatureModal: false, signatureName: '', signatureUrl: '', showImageModal: false, imageUrl: '', currentImageIndex: 0 }">

        {{-- ===== HEADER CARD ===== --}}
        <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
            <div class="p-8">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h2 class="text-xl font-extrabold text-gray-900">{{ $agenda->title }}</h2>
                        </div>
                        @if($agenda->description)
                            <span
                                class="text-sm text-gray-500 max-w-xl whitespace-pre-wrap">{{ $agenda->description }}</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a href="{{ route('admin.agendas.export-pdf', $agenda) }}"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-amber-500 text-white text-sm font-bold shadow-md shadow-amber-500/20 hover:bg-amber-600 active:scale-[0.98] transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Export PDF
                        </a>
                        <a href="{{ route('admin.agendas.edit', $agenda) }}"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-gray-100 text-gray-700 text-sm font-bold hover:bg-gray-200 active:scale-[0.98] transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                            </svg>
                            Edit
                        </a>
                    </div>
                </div>

                {{-- Info Grid --}}
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    <div class="p-4 rounded-2xl border border-gray-100 bg-primary/5">
                        <div class="flex items-center gap-2 mb-1.5">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                            <span class="text-[11px] font-semibold text-primary uppercase bg-primary/10 px-2 py-0.5 rounded-lg">Tanggal</span>
                        </div>
                        <p class="text-sm font-bold text-gray-800">{{ $agenda->event_date->translatedFormat('d F Y') }}
                        </p>
                    </div>
                    <div class="p-4 rounded-2xl border border-gray-100 bg-secondary/5">
                        <div class="flex items-center gap-2 mb-1.5">
                            <svg class="w-4 h-4 text-secondary" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-[11px] font-semibold text-secondary uppercase bg-secondary/10 px-2 py-0.5 rounded-lg">Waktu</span>
                        </div>
                        <p class="text-sm font-bold text-gray-800">
                            {{ \Carbon\Carbon::parse($agenda->event_time)->format('H:i') }}@if($agenda->event_end_time) – {{ \Carbon\Carbon::parse($agenda->event_end_time)->format('H:i') }}@endif WIB
                        </p>
                    </div>
                    <div class="p-4 rounded-2xl border border-gray-100 bg-violet-50/50">
                        <div class="flex items-center gap-2 mb-1.5">
                            <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21" />
                            </svg>
                            <span class="text-[11px] font-semibold text-violet-500 uppercase bg-violet-50 px-2 py-0.5 rounded-lg">Ruangan</span>
                        </div>
                        <p class="text-sm font-bold text-gray-800">{{ $agenda->room->room_name ?? '-' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl border border-gray-100 bg-amber-50/50">
                        <div class="flex items-center gap-2 mb-1.5">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                            </svg>
                            <span class="text-[11px] font-semibold text-amber-500 uppercase bg-amber-50 px-2 py-0.5 rounded-lg">Unit</span>
                        </div>
                        <p class="text-sm font-bold text-gray-800">{{ $agenda->unit?->name ?? '-' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl border border-gray-100 bg-rose-50/50">
                        <div class="flex items-center gap-2 mb-1.5">
                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                            </svg>
                            <span class="text-[11px] font-semibold text-rose-500 uppercase bg-rose-50 px-2 py-0.5 rounded-lg">Pimpinan {{ $agenda->type }}</span>
                        </div>
                        <p class="text-sm font-bold text-gray-800">{{ $agenda->eventLeader?->full_name ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== MEETING FILES SECTION ===== --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-3xl border border-gray-100 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-rose-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-800">Surat Undangan</h4>
                        <p class="text-xs text-gray-400">File PDF surat undangan rapat</p>
                    </div>
                </div>
                @if($agenda->letter_file_path)
                    <a href="{{ Storage::url($agenda->letter_file_path) }}" target="_blank"
                        class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-rose-50 text-rose-600 text-sm font-bold hover:bg-rose-100 active:scale-[0.98] transition-all duration-200 w-full justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Lihat Surat Undangan
                    </a>
                @else
                    <div class="px-5 py-3 rounded-2xl bg-gray-50 border-2 border-dashed border-gray-200 text-center">
                        <p class="text-sm text-gray-400">Belum ada file surat</p>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-800">Materi Rapat</h4>
                        <p class="text-xs text-gray-400">File materi presentasi</p>
                    </div>
                </div>
                @if($agenda->material_file_path)
                    <a href="{{ Storage::url($agenda->material_file_path) }}" target="_blank"
                        class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-blue-50 text-blue-600 text-sm font-bold hover:bg-blue-100 active:scale-[0.98] transition-all duration-200 w-full justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Lihat Materi Rapat
                    </a>
                @else
                    <div class="px-5 py-3 rounded-2xl bg-gray-50 border-2 border-dashed border-gray-200 text-center">
                        <p class="text-sm text-gray-400">Belum ada file materi</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ===== SOAL SECTION (Diklat/Pelatihan only) ===== --}}
        @if($agenda->allowsQuiz() && $agenda->agendaQuestions->count() > 0)
            <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-violet-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Hasil Pretest & Posttest</h3>
                            <p class="text-sm text-gray-400 mt-0.5">
                                @if($agenda->bankSoal)
                                    {{ $agenda->bankSoal->title }}
                                @else
                                    Template telah dihapus
                                @endif
                                &mdash; {{ $agenda->agendaQuestions->count() }} soal
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.agendas.export-quiz-csv', $agenda) }}"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-emerald-50 text-emerald-600 text-sm font-bold hover:bg-emerald-100 active:scale-[0.98] transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Export CSV
                        </a>
                        @if($agenda->bankSoal)
                            <a href="{{ route('admin.bank-soals.show', $agenda->bankSoal) }}"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-violet-50 text-violet-600 text-sm font-bold hover:bg-violet-100 active:scale-[0.98] transition-all duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Lihat Soal
                            </a>
                        @endif
                    </div>
                </div>

                <div class="px-8 py-6 rounded-2xl">
                    @if($quizComparison->count() > 0 && $quizStats)
                        {{-- Unified Table --}}
                        <div class="overflow-x-auto">
                            <div class="rounded-2xl border border-gray-100 shadow-sm" style="max-height: 400px; overflow-y: auto;">
                                <table class="min-w-full">
                                    <thead class="bg-primary-700 text-white" style="position: sticky; top: 0; z-index: 1;">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider w-10">No</th>
                                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider">Nama</th>
                                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider">Jabatan</th>
                                        <th class="px-2 py-3 text-center text-xs font-semibold uppercase tracking-wider">Pretest Benar</th>
                                        <th class="px-2 py-3 text-center text-xs font-semibold uppercase tracking-wider">Pretest Nilai</th>
                                        <th class="px-2 py-3 text-center text-xs font-semibold uppercase tracking-wider">Posttest Benar</th>
                                        <th class="px-2 py-3 text-center text-xs font-semibold uppercase tracking-wider">Posttest Nilai</th>
                                        <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider">Perubahan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($quizComparison as $index => $row)
                                        <tr class="transition-colors hover:bg-primary-50/40 {{ $index % 2 === 0 ? 'bg-white' : 'bg-slate-50' }}">
                                            <td class="px-3 py-3 text-sm text-gray-400 font-medium">{{ $index + 1 }}</td>
                                            <td class="px-3 py-3">
                                                <div class="text-sm font-semibold text-gray-800">{{ $row['employee']->full_name }}
                                                </div>
                                                <div class="text-xs text-gray-400">{{ $row['employee']->unit->name ?? '-' }}</div>
                                            </td>
                                            <td class="px-3 py-3 text-sm text-gray-500">{{ $row['employee']->job_position }}</td>
                                            {{-- Pretest Benar --}}
                                            <td class="px-2 py-3 text-center">
                                                @if($row['pre_correct'] !== null)
                                                    <span
                                                        class="text-sm font-medium text-gray-700">{{ $row['pre_correct'] }}/{{ $row['pre_total'] }}</span>
                                                @else
                                                    <span class="text-xs text-gray-300">&mdash;</span>
                                                @endif
                                            </td>
                                            {{-- Pretest Nilai --}}
                                            <td class="px-2 py-3 text-center">
                                                @if($row['pre_score'] !== null)
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded-md text-sm font-medium text-gray-700">{{ $row['pre_score'] }}</span>
                                                @else
                                                    <span class="text-xs text-gray-300">&mdash;</span>
                                                @endif
                                            </td>
                                            {{-- Posttest Benar --}}
                                            <td class="px-2 py-3 text-center">
                                                @if($row['post_correct'] !== null)
                                                    <span
                                                        class="text-sm font-medium text-gray-700">{{ $row['post_correct'] }}/{{ $row['post_total'] }}</span>
                                                @else
                                                    <span class="text-xs text-gray-300">&mdash;</span>
                                                @endif
                                            </td>
                                            {{-- Posttest Nilai --}}
                                            <td class="px-2 py-3 text-center">
                                                @if($row['post_score'] !== null)
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded-md text-sm font-medium text-gray-700">{{ $row['post_score'] }}</span>
                                                @else
                                                    <span class="text-xs text-gray-300">&mdash;</span>
                                                @endif
                                            </td>
                                            {{-- Perubahan --}}
                                            <td class="px-3 py-3 text-center">
                                                @if($row['improvement'] !== null)
                                                    @if($row['improvement'] > 0)
                                                        <span
                                                            class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-600">
                                                            {{ $row['improvement'] }}
                                                        </span>
                                                    @elseif($row['improvement'] < 0)
                                                        <span
                                                            class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-md text-xs font-bold bg-red-50 text-red-500">
                                                            {{ $row['improvement'] }}
                                                        </span>
                                                    @else
                                                        <span
                                                            class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-gray-50 text-gray-400">0</span>
                                                    @endif
                                                @else
                                                    <span class="text-xs text-gray-300">&mdash;</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <p class="text-sm text-gray-400">Belum ada peserta yang mengerjakan soal</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif


        {{-- ===== EMPLOYEE LIST SECTION ===== --}}
        <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Daftar Kehadiran</h3>
                    <p class="text-sm text-gray-400 mt-0.5">{{ $agenda->employees->count() }} pegawai hadir</p>
                </div>
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary-50 text-xs font-bold text-primary">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ $agenda->employees->count() }} Hadir
                </span>
            </div>

            @if($agenda->employees->count() > 0)
                <div class="px-8 py-6 overflow-x-auto">
                    <div class="rounded-2xl border border-gray-100 shadow-sm" style="max-height: 400px; overflow-y: auto;">
                        <table class="min-w-full">
                            <thead class="bg-primary-700 text-white" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider w-12">No</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">NIP</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Jabatan</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Organisasi</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3.5 text-center text-xs font-semibold uppercase tracking-wider">TTD</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                            @foreach($agenda->employees as $index => $employee)
                                <tr class="transition-colors hover:bg-primary-50/40 {{ $index % 2 === 0 ? 'bg-white' : 'bg-slate-50' }}">
                                    <td class="px-6 py-3.5 text-sm text-gray-400 font-medium">{{ $index + 1 }}</td>
                                    <td class="px-6 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-7 h-7 rounded-lg {{ $employee->pivot->signature_image_path ? 'bg-primary-50' : 'bg-gray-100' }} flex items-center justify-center text-[10px] font-bold {{ $employee->pivot->signature_image_path ? 'text-primary' : 'text-gray-400' }}">
                                                {{ strtoupper(substr($employee->full_name, 0, 1)) }}</div>
                                            <span class="text-sm font-semibold text-gray-800">{{ $employee->full_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5 text-sm text-gray-500 font-mono">{{ $employee->nip }}</td>
                                    <td class="px-6 py-3.5 text-sm text-gray-500">{{ $employee->job_position }}</td>
                                    <td class="px-6 py-3.5 text-sm text-gray-500">{{ $employee->unit->name ?? '-' }}</td>
                                    <td class="px-6 py-3.5">
                                        @if($employee->pivot->signature_image_path)
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-primary-50 text-primary">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M4.5 12.75l6 6 9-13.5" />
                                                </svg>
                                                Hadir
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-400">Belum
                                                Absen</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5 text-center">
                                        @if($employee->pivot->signature_image_path)
                                            <button
                                                @click="signatureName = '{{ addslashes($employee->full_name) }}'; signatureUrl = '{{ Storage::url($employee->pivot->signature_image_path) }}'; showSignatureModal = true"
                                                class="inline-block overflow-hidden hover:border-primary-300 transition-colors cursor-pointer group"
                                                title="Lihat tanda tangan {{ $employee->full_name }}">
                                                <img src="{{ Storage::url($employee->pivot->signature_image_path) }}"
                                                    alt="TTD {{ $employee->full_name }}"
                                                    class="w-16 h-10 object-contain bg-white group-hover:scale-105 transition-transform">
                                            </button>
                                        @else
                                            <span class="text-xs text-gray-300">&mdash;</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            @else

                <div class="text-center py-12">
                    <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-400">Belum ada peserta yang hadir</p>
                </div>
            @endif

        </div>

        {{-- ===== MEETING MINUTES (NOTES) SECTION ===== --}}
        @if($agenda->allowsNotes())
            <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Notulensi Rapat</h3>
                        <p class="text-sm text-gray-400 mt-0.5">{{ $agenda->notes->count() }} catatan pembahasan</p>
                    </div>
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary-50 text-xs font-bold text-primary">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        {{ $agenda->notes->count() }} Catatan
                    </span>
                </div>
                @if($agenda->notes->count() > 0)
                    <div class="px-8 py-6 overflow-x-auto">
                        <div class="overflow-hidden rounded-2xl border border-gray-100 shadow-sm">
                            <table class="min-w-full">
                                <thead class="bg-primary-700 text-white">
                                    <tr>
                                    <th
                                        class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider w-12">
                                        No</th>
                                    <th
                                        class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">
                                        Topik</th>
                                    <th
                                        class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">
                                        Pembahasan</th>
                                    <th
                                        class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">
                                        Kesimpulan</th>
                                    <th
                                        class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">
                                        PJ</th>
                                    <th
                                        class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">
                                        Tanggal</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                @foreach($agenda->notes as $index => $note)
                                    <tr class="transition-colors hover:bg-primary-50/40 {{ $index % 2 === 0 ? 'bg-white' : 'bg-slate-50' }}">
                                        <td class="px-6 py-3.5 text-sm text-gray-400 font-medium">{{ $index + 1 }}</td>
                                        <td class="px-6 py-3.5 text-sm font-semibold text-gray-800">{{ $note->topic }}</td>
                                        <td class="px-6 py-3.5 text-sm text-gray-500">{{ $note->decision }}</td>
                                        <td class="px-6 py-3.5 text-sm text-gray-500">{{ $note->remarks ?? '—' }}</td>
                                        <td class="px-6 py-3.5 text-sm text-gray-500">{{ $note->pj ?? '—' }}</td>
                                        <td class="px-6 py-3.5 text-sm text-gray-500 whitespace-nowrap">
                                            {{ $note->created_at->format('H:i') }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <p class="text-sm text-gray-400">Belum ada catatan notulensi</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- ===== DOCUMENTATION IMAGES SECTION ===== --}}
        <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Dokumentasi Foto</h3>
                <p class="text-sm text-gray-400 mt-0.5">{{ $agenda->images->count() }} foto dokumentasi</p>
            </div>
            <div class="px-8 py-6">
                @if($agenda->images->count() > 0)
                    <div class="columns-2 md:columns-3 gap-4 space-y-4">
                        @foreach($agenda->images as $imgIndex => $image)
                            <div class="break-inside-avoid">
                                <button
                                    @click="imageUrl = '{{ Storage::url($image->image_path) }}'; currentImageIndex = {{ $imgIndex }}; showImageModal = true"
                                    class="relative rounded-2xl overflow-hidden group cursor-pointer w-full block">
                                    <img src="{{ Storage::url($image->image_path) }}" alt="Dokumentasi {{ $imgIndex + 1 }}"
                                        class="w-full rounded-2xl object-cover transition-transform duration-300 group-hover:scale-105"
                                        loading="lazy">
                                    <div
                                        class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-300 rounded-2xl flex items-center justify-center">
                                        <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                            fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6" />
                                        </svg>
                                    </div>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M2.25 18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V6a2.25 2.25 0 00-2.25-2.25h-15A2.25 2.25 0 002.25 6v12z" />
                            </svg>
                        </div>
                        <p class="text-sm text-gray-400">Belum ada foto dokumentasi</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ===== SIGNATURE LIGHTBOX MODAL ===== --}}
        <template x-teleport="body">
            <div x-show="showSignatureModal" x-cloak x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[100] flex items-center justify-center p-4"
                @click.self="showSignatureModal = false" @keydown.escape.window="showSignatureModal = false">
                <div x-show="showSignatureModal" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                    class="bg-white rounded-3xl w-full max-w-md shadow-2xl overflow-hidden" @click.stop>
                    <div class="px-6 pt-6 pb-4 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Tanda Tangan</h3>
                            <p class="text-sm text-gray-400 mt-0.5" x-text="signatureName"></p>
                        </div>
                        <button @click="showSignatureModal = false"
                            class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="border-2 border-dashed border-gray-200 rounded-2xl p-4 bg-gray-50/50">
                            <img :src="signatureUrl" :alt="'Tanda tangan ' + signatureName"
                                class="w-full h-48 object-contain">
                        </div>
                    </div>
                    <div class="px-6 pb-6">
                        <button @click="showSignatureModal = false"
                            class="w-full px-4 py-2.5 rounded-2xl bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200 active:scale-[0.98] transition-all duration-200">Tutup</button>
                    </div>
                </div>
            </div>
        </template>

        {{-- ===== IMAGE LIGHTBOX MODAL ===== --}}
        <template x-teleport="body">
            <div x-show="showImageModal" x-cloak x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[100] flex items-center justify-center p-4"
                @click.self="showImageModal = false" @keydown.escape.window="showImageModal = false">
                <button @click="showImageModal = false"
                    class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <img :src="imageUrl" alt="Dokumentasi"
                    class="max-w-full max-h-[85vh] rounded-2xl object-contain shadow-2xl">
            </div>
        </template>
    </div>
</x-app-layout>
