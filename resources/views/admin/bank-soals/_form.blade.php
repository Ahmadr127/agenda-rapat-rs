{{-- Partial form bank soal: input manual + import Excel. Dipakai create & edit.
    Layout: kiri (judul/deskripsi + import, sticky) | kanan (daftar soal).
    Hasil import masuk ke array yang sama dengan form manual.
    Variabel yang wajib dikirim:
    $action, $method ('POST'|'PUT'), $submitLabel,
    $titleValue, $descriptionValue, $initialQuestions (array),
    $infoSubtitle, $isCreate (bool) --}}
<div class="w-full" x-data="{
    questions: {{ json_encode($initialQuestions ?? []) }},
    importPreview: [],
    importErrors: [],
    importFileName: '',
    importLoading: false,

    blankQuestion() {
        return { question_text: '', option_a: '', option_b: '', option_c: '', option_d: '', option_e: '', correct_option: 'a' };
    },

    addQuestion() {
        this.questions.push(this.blankQuestion());
        this.$nextTick(() => {
            const cards = this.$el.querySelectorAll('[data-question-card]');
            const last = cards[cards.length - 1];
            if (last) last.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    },

    removeQuestion(i) {
        if (!confirm('Hapus soal nomor ' + (i + 1) + '?')) return;
        this.questions.splice(i, 1);
    },

    clearQuestions() {
        if (this.questions.length === 0) return;
        if (!confirm('Hapus semua ' + this.questions.length + ' soal?')) return;
        this.questions = [];
        this.importFileName = '';
    },

    handleFileUpload(event) {
        const file = event.target.files[0];
        if (!file) return;
        this.importFileName = file.name;
        this.importPreview = [];
        this.importErrors = [];
        this.importLoading = true;

        const reader = new FileReader();
        reader.onload = async (e) => {
            try {
                const XLSX = await window.loadXLSX();
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const sheet = workbook.Sheets[workbook.SheetNames[0]];

                const allRows = XLSX.utils.sheet_to_json(sheet, { header: 1, defval: '' });
                if (allRows.length < 2) {
                    this.importErrors = ['File tidak memiliki data. Pastikan baris pertama adalah header dan ada minimal satu baris data.'];
                    return;
                }

                const headers = allRows[0].map(h => String(h).toLowerCase().trim());
                const requiredGroups = [
                    { label: 'question_text / pertanyaan / soal', aliases: ['question_text', 'pertanyaan', 'soal'] },
                    { label: 'option_a / opsi_a', aliases: ['option_a', 'opsi_a', 'a'] },
                    { label: 'option_b / opsi_b', aliases: ['option_b', 'opsi_b', 'b'] },
                    { label: 'option_c / opsi_c', aliases: ['option_c', 'opsi_c', 'c'] },
                    { label: 'correct_option / jawaban / kunci', aliases: ['correct_option', 'jawaban', 'kunci'] },
                ];
                const missing = requiredGroups.filter(g => !g.aliases.some(a => headers.includes(a)));
                if (missing.length > 0) {
                    this.importErrors = [
                        'Kolom wajib tidak ditemukan: ' + missing.map(g => g.label).join(', ') + '.',
                        'Pastikan baris pertama file berisi nama kolom yang sesuai.',
                    ];
                    return;
                }

                const rows = XLSX.utils.sheet_to_json(sheet, { defval: '' });
                const parsed = [];
                rows.forEach((row, i) => {
                    const norm = {};
                    Object.keys(row).forEach(k => { norm[k.toLowerCase().trim()] = String(row[k]).trim(); });

                    const q = {
                        question_text: norm['question_text'] || norm['pertanyaan'] || norm['soal'] || '',
                        option_a: norm['option_a'] || norm['opsi_a'] || norm['a'] || '',
                        option_b: norm['option_b'] || norm['opsi_b'] || norm['b'] || '',
                        option_c: norm['option_c'] || norm['opsi_c'] || norm['c'] || '',
                        option_d: norm['option_d'] || norm['opsi_d'] || norm['d'] || '',
                        option_e: norm['option_e'] || norm['opsi_e'] || norm['e'] || '',
                        correct_option: (norm['correct_option'] || norm['jawaban'] || norm['kunci'] || '').toLowerCase(),
                    };

                    const validOpts = ['a', 'b', 'c'];
                    if (q.option_d) validOpts.push('d');
                    if (q.option_e) validOpts.push('e');

                    const rowErrors = [];
                    if (!q.question_text) rowErrors.push('Pertanyaan kosong');
                    if (!q.option_a) rowErrors.push('Opsi A kosong');
                    if (!q.option_b) rowErrors.push('Opsi B kosong');
                    if (!q.option_c) rowErrors.push('Opsi C kosong');
                    if (!validOpts.includes(q.correct_option)) rowErrors.push('Jawaban harus ' + validOpts.join('/'));

                    parsed.push({ ...q, _errors: rowErrors, _no: i + 1 });
                });

                this.importPreview = parsed;
            } catch (err) {
                this.importErrors = ['Gagal membaca file. Pastikan format file CSV, XLSX, atau XLS yang valid.'];
            } finally {
                this.importLoading = false;
                event.target.value = '';
            }
        };
        reader.readAsArrayBuffer(file);
    },

    applyImport() {
        const valid = this.importPreview.filter(q => q._errors.length === 0);
        if (valid.length === 0) return;
        @if(!$isCreate)
        if (this.questions.length > 0 && !confirm('Soal yang sudah ada akan diganti dengan soal dari file. Lanjutkan?')) return;
        @endif
        this.questions = valid.map(({ question_text, option_a, option_b, option_c, option_d, option_e, correct_option }) =>
            ({ question_text, option_a, option_b, option_c, option_d, option_e, correct_option })
        );
        this.importPreview = [];
        this.importFileName = '';
        this.$nextTick(() => {
            const list = document.getElementById('daftar-soal');
            if (list) list.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    },

    get validCount() { return this.importPreview.filter(q => q._errors.length === 0).length; },
    get errorCount() { return this.importPreview.filter(q => q._errors.length > 0).length; },
}">
    <form action="{{ $action }}" method="POST" class="space-y-6">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        {{-- Hidden inputs untuk submit soal --}}
        <template x-for="(q, i) in questions" :key="i">
            <div>
                <input type="hidden" :name="'questions[' + i + '][question_text]'" :value="q.question_text">
                <input type="hidden" :name="'questions[' + i + '][option_a]'" :value="q.option_a">
                <input type="hidden" :name="'questions[' + i + '][option_b]'" :value="q.option_b">
                <input type="hidden" :name="'questions[' + i + '][option_c]'" :value="q.option_c">
                <input type="hidden" :name="'questions[' + i + '][option_d]'" :value="q.option_d">
                <input type="hidden" :name="'questions[' + i + '][option_e]'" :value="q.option_e">
                <input type="hidden" :name="'questions[' + i + '][correct_option]'" :value="q.correct_option">
            </div>
        </template>

        <div class="grid gap-6 lg:grid-cols-5 items-start">
            {{-- Kolom kiri: judul/deskripsi + import (sticky) --}}
            <div class="lg:col-span-2 space-y-6 lg:sticky lg:top-6 self-start">
                {{-- Info Bank Soal --}}
                <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h3 class="text-base font-bold text-gray-900">Informasi Bank Soal</h3>
                        <p class="text-sm text-gray-400 mt-0.5">{{ $infoSubtitle }}</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Judul</label>
                            <input type="text" name="title" id="title" value="{{ $titleValue }}" placeholder="Masukkan judul bank soal" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" required>
                            @error('title') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi <span class="text-gray-400 font-normal">(opsional)</span></label>
                            <textarea name="description" id="description" rows="4" placeholder="Masukkan deskripsi bank soal" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none resize-none">{{ $descriptionValue }}</textarea>
                            @error('description') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                        </div>
                        <div class="rounded-2xl bg-primary/5 border border-primary/15 px-4 py-3 flex items-center gap-3">
                            <span class="w-9 h-9 rounded-xl bg-primary text-white flex items-center justify-center text-sm font-extrabold flex-shrink-0" x-text="questions.length"></span>
                            <p class="text-xs text-gray-500 font-medium">soal terdaftar — dari <span class="font-bold text-gray-700">input manual</span>, <span class="font-bold text-gray-700">import Excel</span>, atau gabungan keduanya</p>
                        </div>
                    </div>
                </div>

                {{-- Import Excel --}}
                <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h3 class="text-base font-bold text-gray-900">Import Excel</h3>
                        <p class="text-xs text-gray-400 mt-0.5">CSV, XLSX, atau XLS &middot; wajib: <span class="font-mono text-[11px] text-gray-500">question_text, option_a, option_b, option_c, correct_option</span></p>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <label class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200 transition-colors cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                                Pilih File
                                <input type="file" accept=".csv,.xlsx,.xls" class="hidden" @change="handleFileUpload($event)">
                            </label>
                            <a href="{{ route('admin.bank-soals.template') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-50 text-emerald-700 text-sm font-semibold border border-emerald-200 hover:bg-emerald-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M12 3v12m0 0l-4.5-4.5M12 15l4.5-4.5"/></svg>
                                Template
                            </a>
                            <svg x-show="importLoading" class="w-4 h-4 animate-spin text-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        </div>
                        <p class="text-xs text-gray-500 truncate" x-text="importFileName || 'Belum ada file dipilih'"></p>
                        <p class="text-xs text-gray-400">Hasil import yang dikonfirmasi langsung mengisi form soal di kanan dan tetap bisa diubah manual.</p>

                        <template x-if="importErrors.length > 0">
                            <div class="rounded-xl bg-rose-50 border border-rose-200 px-4 py-3">
                                <template x-for="err in importErrors" :key="err">
                                    <p class="text-rose-600 text-sm" x-text="err"></p>
                                </template>
                            </div>
                        </template>

                        {{-- Preview sebelum dikonfirmasi --}}
                        <template x-if="importPreview.length > 0">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <p class="text-sm text-gray-600">
                                        <span class="font-semibold text-green-600" x-text="validCount"></span> valid<template x-if="errorCount > 0"><span>, <span class="font-semibold text-rose-600" x-text="errorCount"></span> bermasalah</span></template>
                                    </p>
                                    <button type="button" @click="applyImport()" :disabled="validCount === 0"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Masukkan ke Form
                                    </button>
                                </div>
                                <div class="overflow-x-auto rounded-2xl border border-gray-200 max-h-72 overflow-y-auto">
                                    <table class="w-full text-xs">
                                        <thead class="bg-gray-50 text-gray-500 font-semibold sticky top-0">
                                            <tr>
                                                <th class="px-3 py-2.5 text-left w-8">#</th>
                                                <th class="px-3 py-2.5 text-left min-w-[160px]">Pertanyaan</th>
                                                <th class="px-3 py-2.5 text-left">A</th>
                                                <th class="px-3 py-2.5 text-left">B</th>
                                                <th class="px-3 py-2.5 text-left">C</th>
                                                <th class="px-3 py-2.5 text-center w-12">Jwb</th>
                                                <th class="px-3 py-2.5 text-left">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <template x-for="row in importPreview" :key="row._no">
                                                <tr :class="row._errors.length > 0 ? 'bg-rose-50' : 'bg-white hover:bg-gray-50'">
                                                    <td class="px-3 py-2 text-gray-400" x-text="row._no"></td>
                                                    <td class="px-3 py-2 text-gray-700 max-w-[180px] truncate" x-text="row.question_text || '—'"></td>
                                                    <td class="px-3 py-2 text-gray-600 max-w-[90px] truncate" x-text="row.option_a || '—'"></td>
                                                    <td class="px-3 py-2 text-gray-600 max-w-[90px] truncate" x-text="row.option_b || '—'"></td>
                                                    <td class="px-3 py-2 text-gray-600 max-w-[90px] truncate" x-text="row.option_c || '—'"></td>
                                                    <td class="px-3 py-2 text-center">
                                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full font-bold uppercase text-xs"
                                                            :class="['a','b','c','d','e'].includes(row.correct_option) ? 'bg-green-100 text-green-700' : 'bg-rose-100 text-rose-600'"
                                                            x-text="row.correct_option || '?'"></span>
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <template x-if="row._errors.length === 0">
                                                            <span class="text-green-600 font-medium">Valid</span>
                                                        </template>
                                                        <template x-if="row._errors.length > 0">
                                                            <span class="text-rose-600" x-text="row._errors.join(', ')"></span>
                                                        </template>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Kolom kanan: daftar soal + action bar --}}
            <div class="lg:col-span-3 space-y-6">
            <div id="daftar-soal" class="bg-white rounded-3xl border border-gray-100 overflow-hidden scroll-mt-6">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Daftar Soal</h3>
                        <p class="text-sm text-gray-400 mt-0.5"><span class="font-bold text-gray-700" x-text="questions.length"></span> soal &middot; lingkaran hijau = kunci jawaban</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button type="button" @click="clearQuestions()" x-show="questions.length > 0" class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-500 text-sm font-semibold hover:bg-rose-50 hover:text-rose-600 transition-colors">Hapus semua</button>
                        <button type="button" @click="addQuestion()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary-700 active:scale-[0.98] transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Tambah Soal
                        </button>
                    </div>
                </div>
                <div class="p-6 space-y-5">
                    @error('questions') <p class="text-rose-500 text-xs font-medium">{{ $message }}</p> @enderror

                    <template x-if="questions.length === 0">
                        <div class="text-center py-10 rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50/50">
                            <p class="text-sm font-semibold text-gray-500">Belum ada soal</p>
                            <p class="text-xs text-gray-400 mt-1">Klik &ldquo;Tambah Soal&rdquo; untuk input manual, atau import file Excel dari panel kiri.</p>
                        </div>
                    </template>

                    <template x-for="(q, i) in questions" :key="i">
                        <div data-question-card class="rounded-2xl border border-gray-200 overflow-hidden bg-white">
                            <div class="flex items-center justify-between gap-3 px-5 py-3 bg-gray-50 border-b border-gray-200">
                                <p class="text-sm font-bold text-gray-800">Soal <span x-text="i + 1"></span></p>
                                <button type="button" @click="removeQuestion(i)" class="inline-flex items-center gap-1 text-xs font-semibold text-rose-500 hover:text-rose-600 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    Hapus
                                </button>
                            </div>
                            <div class="p-5 space-y-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Pertanyaan</label>
                                    <textarea x-model="q.question_text" rows="2" placeholder="Tulis pertanyaan..." required class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none resize-none"></textarea>
                                </div>
                                <template x-for="opt in ['a','b','c','d','e']" :key="opt">
                                    <div class="flex items-center gap-2.5">
                                        <input type="radio" :name="'kunci_' + i" :value="opt" x-model="q.correct_option" :title="'Jadikan opsi ' + opt.toUpperCase() + ' sebagai kunci jawaban'" class="w-4 h-4 flex-shrink-0 text-emerald-600 border-gray-300 focus:ring-emerald-500 cursor-pointer">
                                        <span class="w-7 h-7 flex-shrink-0 rounded-lg flex items-center justify-center text-xs font-extrabold uppercase transition-colors" :class="q.correct_option === opt ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-400'" x-text="opt"></span>
                                        <input type="text" x-model="q['option_' + opt]" :required="['a','b','c'].includes(opt)" :placeholder="'Opsi ' + opt.toUpperCase() + (['d','e'].includes(opt) ? ' (opsional)' : '')" class="block w-full px-4 py-2.5 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none">
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div class="flex items-center gap-2.5 flex-wrap pt-4 mt-1 border-t border-gray-100">
                        <p class="text-sm text-gray-500 mr-auto"><span class="font-bold text-gray-800" x-text="questions.length"></span> soal siap disimpan</p>
                        <button type="button" @click="addQuestion()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200 active:scale-[0.98] transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Tambah Soal
                        </button>
                        <a href="{{ route('admin.bank-soals.index') }}" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-600 text-sm font-semibold hover:bg-gray-200 transition-colors">Batal</a>
                        <button type="submit" :disabled="questions.length === 0" class="px-6 py-2.5 rounded-xl bg-primary text-white text-sm font-bold shadow-md shadow-primary/20 hover:bg-primary-700 hover:shadow-lg active:scale-[0.98] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">{{ $submitLabel }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
