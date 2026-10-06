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
    <div x-data="photoUploader()" class="pb-10">
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

            {{-- Stepper --}}
            <ol class="flex items-center gap-1.5 md:gap-2 text-[10px] md:text-xs font-bold">
                <template x-for="(label, i) in ['Pilih Foto', 'Pratinjau', 'Unggah']" :key="i">
                    <li class="flex items-center gap-1.5 md:gap-2 flex-1 last:flex-none">
                        <span class="flex items-center gap-1.5 md:gap-2">
                            <span class="w-5 h-5 md:w-6 md:h-6 rounded-full flex items-center justify-center text-[10px] md:text-[11px] transition-colors"
                                :class="currentStep > i + 1 ? 'bg-emerald-500 text-white' : (currentStep === i + 1 ? 'bg-primary text-white shadow-md shadow-primary/30' : 'bg-slate-200 text-slate-400')">
                                <svg x-show="currentStep > i + 1" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                <span x-show="currentStep <= i + 1" x-text="i + 1"></span>
                            </span>
                            <span :class="currentStep >= i + 1 ? 'text-slate-700' : 'text-slate-400'" x-text="label"></span>
                        </span>
                        <span x-show="i < 2" class="flex-1 h-0.5 rounded-full mx-1" :class="currentStep > i + 1 ? 'bg-emerald-400' : 'bg-slate-200'"></span>
                    </li>
                </template>
            </ol>

            {{-- Card: Ambil Foto --}}
            <section class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="px-5 md:px-6 pt-5 pb-4 border-b border-slate-100 flex items-start gap-3.5">
                    <span class="w-10 h-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-sm md:text-base font-extrabold text-slate-900">Ambil atau Pilih Foto</h2>
                        <p class="text-[11px] md:text-xs text-slate-400 font-medium mt-0.5">Foto dikompresi otomatis agar cepat diunggah &middot; maks. 3MB per foto di server</p>
                    </div>
                </div>

                <div class="p-5 md:p-6">
                    {{-- Hidden file inputs ( dipertahankan ) --}}
                    <input type="file" x-ref="camera" accept="image/*" capture="camera" class="hidden" @change="handleFiles($event)">
                    <input type="file" x-ref="gallery" accept="image/*" multiple class="hidden" @change="handleFiles($event)">

                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" @click="$refs.camera.click()"
                            class="group flex flex-col items-center gap-2 px-3 py-5 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/60 hover:border-primary/50 hover:bg-primary/5 active:scale-[0.98] transition-all">
                            <span class="w-11 h-11 rounded-2xl bg-white shadow-sm border border-slate-100 text-slate-400 group-hover:text-primary group-hover:border-primary/20 flex items-center justify-center transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
                            </span>
                            <span class="text-xs md:text-sm font-bold text-slate-600 group-hover:text-slate-800">Kamera</span>
                            <span class="text-[10px] text-slate-400 font-medium">Foto langsung</span>
                        </button>
                        <button type="button" @click="$refs.gallery.click()"
                            class="group flex flex-col items-center gap-2 px-3 py-5 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/60 hover:border-primary/50 hover:bg-primary/5 active:scale-[0.98] transition-all">
                            <span class="w-11 h-11 rounded-2xl bg-white shadow-sm border border-slate-100 text-slate-400 group-hover:text-primary group-hover:border-primary/20 flex items-center justify-center transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M2.25 18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V6a2.25 2.25 0 00-2.25-2.25h-15A2.25 2.25 0 002.25 6v12z"/></svg>
                            </span>
                            <span class="text-xs md:text-sm font-bold text-slate-600 group-hover:text-slate-800">Galeri</span>
                            <span class="text-[10px] text-slate-400 font-medium">Bisa pilih banyak</span>
                        </button>
                    </div>

                    {{-- Drag & drop (desktop) --}}
                    <div @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="handleDrop($event)"
                        class="hidden md:flex mt-3 items-center justify-center gap-2 px-4 py-3.5 rounded-2xl border border-slate-200 bg-slate-50/60 text-xs font-semibold text-slate-400 transition-colors"
                        :class="dragging && 'border-primary/50 bg-primary/5 text-primary'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"/></svg>
                        <span x-text="dragging ? 'Lepaskan untuk menambahkan foto' : 'atau seret & letakkan file foto ke sini'"></span>
                    </div>
                </div>
            </section>

            {{-- Peringatan file dilewati --}}
            <template x-if="skipped.length > 0">
                <div class="flex items-start gap-3 px-4 py-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 shadow-sm">
                    <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    <div class="min-w-0 text-[11px] md:text-xs font-medium">
                        <p class="font-bold">Beberapa file dilewati (melebihi 10MB):</p>
                        <ul class="list-disc list-inside mt-1 space-y-0.5">
                            <template x-for="(name, i) in skipped" :key="i">
                                <li class="truncate" x-text="name"></li>
                            </template>
                        </ul>
                    </div>
                </div>
            </template>

            {{-- Pesan error unggah --}}
            <template x-if="error">
                <div class="flex items-start gap-3 px-4 py-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 shadow-sm">
                    <svg class="w-5 h-5 text-rose-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/></svg>
                    <div class="min-w-0">
                        <p class="text-xs md:text-sm font-bold">Gagal mengunggah</p>
                        <p class="text-[11px] md:text-xs font-medium mt-0.5" x-text="error"></p>
                    </div>
                </div>
            </template>

            {{-- Card: Pratinjau --}}
            <template x-if="imagePreviews.length > 0">
                <section class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <div class="px-5 md:px-6 pt-5 pb-4 border-b border-slate-100 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-2xl bg-violet-50 text-violet-500 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M2.25 18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V6a2.25 2.25 0 00-2.25-2.25h-15A2.25 2.25 0 002.25 6v12z"/></svg>
                            </span>
                            <div>
                                <h2 class="text-sm md:text-base font-extrabold text-slate-900">Pratinjau Foto</h2>
                                <p class="text-[11px] md:text-xs text-slate-400 font-medium mt-0.5">
                                    <span class="font-bold text-slate-600" x-text="imagePreviews.length"></span> foto dipilih
                                    &middot; total <span class="font-bold text-slate-600" x-text="formatSize(totalSize())"></span>
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="clearAll()" :disabled="uploading"
                            class="text-[11px] md:text-xs font-bold text-rose-500 hover:text-rose-600 hover:bg-rose-50 px-3 py-2 rounded-xl transition disabled:opacity-40">
                            Hapus semua
                        </button>
                    </div>

                    <div class="p-5 md:p-6 space-y-4">
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5">
                            <template x-for="(img, index) in imagePreviews" :key="index">
                                <div class="group relative rounded-2xl overflow-hidden border border-slate-200 bg-slate-50">
                                    <img :src="img.url" alt="Pratinjau foto" class="w-full aspect-square object-cover">
                                    <span class="absolute bottom-1.5 left-1.5 px-2 py-0.5 rounded-lg bg-black/55 backdrop-blur-sm text-white text-[10px] font-bold" x-text="formatSize(img.size)"></span>
                                    <button type="button" @click="removeImage(index)" :disabled="uploading" aria-label="Hapus foto"
                                        class="absolute top-1.5 right-1.5 w-7 h-7 bg-black/55 backdrop-blur-sm text-white rounded-full flex items-center justify-center hover:bg-rose-500 active:scale-90 transition disabled:opacity-40">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                            {{-- Tile tambah foto --}}
                            <button type="button" @click="$refs.gallery.click()" :disabled="uploading"
                                class="flex flex-col items-center justify-center gap-1.5 aspect-square rounded-2xl border-2 border-dashed border-slate-200 text-slate-400 hover:border-primary/50 hover:text-primary hover:bg-primary/5 active:scale-[0.98] transition-all disabled:opacity-40">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                <span class="text-[10px] md:text-xs font-bold">Tambah</span>
                            </button>
                        </div>

                        {{-- Progress --}}
                        <template x-if="uploading">
                            <div class="space-y-1.5">
                                <div class="h-2.5 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-primary to-emerald-400 transition-all duration-200" :style="'width: ' + progress + '%'"></div>
                                </div>
                                <p class="text-[11px] md:text-xs text-slate-400 font-semibold text-center">
                                    Mengunggah... <span class="text-slate-600 font-bold" x-text="progress + '%'"></span> — mohon jangan tutup halaman
                                </p>
                            </div>
                        </template>

                        <button type="button" @click="uploadAll()" :disabled="uploading"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-2xl bg-primary text-white text-xs md:text-sm font-extrabold shadow-lg shadow-primary/25 hover:bg-primary-700 active:scale-[0.99] transition-all disabled:opacity-60">
                            <svg x-show="!uploading" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"/></svg>
                            <svg x-show="uploading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <span x-text="uploading ? 'Mengunggah...' : ('Unggah ' + imagePreviews.length + ' Foto')"></span>
                        </button>
                    </div>
                </section>
            </template>

            {{-- Empty state (belum ada foto dipilih) --}}
            <template x-if="imagePreviews.length === 0">
                <div class="border-2 border-dashed border-slate-200 rounded-3xl p-6 md:p-8 text-center bg-white/60">
                    <span class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-300 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M2.25 18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V6a2.25 2.25 0 00-2.25-2.25h-15A2.25 2.25 0 002.25 6v12z"/></svg>
                    </span>
                    <p class="text-slate-500 font-bold text-xs md:text-sm">Belum ada foto dipilih</p>
                    <p class="text-[11px] md:text-xs text-slate-400 font-medium mt-1">Gunakan tombol Kamera atau Galeri di atas untuk mulai</p>
                </div>
            </template>

            {{-- Card: Foto terkumpul --}}
            @if($agenda->images->count() > 0)
                <section class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <div class="px-5 md:px-6 pt-5 pb-4 border-b border-slate-100 flex items-center gap-3">
                        <span class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <div>
                            <h2 class="text-sm md:text-base font-extrabold text-slate-900">
                                Foto Terkumpul
                                <span class="ml-1.5 inline-flex items-center px-2 py-0.5 rounded-lg bg-slate-100 text-slate-500 text-[10px] md:text-[11px] font-extrabold align-middle">{{ $agenda->images->count() }}</span>
                            </h2>
                            <p class="text-[11px] md:text-xs text-slate-400 font-medium mt-0.5">Ketuk foto untuk memperbesar</p>
                        </div>
                    </div>
                    <div class="p-5 md:p-6">
                        <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-2 md:gap-2.5">
                            @foreach($agenda->images as $i => $image)
                                <button type="button" @click="lightbox = {{ $i }}" aria-label="Perbesar foto {{ $i + 1 }}"
                                    class="group relative rounded-2xl overflow-hidden border border-slate-100 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary/40">
                                    <img src="{{ Storage::url($image->image_path) }}" alt="Dokumentasi {{ $i + 1 }}" loading="lazy"
                                        class="w-full aspect-square object-cover group-hover:scale-105 transition-transform duration-300">
                                    <span class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/20 transition-colors flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6"/></svg>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        </div>

        {{-- Footer --}}
        <div class="text-center mt-8 px-4">
            <p class="text-[10px] md:text-xs text-slate-300 font-medium">D-ASSA &middot; Digital Agenda &amp; Attendance System</p>
        </div>

        {{-- Lightbox --}}
        <template x-if="lightbox !== null">
            <div class="fixed inset-0 z-[100] bg-slate-950/90 backdrop-blur-sm flex items-center justify-center p-4"
                @click.self="lightbox = null" @keydown.escape.window="lightbox = null" x-transition.opacity>
                <button type="button" @click="lightbox = null" aria-label="Tutup"
                    class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <template x-if="lightbox > 0">
                    <button type="button" @click="lightbox--" aria-label="Foto sebelumnya"
                        class="absolute left-3 md:left-6 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    </button>
                </template>
                <figure class="max-w-full">
                    <img :src="gallery[lightbox]" alt="Dokumentasi" class="max-w-full max-h-[80vh] rounded-2xl object-contain shadow-2xl">
                    <figcaption class="text-center text-white/70 text-xs font-semibold mt-3" x-text="(lightbox + 1) + ' / ' + gallery.length"></figcaption>
                </figure>
                <template x-if="lightbox < gallery.length - 1">
                    <button type="button" @click="lightbox++" aria-label="Foto berikutnya"
                        class="absolute right-3 md:right-6 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </button>
                </template>
            </div>
        </template>
    </div>
<script>
function photoUploader() {
    return {
        imagePreviews: [],
        uploading: false,
        progress: 0,
        error: '',
        skipped: [],
        dragging: false,
        lightbox: null,
        gallery: @js($agenda->images->map(fn ($img) => Storage::url($img->image_path))->values()->all()),
        uploadUrl: @js(route('agenda.image.store', $agenda)),

        get currentStep() {
            if (this.uploading || this.progress === 100) return 3;
            return this.imagePreviews.length > 0 ? 2 : 1;
        },

        handleFiles(event) {
            this.addFiles(event.target.files);
            event.target.value = '';
        },

        handleDrop(event) {
            this.dragging = false;
            if (event.dataTransfer?.files?.length) {
                this.addFiles(event.dataTransfer.files);
            }
        },

        addFiles(files) {
            this.error = '';
            for (const file of files) {
                if (file.size > 10 * 1024 * 1024) {
                    this.skipped.push(file.name + ' (' + this.formatSize(file.size) + ')');
                    continue;
                }
                this.compressImage(file).then(compressed => {
                    this.imagePreviews.push({
                        url: URL.createObjectURL(compressed),
                        file: compressed,
                        size: compressed.size
                    });
                });
            }
        },

        formatSize(bytes) {
            if (!bytes) return '0 KB';
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return Math.round(bytes / 1024) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        },

        totalSize() {
            return this.imagePreviews.reduce((sum, p) => sum + (p.size || 0), 0);
        },

        compressImage(file, maxWidth = 1024, maxHeight = 1024, targetSize = 500 * 1024) {
            return new Promise((resolve) => {
                const img = new Image();
                img.onload = () => {
                    let width = img.width;
                    let height = img.height;
                    const aspectRatio = width / height;

                    if (width > maxWidth || height > maxHeight) {
                        if (aspectRatio > 1) {
                            width = maxWidth;
                            height = Math.round(maxWidth / aspectRatio);
                        } else {
                            height = maxHeight;
                            width = Math.round(maxHeight * aspectRatio);
                        }
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    let quality = 0.8;

                    const changeExtensionToWebp = (name) => {
                        return name.replace(/\.[^/.]+$/, '') + '.webp';
                    };

                    const processBlob = (blob) => {
                        if (blob.size <= targetSize || quality <= 0.1) {
                            resolve(new File([blob], changeExtensionToWebp(file.name), { type: 'image/webp', lastModified: Date.now() }));
                            URL.revokeObjectURL(img.src);
                            return;
                        }
                        quality -= 0.05;
                        canvas.toBlob(processBlob, 'image/webp', quality);
                    };

                    canvas.toBlob(processBlob, 'image/webp', quality);
                };
                img.src = URL.createObjectURL(file);
            });
        },

        removeImage(index) {
            URL.revokeObjectURL(this.imagePreviews[index].url);
            this.imagePreviews.splice(index, 1);
        },

        clearAll() {
            this.imagePreviews.forEach(p => URL.revokeObjectURL(p.url));
            this.imagePreviews = [];
        },

        async uploadAll() {
            if (!this.imagePreviews.length || this.uploading) return;
            this.uploading = true;
            this.progress = 0;
            this.error = '';
            const fd = new FormData();
            this.imagePreviews.forEach(p => fd.append('images[]', p.file));
            try {
                const outcome = await new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', this.uploadUrl);
                    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.upload.onprogress = (e) => {
                        if (e.lengthComputable) {
                            this.progress = Math.round((e.loaded / e.total) * 100);
                        }
                    };
                    xhr.onload = () => resolve({ ok: xhr.status >= 200 && xhr.status < 300, body: xhr.responseText });
                    xhr.onerror = () => reject(new Error('network'));
                    xhr.send(fd);
                });
                if (outcome.ok) {
                    this.progress = 100;
                    window.location.reload();
                } else {
                    let message = 'Gagal mengunggah foto.';
                    try {
                        const data = JSON.parse(outcome.body);
                        if (data?.message) message = data.message;
                    } catch { /* abaikan, pakai pesan bawaan */ }
                    this.error = message;
                }
            } catch {
                this.error = 'Terjadi kesalahan jaringan. Periksa koneksi lalu coba lagi.';
            } finally {
                this.uploading = false;
            }
        }
    };
}
</script>
</body>

</html>
