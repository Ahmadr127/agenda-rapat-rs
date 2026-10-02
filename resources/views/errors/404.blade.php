<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 — Halaman Tidak Ditemukan | RS AZRA</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-tab.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                    colors: {
                        primary: { DEFAULT: '#007774', 50: '#effafa', 100: '#d7f0f0', 700: '#00605e' },
                    },
                },
            },
        };
    </script>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-50/80 flex items-center justify-center p-6">
        <div class="w-full max-w-lg text-center">
            <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-xl shadow-gray-100/50">
                <div class="bg-[#007774] px-8 py-10 relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full"></div>
                    <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-white/10 rounded-full"></div>
                    <p class="relative text-white font-extrabold tracking-tight leading-none" style="font-size: 5.5rem;">404</p>
                    <p class="relative text-white/70 text-sm font-semibold mt-1 uppercase tracking-[0.2em]">Halaman Tidak Ditemukan</p>
                </div>
                <div class="px-8 py-8">
                    <div class="w-14 h-14 rounded-2xl bg-primary-50 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/></svg>
                    </div>
                    <h1 class="text-xl font-extrabold text-gray-900 tracking-tight">Ups, alamat ini tidak tersedia</h1>
                    <p class="text-sm text-gray-400 mt-2">Halaman yang Anda cari mungkin sudah dipindah, dihapus, atau alamatnya salah ketik.</p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-2.5 mt-6">
                        <a href="{{ url('/') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-primary text-white text-sm font-bold shadow-md shadow-primary/20 hover:bg-primary-700 active:scale-[0.98] transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg>
                            Beranda
                        </a>
                        <button onclick="history.back()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-gray-100 text-gray-600 text-sm font-bold hover:bg-gray-200 active:scale-[0.98] transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/></svg>
                            Kembali
                        </button>
                    </div>
                    <p class="text-xs text-gray-300 font-mono mt-6">RS AZRA &middot; Digital Agenda &amp; Attendance &middot; Error 404</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
