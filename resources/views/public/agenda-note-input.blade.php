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
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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

            {{-- Satu tabel notulensi (bentuk sama dengan export laporan agenda) --}}
            <section id="note-form-section" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden scroll-mt-5">
                <div class="px-5 md:px-6 pt-5 pb-4 border-b border-slate-100 flex items-center justify-between gap-3.5">
                    <div class="min-w-0">
                        <h2 class="text-sm md:text-base font-extrabold text-slate-900">Notulensi Rapat</h2>
                        <p class="text-[11px] md:text-xs text-slate-400 font-medium mt-0.5">{{ $agenda->notes->count() }} catatan pembahasan</p>
                    </div>
                    <button type="button" @click="startCreate()"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-primary text-white text-[11px] md:text-xs font-bold shadow-md shadow-primary/20 hover:bg-primary-700 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Tambah Notulensi
                    </button>
                </div>

                @if($agenda->notes->count() > 0)
                    <div class="px-5 md:px-6 py-6 overflow-x-auto">
                        <table class="w-full min-w-[680px] text-left border-collapse">
                            <thead>
                                <tr class="bg-primary-700 text-white">
                                    <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider w-12">No</th>
                                    <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider">Topik</th>
                                    <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider">Pembahasan</th>
                                    <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider">Kesimpulan</th>
                                    <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider">PJ</th>
                                    <th class="px-4 py-3.5 text-left text-[10px] font-semibold uppercase tracking-wider">Waktu</th>
                                    <th class="px-4 py-3.5 text-right text-[10px] font-semibold uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($agenda->notes->sortByDesc('created_at')->values() as $index => $note)
                                    <tr x-if="editingId !== {{ $note->id }}">
                                        <td class="px-4 py-3.5 text-sm text-slate-400 font-medium">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3.5 text-sm font-semibold text-slate-900 break-words min-w-[140px]">{{ $note->topic }}</td>
                                        <td class="px-4 py-3.5 text-sm text-slate-600 break-words min-w-[160px]">{{ $note->decision }}</td>
                                        <td class="px-4 py-3.5 text-sm text-slate-600 break-words min-w-[140px]">{{ $note->remarks ?? '—' }}</td>
                                        <td class="px-4 py-3.5 text-sm text-slate-700 font-semibold break-words min-w-[120px]">{{ $note->pj ?? '—' }}</td>
                                        <td class="px-4 py-3.5 text-sm text-slate-500 whitespace-nowrap">{{ $note->created_at->format('H:i') }}</td>
                                        <td class="px-4 py-3.5 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button type="button" @click="startEdit({{ $note->id }})"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[10px] md:text-[11px] font-bold text-slate-400 hover:text-primary hover:bg-primary/5 transition cursor-pointer"
                                                    title="Edit catatan ini">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    <span>Edit</span>
                                                </button>
                                                <form method="POST" action="{{ route('agenda.note.destroy', [$agenda, $note]) }}" class="inline" onsubmit="return confirm('Hapus notulensi ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[10px] md:text-[11px] font-bold text-slate-400 hover:text-red-500 hover:bg-red-50 transition cursor-pointer"
                                                        title="Hapus catatan ini">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        <span>Hapus</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr x-if="editingId === {{ $note->id }}">
                                        <td class="px-4 py-3.5 text-sm text-slate-400 font-medium">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3.5">
                                            <input type="text" name="topic" value="{{ old('topic', $note->topic) }}" required
                                                class="block w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/60 text-sm font-semibold text-slate-800">
                                            @error('topic') <p class="text-rose-500 text-[11px] md:text-xs font-semibold mt-1.5">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <textarea name="decision" rows="2" required
                                                class="block w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/60 text-sm text-slate-600 resize-y">{{ old('decision', $note->decision) }}</textarea>
                                            @error('decision') <p class="text-rose-500 text-[11px] md:text-xs font-semibold mt-1.5">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <textarea name="remarks" rows="2"
                                                class="block w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/60 text-sm text-slate-600 resize-y">{{ old('remarks', $note->remarks) }}</textarea>
                                            @error('remarks') <p class="text-rose-500 text-[11px] md:text-xs font-semibold mt-1.5">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <input type="text" name="pj" value="{{ old('pj', $note->pj) }}" required
                                                class="block w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/60 text-sm text-slate-700">
                                            @error('pj') <p class="text-rose-500 text-[11px] md:text-xs font-semibold mt-1.5">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="px-4 py-3.5 text-sm text-slate-500 whitespace-nowrap">{{ $note->created_at->format('H:i') }}</td>
                                        <td class="px-4 py-3.5 text-right">
                                            <form method="POST" data-note-id="{{ $note->id }}" action="{{ route('agenda.note.update', [$agenda, $note]) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="flex items-center justify-end gap-2">
                                                    <button type="button" @click="saveEdit({{ $note->id }})"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-primary text-white text-[10px] md:text-[11px] font-bold shadow-md shadow-primary/20 hover:bg-primary-700 transition-all">
                                                        Simpan
                                                    </button>
                                                    <button type="button" @click="cancelEdit()"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-[10px] md:text-[11px] font-bold hover:bg-gray-200 transition-colors">
                                                        Batal
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach

                                <tr x-if="editingId === 'new'">
                                    <td class="px-4 py-3.5 text-sm text-slate-400 font-medium">-</td>
                                    <td class="px-4 py-3.5">
                                        <input type="text" name="topic" x-model="form.topic" required placeholder="cth: Evaluasi pelayanan triwulan I"
                                            class="block w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/60 text-sm font-semibold text-slate-800">
                                        @error('topic') <p class="text-rose-500 text-[11px] md:text-xs font-semibold mt-1.5">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <textarea name="decision" rows="2" x-model="form.decision" required placeholder="Tuliskan jalannya pembahasan..."
                    class="block w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/60 text-sm text-slate-600 resize-y"></textarea>
                @error('decision') <p class="text-rose-500 text-[11px] md:text-xs font-semibold mt-1.5">{{ $message }}</p> @enderror
            </td>
            <td class="px-4 py-3.5">
                <textarea name="remarks" rows="2" x-model="form.remarks" placeholder="Kesimpulan / tindak lanjut..."
                    class="block w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/60 text-sm text-slate-600 resize-y"></textarea>
                @error('remarks') <p class="text-rose-500 text-[11px] md:text-xs font-semibold mt-1.5">{{ $message }}</p> @enderror
            </td>
            <td class="px-4 py-3.5">
                <input type="text" name="pj" x-model="form.pj" required placeholder="Nama penanggung jawab"
                    class="block w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50/60 text-sm text-slate-700">
                @error('pj') <p class="text-rose-500 text-[11px] md:text-xs font-semibold mt-1.5">{{ $message }}</p> @enderror
            </td>
            <td class="px-4 py-3.5 text-sm text-slate-500">-</td>
            <td class="px-4 py-3.5 text-right">
                <form method="POST" action="{{ route('agenda.note.store', $agenda) }}">
                    @csrf
                    <button type="button" @click="saveCreate()"
                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-primary text-white text-[10px] md:text-[11px] font-bold shadow-md shadow-primary/20 hover:bg-primary-700 transition-all">
                        Simpan
                    </button>
                    <button type="button" @click="cancelEdit()"
                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-[10px] md:text-[11px] font-bold hover:bg-gray-200 transition-colors">
                        Batal
                    </button>
                </form>
            </td>
        </tr>
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <span class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-300 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0-.621.504-1.125 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        </span>
                        <p class="text-slate-500 font-bold text-xs md:text-sm">Belum ada catatan</p>
                        <p class="text-[11px] md:text-xs text-slate-400 font-medium mt-1">Jadilah yang pertama menambahkan notulensi rapat ini</p>
                        <button type="button" @click="startCreate()"
                            class="mt-4 inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-primary text-white text-[11px] md:text-xs font-bold shadow-md shadow-primary/20 hover:bg-primary-700 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Tambah Notulensi
                        </button>
                    </div>
                @endif
            </section>
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
                editingId: null,
                action: window.CREATE_URL,
                method: 'POST',
                form: @json($initialForm),

                startEdit(noteId) {
                    this.editingId = noteId;
                    this.$nextTick(() => {
                        const el = document.getElementById('note-form-section');
                        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                },

                startCreate() {
                    this.editingId = 'new';
                    this.action = window.CREATE_URL;
                    this.method = 'POST';
                    this.form = { topic: '', decision: '', remarks: '', pj: '' };
                    this.$nextTick(() => {
                        const el = document.getElementById('note-form-section');
                        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                },

                cancelEdit() {
                    this.editingId = null;
                    this.action = window.CREATE_URL;
                    this.method = 'POST';
                    this.form = { topic: '', decision: '', remarks: '', pj: '' };
                },

                saveCreate() {
                    const data = new FormData();
                    data.append('topic', this.form.topic);
                    data.append('decision', this.form.decision);
                    data.append('remarks', this.form.remarks);
                    data.append('pj', this.form.pj);

                    fetch(window.CREATE_URL, {
                        method: 'POST',
                        body: data,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    }).then(() => window.location.reload());
                },

                saveEdit(noteId) {
                    const form = document.querySelector('form[data-note-id="' + noteId + '"]');
                    if (!form) {
                        return;
                    }

                    const data = new FormData(form);

                    return fetch(form.action, {
                        method: 'POST',
                        body: data,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    }).then(() => window.location.reload());
                },
            };
        }
    </script>
</body>

</html>
