<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $agenda->allowsNotes() ? 'Input Notulensi' : 'Dokumentasi Foto' }} - {{ $agenda->title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-tab.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>

<body class="bg-slate-100 min-h-screen font-sans text-slate-800">
    <div class="pb-10">
        {{-- Header --}}
        <x-agenda-header :agenda="$agenda">
            <x-slot:actions>
                <a href="{{ route('attendance.show', $agenda) }}"
                    class="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur-sm text-white text-[10px] md:text-[11px] font-semibold px-3.5 py-1.5 rounded-full hover:bg-white/25 active:scale-95 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Absensi
                </a>
            </x-slot:actions>
        </x-agenda-header>

        <div class="px-4 md:px-8 mt-6 space-y-5">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition class="flex items-center gap-3 px-4 py-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 shadow-sm">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs md:text-sm font-bold">Berhasil</p>
                        <p class="text-[11px] md:text-xs font-medium text-emerald-700">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <div class="grid gap-5 xl:grid-cols-2 items-start">
            {{-- Form tambah notulensi --}}
            <section class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="px-5 md:px-6 pt-5 pb-4 border-b border-slate-100 flex items-center gap-3.5">
                    <span class="w-10 h-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-sm md:text-base font-extrabold text-slate-900">
                            Tambah Notulensi
                            <span class="ml-1.5 inline-flex items-center justify-center min-w-6 h-6 px-1.5 rounded-lg bg-primary/10 text-primary text-[11px] font-extrabold align-middle">#{{ $agenda->notes->count() + 1 }}</span>
                        </h2>
                        <p class="text-[11px] md:text-xs text-slate-400 font-medium mt-0.5">Catat topik, pembahasan, kesimpulan, dan penanggung jawab</p>
                    </div>
                </div>

                <form action="{{ route('agenda.note.store', $agenda) }}" method="POST" class="p-5 md:p-6 space-y-4">
                    @csrf
                    <div>
                        <label for="topic" class="block text-xs md:text-sm font-bold text-slate-700 mb-1.5">Topik <span class="text-rose-500">*</span></label>
                        <input type="text" name="topic" id="topic" value="{{ old('topic') }}" placeholder="cth: Evaluasi pelayanan triwulan I" required
                            class="block w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/60 text-xs md:text-sm font-medium text-slate-800 placeholder-slate-300 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition">
                        @error('topic') <p class="text-rose-500 text-[11px] md:text-xs font-semibold mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="decision" class="block text-xs md:text-sm font-bold text-slate-700 mb-1.5">Pembahasan <span class="text-rose-500">*</span></label>
                        <textarea name="decision" id="decision" rows="3" placeholder="Tuliskan jalannya pembahasan..." required
                            class="block w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/60 text-xs md:text-sm font-medium text-slate-800 placeholder-slate-300 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition resize-y">{{ old('decision') }}</textarea>
                        @error('decision') <p class="text-rose-500 text-[11px] md:text-xs font-semibold mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label for="remarks" class="block text-xs md:text-sm font-bold text-slate-700 mb-1.5">Kesimpulan</label>
                            <textarea name="remarks" id="remarks" rows="2" placeholder="Kesimpulan / tindak lanjut..."
                                class="block w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/60 text-xs md:text-sm font-medium text-slate-800 placeholder-slate-300 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition resize-y">{{ old('remarks') }}</textarea>
                            @error('remarks') <p class="text-rose-500 text-[11px] md:text-xs font-semibold mt-1.5">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="pj" class="block text-xs md:text-sm font-bold text-slate-700 mb-1.5">Penanggung Jawab (PJ) <span class="text-rose-500">*</span></label>
                            <input type="text" name="pj" id="pj" value="{{ old('pj') }}" placeholder="Nama penanggung jawab" required
                                class="block w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/60 text-xs md:text-sm font-medium text-slate-800 placeholder-slate-300 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition">
                            @error('pj') <p class="text-rose-500 text-[11px] md:text-xs font-semibold mt-1.5">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-2xl bg-primary text-white text-xs md:text-sm font-extrabold shadow-lg shadow-primary/25 hover:bg-primary-700 active:scale-[0.99] transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Simpan Notulensi
                    </button>
                </form>
            </section>

            {{-- Catatan sebelumnya --}}
            @if($agenda->notes->count() > 0)
                <section class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <div class="px-5 md:px-6 pt-5 pb-4 border-b border-slate-100 flex items-center gap-3.5">
                        <span class="w-10 h-10 rounded-2xl bg-violet-50 text-violet-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        </span>
                        <div>
                            <h2 class="text-sm md:text-base font-extrabold text-slate-900">
                                Catatan Sebelumnya
                                <span class="ml-1.5 inline-flex items-center px-2 py-0.5 rounded-lg bg-slate-100 text-slate-500 text-[10px] md:text-[11px] font-extrabold align-middle">{{ $agenda->notes->count() }}</span>
                            </h2>
                            <p class="text-[11px] md:text-xs text-slate-400 font-medium mt-0.5">Terbaru ditampilkan paling atas</p>
                        </div>
                    </div>

                    <ol class="divide-y divide-slate-100">
                        @foreach($agenda->notes->sortByDesc('created_at') as $index => $note)
                            <li class="p-5 md:p-6 flex gap-3.5">
                                <span class="flex-shrink-0 w-8 h-8 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center text-xs font-extrabold">{{ $index + 1 }}</span>
                                <div class="min-w-0 flex-1 space-y-2.5">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 class="text-xs md:text-sm font-extrabold text-slate-900 leading-snug break-words">{{ $note->topic }}</h3>
                                        <span class="flex-shrink-0 inline-flex items-center px-2 py-0.5 rounded-lg bg-slate-100 text-slate-400 text-[10px] font-bold">{{ $note->created_at->format('H:i') }}</span>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 border border-slate-100 px-3.5 py-3">
                                        <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Pembahasan</p>
                                        <p class="text-[11px] md:text-xs text-slate-600 font-medium mt-1 whitespace-pre-line break-words">{{ $note->decision }}</p>
                                    </div>
                                    <div class="grid sm:grid-cols-2 gap-2.5">
                                        <div class="rounded-2xl bg-emerald-50/60 border border-emerald-100 px-3.5 py-3">
                                            <p class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-500">Kesimpulan</p>
                                            <p class="text-[11px] md:text-xs text-slate-600 font-medium mt-1 whitespace-pre-line break-words">{{ $note->remarks ?? '—' }}</p>
                                        </div>
                                        <div class="rounded-2xl bg-blue-50/60 border border-blue-100 px-3.5 py-3">
                                            <p class="text-[10px] font-extrabold uppercase tracking-wider text-blue-500">Penanggung Jawab</p>
                                            <p class="text-[11px] md:text-xs text-slate-700 font-bold mt-1 break-words">{{ $note->pj ?? '—' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @else
                <div class="border-2 border-dashed border-slate-200 rounded-3xl p-6 md:p-8 text-center bg-white/60">
                    <span class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-300 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    </span>
                    <p class="text-slate-500 font-bold text-xs md:text-sm">Belum ada catatan</p>
                    <p class="text-[11px] md:text-xs text-slate-400 font-medium mt-1">Jadilah yang pertama menambahkan notulensi rapat ini</p>
                </div>
            @endif
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center mt-8 px-4">
            <p class="text-[10px] md:text-xs text-slate-300 font-medium">D-ASSA &middot; Digital Agenda &amp; Attendance System</p>
        </div>
    </div>
</body>

</html>
