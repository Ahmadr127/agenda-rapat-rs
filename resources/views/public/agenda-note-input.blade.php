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
            @if($errors->any())
                <div class="flex items-start gap-3 px-4 py-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm">
                    <span class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M12 3l9.2 16a.9.9 0 01-.8 1.3H3.6a.9.9 0 01-.8-1.3L12 3z"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs md:text-sm font-bold">Periksa kembali isian Anda</p>
                        <ul class="mt-1.5 text-[11px] md:text-xs font-semibold text-rose-700 space-y-0.5 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
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

            <div class="grid gap-5 xl:grid-cols-2 items-start" x-data="noteEditor()">
            {{-- Form tambah notulensi --}}
            <section id="note-form-section" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden scroll-mt-5">
                <div class="px-5 md:px-6 pt-5 pb-4 border-b border-slate-100 flex items-center gap-3.5">
                    <span class="w-10 h-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" x-show="!editing"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" x-show="editing" x-cloak style="display:none"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.89 1.13L6 18l.8-2.71a4.5 4.5 0 011.13-1.891L16.862 4.487z"/></svg>
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-sm md:text-base font-extrabold text-slate-900">
                            <span x-text="editing ? 'Edit Notulensi' : 'Tambah Notulensi'"></span>
                            <span class="ml-1.5 inline-flex items-center justify-center min-w-6 h-6 px-1.5 rounded-lg bg-primary/10 text-primary text-[11px] font-extrabold align-middle" x-show="!editing">#{{ $agenda->notes->count() + 1 }}</span>
                        </h2>
                        <p class="text-[11px] md:text-xs text-slate-400 font-medium mt-0.5" x-text="editing ? 'Perbarui catatan yang dipilih' : 'Catat topik, pembahasan, kesimpulan, dan penanggung jawab'"></p>
                    </div>
                </div>

                <form x-ref="form" method="POST" :action="action" class="p-5 md:p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="_method" :value="method">
                    <div>
                        <label for="topic" class="block text-xs md:text-sm font-bold text-slate-700 mb-1.5">Topik <span class="text-rose-500">*</span></label>
                        <input type="text" name="topic" id="topic" x-ref="topic" x-model="form.topic" placeholder="cth: Evaluasi pelayanan triwulan I" required
                            class="block w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/60 text-xs md:text-sm font-medium text-slate-800 placeholder-slate-300 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition">
                        @error('topic') <p class="text-rose-500 text-[11px] md:text-xs font-semibold mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="decision" class="block text-xs md:text-sm font-bold text-slate-700 mb-1.5">Pembahasan <span class="text-rose-500">*</span></label>
                        <textarea name="decision" id="decision" rows="3" x-model="form.decision" placeholder="Tuliskan jalannya pembahasan..." required
                            class="block w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/60 text-xs md:text-sm font-medium text-slate-800 placeholder-slate-300 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition resize-y"></textarea>
                        @error('decision') <p class="text-rose-500 text-[11px] md:text-xs font-semibold mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label for="remarks" class="block text-xs md:text-sm font-bold text-slate-700 mb-1.5">Kesimpulan</label>
                            <textarea name="remarks" id="remarks" rows="2" x-model="form.remarks" placeholder="Kesimpulan / tindak lanjut..."
                                class="block w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/60 text-xs md:text-sm font-medium text-slate-800 placeholder-slate-300 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition resize-y"></textarea>
                            @error('remarks') <p class="text-rose-500 text-[11px] md:text-xs font-semibold mt-1.5">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="pj" class="block text-xs md:text-sm font-bold text-slate-700 mb-1.5">Penanggung Jawab (PJ) <span class="text-rose-500">*</span></label>
                            <input type="text" name="pj" id="pj" x-model="form.pj" placeholder="Nama penanggung jawab" required
                                class="block w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/60 text-xs md:text-sm font-medium text-slate-800 placeholder-slate-300 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition">
                            @error('pj') <p class="text-rose-500 text-[11px] md:text-xs font-semibold mt-1.5">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-2xl bg-primary text-white text-xs md:text-sm font-extrabold shadow-lg shadow-primary/25 hover:bg-primary-700 active:scale-[0.99] transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <span x-text="editing ? 'Simpan Perubahan' : 'Simpan Notulensi'"></span>
                        </button>
                        <button type="button" x-show="editing" x-cloak style="display:none" @click="cancelEdit()"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-2xl border border-slate-200 bg-white text-xs md:text-sm font-bold text-slate-500 hover:text-slate-700 hover:border-slate-300 transition cursor-pointer">
                            Batal Edit
                        </button>
                    </div>
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
                        @foreach($agenda->notes->sortByDesc('created_at')->values() as $index => $note)
                            <li class="p-5 md:p-6 flex gap-3.5">
                                <span class="flex-shrink-0 w-8 h-8 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center text-xs font-extrabold">{{ $index + 1 }}</span>
                                <div class="min-w-0 flex-1 space-y-2.5">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 class="text-xs md:text-sm font-extrabold text-slate-900 leading-snug break-words min-w-0">{{ $note->topic }}</h3>
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-slate-100 text-slate-400 text-[10px] font-bold">{{ $note->created_at->format('H:i') }}</span>
                                            <button type="button" @click="editNote({{ $index }})"
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] md:text-[11px] font-bold text-slate-400 hover:text-primary hover:bg-primary/5 transition cursor-pointer"
                                                title="Edit catatan ini">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                <span class="hidden sm:inline">Edit</span>
                                            </button>
                                        </div>
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

    <script>
        @php
            $noteList = $agenda->notes->sortByDesc('created_at')->values()->map(fn ($n) => ['id' => $n->id, 'topic' => $n->topic, 'decision' => $n->decision, 'remarks' => $n->remarks, 'pj' => $n->pj])->values();
            $createUrl = route('agenda.note.store', $agenda);
            $updateTemplate = route('agenda.note.update', [$agenda, '__ID__']);
            $initialForm = ['topic' => old('topic', ''), 'decision' => old('decision', ''), 'remarks' => old('remarks', ''), 'pj' => old('pj', '')];
        @endphp
        window.NOTE_LIST = @json($noteList);
        window.CREATE_URL = @json($createUrl);
        window.UPDATE_TEMPLATE = @json($updateTemplate);

        function noteEditor() {
            return {
                editing: false,
                noteId: null,
                action: window.CREATE_URL,
                method: 'POST',
                form: @json($initialForm),

                editNote(index) {
                    const note = window.NOTE_LIST[index];
                    if (!note) return;
                    this.editing = true;
                    this.noteId = note.id;
                    this.form = {
                        topic: note.topic ?? '',
                        decision: note.decision ?? '',
                        remarks: note.remarks ?? '',
                        pj: note.pj ?? '',
                    };
                    this.action = window.UPDATE_TEMPLATE.replace('__ID__', note.id);
                    this.method = 'PUT';
                    this.$nextTick(() => {
                        const el = document.getElementById('note-form-section');
                        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        this.$refs.topic?.focus({ preventScroll: true });
                    });
                },

                cancelEdit() {
                    this.editing = false;
                    this.noteId = null;
                    this.action = window.CREATE_URL;
                    this.method = 'POST';
                    this.form = { topic: '', decision: '', remarks: '', pj: '' };
                },
            };
        }
    </script>
</body>

</html>
