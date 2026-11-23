<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tanda Tangan Eksternal - {{ $agenda->title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-tab.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>

<body class="bg-slate-100 min-h-screen font-sans text-slate-800">
    <div class="pb-10" x-data="externalAttendApp()" x-init="load(); initPad()">
        <x-agenda-header :agenda="$agenda">
            <x-slot:actions>
                <a href="{{ route('home') }}"
                    class="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur-sm text-white text-[10px] md:text-[11px] font-semibold px-3.5 py-1.5 rounded-full hover:bg-white/25 active:scale-95 transition">
                    Kembali
                </a>
            </x-slot:actions>
        </x-agenda-header>

        <div class="px-4 md:px-8 mt-6 space-y-5">
            @if(session('success'))
                <div class="flex items-center gap-3 px-4 py-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 shadow-sm">
                    <p class="text-xs md:text-sm font-bold">{{ session('success') }}</p>
                </div>
            @endif

            <section class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="px-5 md:px-6 pt-5 pb-4 border-b border-slate-100">
                    <h2 class="text-sm md:text-base font-extrabold text-slate-900">Tanda Tangan Tamu Eksternal</h2>
                    <p class="text-[11px] md:text-xs text-slate-400 font-medium mt-0.5">Isi nama, organisasi (opsional), lalu bubuhkan tanda tangan.</p>
                </div>

                <div class="px-5 md:px-6 py-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama</label>
                            <input type="text" x-model="form.name" required
                                class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Organisasi (opsional)</label>
                            <input type="text" x-model="form.organization"
                                class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" />
                        </div>
                    </div>

                    <div>
                        <p class="text-[11px] md:text-xs text-slate-400 font-medium mb-2">Bubuhkan tanda tangan pada kolom di bawah ini</p>
                        <div class="border-2 border-dashed border-slate-200 rounded-2xl mb-3 bg-slate-50 overflow-hidden focus-within:border-primary/40 transition-colors">
                            <canvas id="ext-signature-canvas" class="w-full bg-white" style="height: 200px; touch-action: none;"></canvas>
                        </div>
                        <div class="flex gap-2.5">
                            <button type="button" @click="clearPad()"
                                class="flex-1 px-4 py-3 border border-slate-200 text-slate-600 text-xs md:text-sm font-bold rounded-2xl hover:bg-slate-50 transition active:scale-[0.98]">
                                Hapus
                            </button>
                            <button type="button" @click="submit()" :disabled="submitting"
                                class="flex-[2] inline-flex items-center justify-center gap-2 px-4 py-3 bg-primary text-white text-xs md:text-sm font-extrabold rounded-2xl hover:bg-primary-700 shadow-lg shadow-primary/25 transition disabled:opacity-50 active:scale-[0.98]">
                                <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                <span x-show="!submitting">Simpan Absensi</span>
                                <span x-show="submitting">Menyimpan...</span>
                            </button>
                        </div>
                    </div>

                    <div x-show="showToast" x-transition x-cloak
                        class="fixed top-4 left-1/2 -translate-x-1/2 bg-emerald-600 text-white pl-4 pr-5 py-3 rounded-2xl shadow-xl z-50 text-xs md:text-sm font-bold flex items-center gap-2.5 max-w-[calc(100vw-2rem)]">
                        <span x-text="toastMessage">Tanda tangan berhasil disimpan!</span>
                    </div>
                    <div x-show="showError" x-transition x-cloak
                        class="fixed top-4 left-1/2 -translate-x-1/2 bg-rose-500 text-white pl-4 pr-5 py-3 rounded-2xl shadow-xl z-50 text-xs md:text-sm font-bold flex items-center gap-2.5 max-w-[calc(100vw-2rem)]">
                        <span x-text="errorMessage"></span>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        function externalAttendApp() {
            return {
                members: [],
                saving: false,
                submitting: false,
                showToast: false,
                toastMessage: '',
                showError: false,
                errorMessage: '',
                form: { name: '', organization: '' },
                signaturePad: null,

                initPad() {
                    this.$nextTick(() => {
                        const canvas = document.getElementById('ext-signature-canvas');
                        if (!canvas || !window.SignaturePad) return;
                        const rect = canvas.getBoundingClientRect();
                        canvas.width = rect.width || canvas.parentElement.offsetWidth;
                        canvas.height = 200;
                        this.signaturePad = new window.SignaturePad(canvas, {
                            backgroundColor: 'rgb(255,255,255)',
                            penColor: 'rgb(0,0,0)',
                        });
                    });
                },

                clearPad() {
                    if (this.signaturePad) this.signaturePad.clear();
                },

                async load() {
                    try {
                        const { data } = await axios.get('{{ route('agenda.external-attend.index', $agenda) }}', {
                            headers: { 'Accept': 'application/json' },
                        });
                        this.members = data.items || [];
                    } catch (e) {
                        console.error(e);
                    }
                },

                async submit() {
                    if (!this.form.name.trim()) {
                        this.errorMessage = 'Nama wajib diisi.';
                        this.showError = true;
                        setTimeout(() => this.showError = false, 3000);
                        return;
                    }
                    if (!this.signaturePad || this.signaturePad.isEmpty()) {
                        this.errorMessage = 'Tanda tangan wajib diisi.';
                        this.showError = true;
                        setTimeout(() => this.showError = false, 3000);
                        return;
                    }
                    this.submitting = true;
                    try {
                        const blob = await (await fetch(this.signaturePad.toDataURL('image/png'))).blob();
                        const fd = new FormData();
                        fd.append('name', this.form.name);
                        if (this.form.organization) fd.append('organization', this.form.organization);
                        fd.append('signature', blob, 'signature.png');

                        const { data } = await axios.post('{{ route('agenda.external-attend.store', $agenda) }}', fd, {
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        });

                        this.members.unshift(data.item);
                        this.form = { name: '', organization: '' };
                        this.clearPad();
                        this.toastMessage = data.message || 'Tanda tangan berhasil disimpan.';
                        this.showToast = true;
                        setTimeout(() => this.showToast = false, 3000);
                    } catch (e) {
                        this.errorMessage = 'Gagal menyimpan tanda tangan.';
                        this.showError = true;
                        setTimeout(() => this.showError = false, 3000);
                        console.error(e);
                    } finally {
                        this.submitting = false;
                    }
                },
            };
        }
    </script>
</body>

</html>
