<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Posttest - {{ $agenda->title }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="bg-gray-50 min-h-screen font-sans">
    <div x-data="quizApp" class="max-w-6xl mx-auto pb-8">
        {{-- Header --}}
        <x-agenda-header :agenda="$agenda">
            <x-slot:actions>
                <a href="{{ route('attendance.show', $agenda) }}"
                    class="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur-sm text-white text-[10px] md:text-[11px] font-medium px-3 py-1.5 rounded-full hover:bg-white/25 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    Absensi
                </a>
            </x-slot:actions>
        </x-agenda-header>

        {{-- Posttest Info Banner --}}
        <div class="px-4 mt-5" x-show="!selectedEmployee">
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl border border-amber-100 p-4 mb-4">
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 md:w-8 md:h-8 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 md:w-4 md:h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-[10px] md:text-[11px] md:text-sm font-bold text-amber-900">Posttest — Setelah Pelatihan</h3>
                        <p class="text-[9px] md:text-[10px] md:text-xs text-amber-600 mt-0.5">Kerjakan soal ini setelah pelatihan selesai untuk mengukur pemahaman Anda. Peserta harus sudah mengerjakan pretest sebelumnya.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 1: Select Employee --}}
        <div x-show="!selectedEmployee" class="px-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 md:w-8 md:h-8 rounded-xl bg-amber-50 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 md:w-4 md:h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-gray-900 text-[10px] md:text-[11px] md:text-sm">Identifikasi Peserta</h2>
                        <p class="text-[9px] md:text-[10px] md:text-xs text-gray-400">Cari dan pilih nama Anda untuk mengerjakan posttest</p>
                    </div>
                </div>
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-3.5 h-3.5 md:w-4 md:h-4 text-gray-400 absolute left-3 md:left-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" x-model="search" placeholder="Ketik nama Anda untuk mencari..."
                        class="w-full rounded-2xl border-gray-200 shadow-sm focus:border-primary focus:ring-primary pl-9 md:pl-12 pr-4 py-1.5 md:py-3 text-[11px] md:text-xs bg-white">
                </div>

                <div x-show="search.length >= 2" x-cloak class="mt-3 space-y-2 max-h-64 overflow-y-auto">
                    <template x-for="emp in filteredEmployees" :key="emp.id">
                        <div @click="selectEmployee(emp)"
                            class="rounded-xl p-3 border transition-all duration-200 flex items-center justify-between"
                            :class="getEmployeeClass(emp)">
                            <div class="min-w-0 flex-1">
                                <div class="font-semibold text-gray-900 text-[11px] md:text-sm" x-text="emp.name"></div>
                                <div class="text-[10px] md:text-xs text-gray-500 mt-0.5" x-text="emp.position + ' - ' + emp.organization"></div>
                            </div>
                            <div class="shrink-0 ml-3">
                                {{-- Posttest done --}}
                                <template x-if="posttestCompletedIds.includes(emp.id)">
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] md:text-xs font-medium bg-green-50 text-green-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Selesai
                                    </span>
                                </template>
                                {{-- Pretest done, posttest pending --}}
                                <template x-if="pretestCompletedIds.includes(emp.id) && !posttestCompletedIds.includes(emp.id)">
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] md:text-xs font-medium bg-amber-50 text-amber-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Kerjakan Posttest
                                    </span>
                                </template>
                                {{-- Pretest not done yet --}}
                                <template x-if="!pretestCompletedIds.includes(emp.id)">
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] md:text-xs font-medium bg-gray-100 text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        Pretest Belum
                                    </span>
                                </template>
                            </div>
                        </div>
                    </template>

                    <template x-if="filteredEmployees.length === 0 && search.length >= 2">
                        <div class="text-center py-6">
                            <div class="w-7 h-7 md:w-8 md:h-8 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 md:w-4 md:h-4 text-gray-300" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <p class="text-gray-400 text-[11px] md:text-sm">Nama tidak ditemukan.</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Step 2: Answer Questions --}}
        <div x-show="selectedEmployee" x-cloak class="px-4 mt-5">
            {{-- Employee info bar --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4 flex items-center justify-between">
                <div class="min-w-0">
                    <div class="font-bold text-gray-900 text-[10px] md:text-[11px] md:text-sm" x-text="selectedEmployee?.name"></div>
                    <div class="text-[9px] md:text-[10px] md:text-xs text-gray-500" x-text="selectedEmployee?.position + ' - ' + selectedEmployee?.organization"></div>
                </div>
                <div class="flex items-center gap-2 shrink-0 ml-3">
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] md:text-xs font-semibold bg-amber-50 text-amber-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Posttest
                    </span>
                    <button @click="resetSelection()"
                        class="text-[10px] md:text-xs text-gray-400 hover:text-gray-600 transition font-medium">
                        Ganti
                    </button>
                </div>
            </div>

            {{-- Questions --}}
            <div class="space-y-4">
                <template x-for="(q, index) in questions" :key="q.id">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <div class="flex items-start gap-3 mb-4">
                            <span class="shrink-0 w-7 h-7 md:w-8 md:h-8 rounded-lg bg-amber-50 flex items-center justify-center text-[10px] md:text-xs font-bold text-amber-600"
                                x-text="index + 1"></span>
                            <p class="text-[10px] md:text-sm text-gray-800 font-medium leading-relaxed" x-text="q.question_text"></p>
                        </div>
                        <div class="space-y-2 ml-10">
                            <template x-for="opt in ['a','b','c','d','e']" :key="opt">
                                <label x-show="q['option_' + opt]"
                                    class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-all duration-200"
                                    :class="answers[q.id] === opt
                                        ? 'border-amber-400 bg-amber-50/50 ring-1 ring-amber-200'
                                        : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50/50'">
                                    <input type="radio" :name="'q_' + q.id" :value="opt"
                                        x-model="answers[q.id]"
                                        class="mt-0.5 text-amber-500 focus:ring-amber-500">
                                    <div class="flex items-start gap-2 min-w-0">
                                        <span class="shrink-0 text-[10px] md:text-xs font-bold text-gray-400 uppercase mt-0.5" x-text="opt + '.'"></span>
                                        <span class="text-[10px] md:text-sm text-gray-700" x-text="q['option_' + opt]"></span>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Submit button --}}
            <div class="mt-6">
                <button @click="submitQuiz()" :disabled="submitting || !allAnswered"
                    class="w-full px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white text-[10px] md:text-sm font-semibold rounded-2xl hover:from-amber-600 hover:to-orange-700 transition disabled:opacity-50 active:scale-[0.99] shadow-lg shadow-amber-500/20">
                    <span x-show="!submitting">Kirim Posttest</span>
                    <span x-show="submitting">Mengirim...</span>
                </button>
                <p class="text-center text-[10px] md:text-xs mt-2" :class="allAnswered ? 'text-gray-300' : 'text-amber-500'">
                    <span x-show="!allAnswered" x-text="answeredCount + ' dari ' + questions.length + ' soal dijawab'"></span>
                    <span x-show="allAnswered">Semua soal sudah dijawab</span>
                </p>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center mt-8 px-4">
            <p class="text-xs text-gray-300">D-ASSA &middot; Digital Agenda & Attendance System</p>
        </div>

        {{-- Success Toast --}}
        <div x-show="showToast" x-transition x-cloak
            class="fixed top-4 left-1 translate-x-2 bg-greeen-600 text-white px-6 py-3 rounded-xl shadow-lg z-50 text-[10px] md:text-sm font-medium flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            Posttest berhasil disimpan!
        </div>

        {{-- Error Toast --}}
        <div x-show="showError" x-transition x-cloak
            class="fixed top-4 left-1 translate-x-2 bg-red-500 text-white px-6 py-3 rounded-xl shadow-lg z-50 text-[10px] md:text-sm font-medium flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
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
                        return 'bg-gray-50 border-gray-100 opacity-60';
                    }
                    if (!this.pretestCompletedIds.includes(emp.id)) {
                        return 'bg-gray-50 border-gray-100 opacity-60';
                    }
                    return 'bg-white border-gray-200 cursor-pointer hover:border-amber-200 hover:shadow-sm active:scale-[0.99]';
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
                },

                resetSelection() {
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
