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
    <div class="pb-10">
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
                    <p class="text-[11px] md:text-xs text-slate-400 font-medium mt-0.5">Isi nama, organisasi (opsional), dan tanda tangan.</p>
                </div>

                <form method="POST" action="{{ route('agenda.external-attend.store', $agenda) }}" enctype="multipart/form-data" class="px-5 md:px-6 py-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama</label>
                        <input type="text" name="name" required value="{{ old('name') }}"
                            class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" />
                        @error('name') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Organisasi / Instansi (opsional)</label>
                        <input type="text" name="organization" value="{{ old('organization') }}"
                            class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tanda Tangan (gambar)</label>
                        <input type="file" name="signature" accept="image/*" required
                            class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none file:mr-4 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20" />
                        @error('signature') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-primary text-white text-sm font-bold shadow-md shadow-primary/20 hover:bg-primary-700 transition-all">
                        Simpan Tanda Tangan
                    </button>
                </form>
            </section>
        </div>
    </div>
</body>

</html>
