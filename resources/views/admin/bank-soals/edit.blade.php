<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.bank-soals.index') }}" class="text-gray-400 hover:text-primary transition-colors">Bank Soal</a>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            <span class="font-semibold text-gray-700">Ubah Bank Soal</span>
        </div>
    </x-slot>

    @php
        $existingQuestions = old('questions', $bankSoal->questions->map(fn($q) => [
            'question_text' => $q->question_text,
            'option_a' => $q->option_a,
            'option_b' => $q->option_b,
            'option_c' => $q->option_c,
            'option_d' => $q->option_d,
            'option_e' => $q->option_e,
            'correct_option' => $q->correct_option,
        ])->toArray());
    @endphp

    <div class="max-w-4xl" x-data="{
        questions: {{ json_encode($existingQuestions) }},
        importPreview: [],
        importErrors: [],
        importFileName: '',
        importLoading: false,

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
            if (!confirm('Soal yang sudah ada akan diganti dengan soal dari file. Lanjutkan?')) return;
            this.questions = valid.map(({ question_text, option_a, option_b, option_c, option_d, option_e, correct_option }) =>
                ({ question_text, option_a, option_b, option_c, option_d, option_e, correct_option })
            );
            this.importPreview = [];
            this.importFileName = '';
        },

        get validCount() { return this.importPreview.filter(q => q._errors.length === 0).length; },
        get errorCount() { return this.importPreview.filter(q => q._errors.length > 0).length; },
    }">
        <form action="{{ route('admin.bank-soals.update', $bankSoal) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

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

            {{-- Info Bank Soal --}}
            <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">Informasi Bank Soal</h3>
                    <p class="text-sm text-gray-400 mt-0.5">Ubah judul dan deskripsi bank soal</p>
                </div>
                <div class="p-8 space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Judul</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $bankSoal->title) }}" placeholder="Masukkan judul bank soal" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" required>
                        @error('title') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi <span class="text-gray-400 font-normal">(opsional)</span></label>
                        <textarea name="description" id="description" rows="3" placeholder="Masukkan deskripsi bank soal" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none resize-none">{{ old('description', $bankSoal->description) }}</textarea>
                        @error('description') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Upload & Preview --}}
            <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">Upload Soal</h3>
                    <p class="text-sm text-gray-400 mt-0.5">Upload file CSV, XLSX, atau XLS untuk mengganti soal saat ini. Kolom wajib: <span class="font-mono text-xs text-gray-500">question_text, option_a, option_b, option_c, correct_option</span> — opsional: <span class="font-mono text-xs text-gray-500">option_d, option_e</span></p>
                </div>
                <div class="p-8 space-y-5">

                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200 transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                            Pilih File
                            <input type="file" accept=".csv,.xlsx,.xls" class="hidden" @change="handleFileUpload($event)">
                        </label>
                        <span class="text-sm text-gray-500" x-text="importFileName || 'Belum ada file dipilih'"></span>
                        <svg x-show="importLoading" class="w-4 h-4 animate-spin text-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </div>

                    @error('questions') <p class="text-rose-500 text-xs font-medium -mt-1">{{ $message }}</p> @enderror

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
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-gray-600">
                                    <span class="font-semibold text-green-600" x-text="validCount"></span> soal valid
                                    <template x-if="errorCount > 0">
                                        <span>, <span class="font-semibold text-rose-600" x-text="errorCount"></span> soal bermasalah</span>
                                    </template>
                                </p>
                                <button type="button" @click="applyImport()" :disabled="validCount === 0"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Gunakan Soal Ini
                                </button>
                            </div>
                            <div class="overflow-x-auto rounded-2xl border border-gray-200">
                                <table class="w-full text-xs">
                                    <thead class="bg-gray-50 text-gray-500 font-semibold">
                                        <tr>
                                            <th class="px-3 py-2.5 text-left w-8">#</th>
                                            <th class="px-3 py-2.5 text-left min-w-[180px]">Pertanyaan</th>
                                            <th class="px-3 py-2.5 text-left min-w-[80px]">A</th>
                                            <th class="px-3 py-2.5 text-left min-w-[80px]">B</th>
                                            <th class="px-3 py-2.5 text-left min-w-[80px]">C</th>
                                            <th class="px-3 py-2.5 text-left min-w-[80px]">D</th>
                                            <th class="px-3 py-2.5 text-left min-w-[80px]">E</th>
                                            <th class="px-3 py-2.5 text-center w-14">Jwb</th>
                                            <th class="px-3 py-2.5 text-left">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <template x-for="row in importPreview" :key="row._no">
                                            <tr :class="row._errors.length > 0 ? 'bg-rose-50' : 'bg-white hover:bg-gray-50'">
                                                <td class="px-3 py-2 text-gray-400" x-text="row._no"></td>
                                                <td class="px-3 py-2 text-gray-700 max-w-[200px] truncate" x-text="row.question_text || '—'"></td>
                                                <td class="px-3 py-2 text-gray-600 max-w-[100px] truncate" x-text="row.option_a || '—'"></td>
                                                <td class="px-3 py-2 text-gray-600 max-w-[100px] truncate" x-text="row.option_b || '—'"></td>
                                                <td class="px-3 py-2 text-gray-600 max-w-[100px] truncate" x-text="row.option_c || '—'"></td>
                                                <td class="px-3 py-2 text-gray-600 max-w-[100px] truncate" x-text="row.option_d || '—'"></td>
                                                <td class="px-3 py-2 text-gray-600 max-w-[100px] truncate" x-text="row.option_e || '—'"></td>
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

                    {{-- Soal aktif (dari DB atau setelah import) --}}
                    <template x-if="questions.length > 0 && importPreview.length === 0">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-700"><span x-text="questions.length"></span> soal tersimpan</p>
                            </div>
                            <div class="overflow-x-auto rounded-2xl border border-gray-200">
                                <table class="w-full text-xs">
                                    <thead class="bg-gray-50 text-gray-500 font-semibold">
                                        <tr>
                                            <th class="px-3 py-2.5 text-left w-8">#</th>
                                            <th class="px-3 py-2.5 text-left min-w-[180px]">Pertanyaan</th>
                                            <th class="px-3 py-2.5 text-left min-w-[80px]">A</th>
                                            <th class="px-3 py-2.5 text-left min-w-[80px]">B</th>
                                            <th class="px-3 py-2.5 text-left min-w-[80px]">C</th>
                                            <th class="px-3 py-2.5 text-left min-w-[80px]">D</th>
                                            <th class="px-3 py-2.5 text-left min-w-[80px]">E</th>
                                            <th class="px-3 py-2.5 text-center w-14">Jwb</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <template x-for="(q, i) in questions" :key="i">
                                            <tr class="bg-white hover:bg-gray-50">
                                                <td class="px-3 py-2 text-gray-400" x-text="i + 1"></td>
                                                <td class="px-3 py-2 text-gray-700 max-w-[200px] truncate" x-text="q.question_text"></td>
                                                <td class="px-3 py-2 text-gray-600 max-w-[100px] truncate" x-text="q.option_a"></td>
                                                <td class="px-3 py-2 text-gray-600 max-w-[100px] truncate" x-text="q.option_b"></td>
                                                <td class="px-3 py-2 text-gray-600 max-w-[100px] truncate" x-text="q.option_c"></td>
                                                <td class="px-3 py-2 text-gray-600 max-w-[100px] truncate" x-text="q.option_d"></td>
                                                <td class="px-3 py-2 text-gray-600 max-w-[100px] truncate" x-text="q.option_e"></td>
                                                <td class="px-3 py-2 text-center">
                                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 text-green-700 font-bold uppercase text-xs" x-text="q.correct_option"></span>
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

            <div class="flex items-center gap-3">
                <button type="submit" class="px-6 py-3 rounded-2xl bg-primary text-white text-sm font-bold shadow-md shadow-primary/20 hover:bg-primary-700 hover:shadow-lg active:scale-[0.98] transition-all duration-200">Perbarui</button>
                <a href="{{ route('admin.bank-soals.index') }}" class="px-5 py-3 rounded-2xl bg-gray-100 text-gray-600 text-sm font-semibold hover:bg-gray-200 transition-colors">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
