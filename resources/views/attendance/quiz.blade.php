<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Posttest - {{ $agenda->title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-tab.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="bg-slate-100 min-h-screen font-sans text-slate-800">
    <div x-data="quizApp" class="pb-10">
        {{-- Header --}}
        <x-agenda-header :agenda="$agenda">
            <x-slot:actions>
                <a href="{{ route('attendance.show', $agenda) }}"
                    class="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur-sm text-white text-[10px] md:text-[11px] font-semibold px-3.5 py-1.5 rounded-full hover:bg-white/25 active:scale-95 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    Absensi
                </a>
            </x-slot:actions>
        </x-agenda-header>

        <div class="px-4 md:px-8 mt-6 space-y-5">
            {{-- Stepper --}}
            <ol class="flex items-center gap-1.5 md:gap-2 text-[10px] md:text-xs font-bold">
                <template x-for="(s, i) in ['Identifikasi', 'Kerjakan Soal', 'Selesai']" :key="i">
                    <li class="flex items-center gap-1.5 md:gap-2 flex-1 last:flex-none">
                        <span class="flex items-center gap-1.5 md:gap-2">
                            <span class="w-5 h-5 md:w-6 md:h-6 rounded-full flex items-center justify-center text-[10px] md:text-[11px] transition-colors"
                                :class="quizStepIndex > i ? 'bg-emerald-500 text-white' : (quizStepIndex === i ? 'bg-amber-500 text-white shadow-md shadow-amber-500/30' : 'bg-slate-200 text-slate-400')">
                                <svg x-show="quizStepIndex > i" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span x-show="quizStepIndex <= i" x-text="i + 1"></span>
                            </span>
                            <span :class="quizStepIndex >= i ? 'text-slate-700' : 'text-slate-400'" x-text="s"></span>
                        </span>
                        <span x-show="i < 2" class="flex-1 h-0.5 rounded-full mx-1" :class="quizStepIndex > i ? 'bg-emerald-400' : 'bg-slate-200'"></span>
                    </li>
                </template>
            </ol>

            {{-- Posttest Info Banner --}}
            <div x-show="!selectedEmployee">
                <div class="rounded-3xl border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50 p-4 md:p-5 flex items-start gap-3">
                    <span class="w-9 h-9 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 md:w-5 md:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-xs md:text-sm font-extrabold text-amber-900">Posttest — Setelah Pelatihan</h3>
                        <p class="text-[11px] md:text-xs text-amber-700/80 font-medium mt-0.5">Kerjakan setelah pelatihan selesai untuk mengukur pemahaman Anda. Peserta harus sudah mengerjakan pretest sebelumnya.</p>
                    </div>
                </div>
            </div>

            {{-- Step 1: Select Employee --}}
            <div x-show="!selectedEmployee">
                <section class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <div class="px-5 md:px-6 pt-5 pb-4 border-b border-slate-100 flex items-center gap-3.5">
                        <span class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-sm md:text-base font-extrabold text-slate-900">Identifikasi Peserta</h2>
                            <p class="text-[11px] md:text-xs text-slate-400 font-medium mt-0.5">Cari dan pilih nama Anda untuk mengerjakan posttest</p>
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

                        <div x-show="search.length >= 2" x-cloak class="mt-3 space-y-2 max-h-72 overflow-y-auto pr-0.5">
                            <template x-for="emp in filteredEmployees" :key="emp.id">
                                <button type="button" @click="selectEmployee(emp)"
                                    class="w-full rounded-2xl p-3.5 border text-left transition-all duration-200 flex items-center gap-3"
                                    :class="getEmployeeClass(emp)">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-extrabold flex-shrink-0"
                                        :class="posttestCompletedIds.includes(emp.id) || !pretestCompletedIds.includes(emp.id) ? 'bg-slate-100 text-slate-400' : 'bg-amber-50 text-amber-600'"
                                        x-text="emp.name.charAt(0).toUpperCase()"></span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block font-bold text-slate-900 text-xs md:text-sm truncate" x-text="emp.name"></span>
                                        <span class="block text-[10px] md:text-xs text-slate-400 font-medium mt-0.5 truncate" x-text="emp.position + ' • ' + emp.organization"></span>
                                    </span>
                                    <span class="flex-shrink-0">
                                        {{-- Posttest done --}}
                                        <template x-if="posttestCompletedIds.includes(emp.id)">
                                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-[10px] md:text-xs font-bold bg-emerald-50 text-emerald-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Selesai
                                            </span>
                                        </template>
                                        {{-- Pretest done, posttest pending --}}
                                        <template x-if="pretestCompletedIds.includes(emp.id) && !posttestCompletedIds.includes(emp.id)">
                                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-[10px] md:text-xs font-bold bg-amber-500 text-white shadow-md shadow-amber-500/25">
                                                Kerjakan
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                                </svg>
                                            </span>
                                        </template>
                                        {{-- Pretest not done yet --}}
                                        <template x-if="!pretestCompletedIds.includes(emp.id)">
                                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-[10px] md:text-xs font-bold bg-slate-100 text-slate-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                </svg>
                                                Pretest Belum
                                            </span>
                                        </template>
                                    </span>
                                </button>
                            </template>

                            <template x-if="filteredEmployees.length === 0 && search.length >= 2">
                                <div class="text-center py-8">
                                    <span class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-300 flex items-center justify-center mx-auto mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </span>
                                    <p class="text-slate-500 text-xs md:text-sm font-bold">Nama tidak ditemukan</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Step 2: Answer Questions --}}
            <div x-show="selectedEmployee" x-cloak class="space-y-5">
                {{-- Peserta + progress --}}
                <section class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <div class="p-4 md:p-5 flex items-center gap-3">
                        <span class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-extrabold flex-shrink-0"
                            x-text="(selectedEmployee?.name || '?').charAt(0).toUpperCase()"></span>
                        <div class="min-w-0 flex-1">
                            <p class="font-extrabold text-slate-900 text-xs md:text-sm truncate" x-text="selectedEmployee?.name"></p>
                            <p class="text-[10px] md:text-xs text-slate-400 font-medium truncate" x-text="selectedEmployee?.position + ' • ' + selectedEmployee?.organization"></p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-[10px] md:text-xs font-bold bg-amber-50 text-amber-600">
                                Posttest
                            </span>
                            <button @click="resetSelection()"
                                class="text-[10px] md:text-xs text-slate-400 hover:text-slate-600 font-bold px-2 py-1.5 transition">
                                Ganti
                            </button>
                        </div>
                    </div>
                    <div class="px-4 md:px-5 pb-4">
                        <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-500 transition-all duration-200"
                                :style="'width: ' + (questions.length ? Math.round(answeredCount / questions.length * 100) : 0) + '%'"></div>
                        </div>
                        <p class="text-[10px] md:text-xs text-slate-400 font-semibold text-center mt-1.5">
                            <span class="text-slate-700 font-bold" x-text="answeredCount + ' dari ' + questions.length"></span> soal dijawab
                        </p>
                    </div>
                </section>

                {{-- Questions --}}
                <template x-if="draftRestored > 0">
                    <div class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-[11px] md:text-xs text-blue-700 font-semibold" x-text="'Jawaban Anda sebelumnya (' + draftRestored + ' soal) dipulihkan otomatis. Periksa kembali sebelum mengirim.'"></p>
                    </div>
                </template>
                <div class="grid gap-3.5 xl:grid-cols-2 items-start">
                    <template x-for="(q, index) in questions" :key="q.id">
                        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5"
                            :class="answers[q.id] && 'border-emerald-200'">
                            <div class="flex items-start gap-3 mb-4">
                                <span class="shrink-0 w-8 h-8 rounded-xl flex items-center justify-center text-xs font-extrabold transition-colors"
                                    :class="answers[q.id] ? 'bg-emerald-500 text-white' : 'bg-amber-50 text-amber-600'"
                                    x-text="index + 1"></span>
                                <p class="text-xs md:text-sm text-slate-800 font-semibold leading-relaxed" x-text="q.question_text"></p>
                            </div>
                            <div class="space-y-2 sm:ml-11">
                                <template x-for="opt in ['a','b','c','d','e']" :key="opt">
                                    <label x-show="q['option_' + opt]"
                                        class="flex items-start gap-3 p-3 rounded-2xl border cursor-pointer transition-all duration-200"
                                        :class="answers[q.id] === opt
                                            ? 'border-amber-400 bg-amber-50/60 ring-1 ring-amber-200'
                                            : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50/60'">
                                        <input type="radio" :name="'q_' + q.id" :value="opt"
                                            x-model="answers[q.id]"
                                            class="mt-1 w-4 h-4 text-amber-500 focus:ring-amber-500 border-slate-300">
                                        <span class="min-w-0 text-[11px] md:text-sm text-slate-700 font-medium"><span class="font-extrabold text-slate-400 uppercase mr-1.5" x-text="opt + '.'"></span><span x-text="q['option_' + opt]"></span></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <button @click="submitQuiz()" :disabled="submitting || !allAnswered"
                    class="w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-gradient-to-r from-amber-500 to-orange-600 text-white text-xs md:text-sm font-extrabold rounded-2xl hover:from-amber-600 hover:to-orange-700 transition disabled:opacity-50 active:scale-[0.99] shadow-lg shadow-amber-500/25">
                    <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span x-show="!submitting">Kirim Posttest</span>
                    <span x-show="submitting">Mengirim...</span>
                </button>
                <p class="text-center text-[11px] md:text-xs font-semibold -mt-2" :class="allAnswered ? 'text-emerald-500' : 'text-amber-500'">
                    <span x-show="!allAnswered" x-text="answeredCount + ' dari ' + questions.length + ' soal dijawab'"></span>
                    <span x-show="allAnswered">Semua soal sudah dijawab — siap dikirim</span>
                </p>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center mt-8 px-4">
            <p class="text-[10px] md:text-xs text-slate-300 font-medium">D-ASSA &middot; Digital Agenda &amp; Attendance System</p>
        </div>

        {{-- Success Toast --}}
        <div x-show="showToast" x-transition x-cloak
            class="fixed top-4 left-1/2 -translate-x-1/2 bg-emerald-600 text-white pl-4 pr-5 py-3 rounded-2xl shadow-xl z-50 text-xs md:text-sm font-bold flex items-center gap-2.5 max-w-[calc(100vw-2rem)]">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            Posttest berhasil disimpan!
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
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('quizApp', () => ({
                search: '',
                employees: @json($employeesJson),
                questions: @json($questionsJson),
                pretestCompletedIds: @json($pretestCompletedIds),
                posttestCompletedIds: @json($posttestCompletedIds),
                selectedEmployee: null,
                answers: {},
                submitting: false,
                showToast: false,
                showError: false,
                errorMessage: '',
                agendaId: {{ $agenda->id }},
                stopDraftWatch: null,
                draftRestored: 0,

                draftKey() {
                    return 'posttest-draft-' + this.agendaId + '-' + this.selectedEmployee?.id;
                },

                watchDraft() {
                    if (this.stopDraftWatch) this.stopDraftWatch();
                    this.stopDraftWatch = this.$watch(
                        () => JSON.stringify(this.answers),
                        (value) => {
                            try { localStorage.setItem(this.draftKey(), value); } catch { /* abaikan */ }
                        },
                    );
                    try { localStorage.setItem(this.draftKey(), JSON.stringify(this.answers)); } catch { /* abaikan */ }
                },

                restoreDraft() {
                    let saved = null;
                    try {
                        const raw = localStorage.getItem(this.draftKey());
                        saved = raw ? JSON.parse(raw) : null;
                    } catch { saved = null; }
                    if (!saved) return 0;
                    let restored = 0;
                    Object.keys(this.answers).forEach(id => {
                        if (saved[id]) {
                            this.answers[id] = saved[id];
                            restored++;
                        }
                    });
                    return restored;
                },

                clearDraft() {
                    try { localStorage.removeItem(this.draftKey()); } catch { /* abaikan */ }
                    if (this.stopDraftWatch) {
                        this.stopDraftWatch();
                        this.stopDraftWatch = null;
                    }
                },

                get quizStepIndex() {
                    if (this.posttestCompletedIds.includes(this.selectedEmployee?.id)) return 2;
                    return this.selectedEmployee ? 1 : 0;
                },

                get filteredEmployees() {
                    if (this.search.length < 2) return [];
                    const q = this.search.toLowerCase();
                    return this.employees.filter(e => e.name.toLowerCase().includes(q));
                },

                get answeredCount() {
                    return Object.keys(this.answers).filter(k => this.answers[k]).length;
                },

                get allAnswered() {
                    return this.answeredCount === this.questions.length;
                },

                getEmployeeClass(emp) {
                    if (this.posttestCompletedIds.includes(emp.id)) {
                        return 'bg-slate-50 border-slate-100 opacity-70';
                    }
                    if (!this.pretestCompletedIds.includes(emp.id)) {
                        return 'bg-slate-50 border-slate-100 opacity-70';
                    }
                    return 'bg-white border-slate-200 cursor-pointer hover:border-amber-300 hover:shadow-md hover:shadow-amber-500/5 active:scale-[0.99]';
                },

                selectEmployee(emp) {
                    // Can only do posttest if pretest is done and posttest is not done
                    if (!this.pretestCompletedIds.includes(emp.id)) {
                        this.showErrorToast('Anda harus mengerjakan pretest terlebih dahulu di halaman absensi.');
                        return;
                    }
                    if (this.posttestCompletedIds.includes(emp.id)) return;

                    this.selectedEmployee = emp;
                    this.search = '';
                    // Initialize answers object
                    this.answers = {};
                    this.questions.forEach(q => {
                        this.answers[q.id] = '';
                    });
                    // Pulihkan draf bila browser sempat crash saat mengisi.
                    this.draftRestored = this.restoreDraft();
                    this.watchDraft();
                },

                resetSelection() {
                    if (this.stopDraftWatch) {
                        this.stopDraftWatch();
                        this.stopDraftWatch = null;
                    }
                    this.draftRestored = 0;
                    this.selectedEmployee = null;
                    this.answers = {};
                },

                async submitQuiz() {
                    if (!this.allAnswered || this.submitting) return;

                    this.submitting = true;

                    try {
                        const response = await fetch(`/absen/${this.agendaId}/quiz`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            },
                            body: JSON.stringify({
                                employee_id: this.selectedEmployee.id,
                                answers: this.answers,
                            }),
                        });

                        if (response.redirected) {
                            window.location.href = response.url;
                        } else {
                            const data = await response.json();

                            if (response.ok) {
                                this.posttestCompletedIds.push(this.selectedEmployee.id);
                                this.clearDraft();
                                this.draftRestored = 0;
                                window.location.href = '{{ route("attendance.show", $agenda) }}';
                            } else {
                                this.showErrorToast(data.message || 'Terjadi kesalahan.');
                            }
                        }
                    } catch (e) {
                        this.showErrorToast('Gagal menghubungi server.');
                    } finally {
                        this.submitting = false;
                    }
                },

                showErrorToast(msg) {
                    this.errorMessage = msg;
                    this.showError = true;
                    setTimeout(() => this.showError = false, 3000);
                },
            }));
        });
    </script>
</body>

</html>
