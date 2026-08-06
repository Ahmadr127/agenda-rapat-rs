<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $agenda->allowsNotes() ? 'Input Notulensi' : 'Dokumentasi Foto' }} - {{ $agenda->title }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>

<body class="bg-gray-50 min-h-screen font-sans">
    <div x-data="photoUploader()" class="max-w-6xl mx-auto pb-8">
        {{-- Header --}}
        <x-agenda-header :agenda="$agenda">
            <x-slot:actions>
                <a href="{{ route('attendance.show', $agenda) }}"
                    class="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur-sm text-white text-[10px] md:text-[11px] font-medium px-3 py-1.5 rounded-full hover:bg-white/25 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Absensi
                </a>
            </x-slot:actions>
        </x-agenda-header>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition class="mx-4 mt-4">
                <div class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-green-50 border border-green-200 text-green-800">
                    <svg class="w-4 h-4 md:w-5 md:h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-[10px] md:text-sm font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <div x-transition class="px-4 mt-5 space-y-4">
            {{-- Upload Form --}}
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <h3 class="text-[10px] md:text-sm font-bold text-gray-800 mb-4">Unggah Foto Dokumentasi</h3>

                {{-- Hidden file inputs --}}
                <input type="file" x-ref="camera" accept="image/*" capture="camera" class="hidden" @change="handleFiles($event)">
                <input type="file" x-ref="gallery" accept="image/*" multiple class="hidden" @change="handleFiles($event)">

                {{-- Capture buttons --}}
                <div class="grid grid-cols-2 gap-2 mb-4">
                    <button type="button" @click="$refs.camera.click()" class="flex flex-col items-center gap-1.5 px-2 py-3 rounded-xl border-2 border-dashed border-gray-200 hover:border-primary/40 transition-colors">
                        <svg class="w-4 h-4 md:w-5 md:h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
                        <span class="text-[10px] font-semibold text-gray-500">Kamera</span>
                    </button>
                    <button type="button" @click="$refs.gallery.click()" class="flex flex-col items-center gap-1.5 px-2 py-3 rounded-xl border-2 border-dashed border-gray-200 hover:border-primary/40 transition-colors">
                        <svg class="w-4 h-4 md:w-5 md:h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M2.25 18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V6a2.25 2.25 0 00-2.25-2.25h-15A2.25 2.25 0 002.25 6v12z"/></svg>
                        <span class="text-[10px] font-semibold text-gray-500">Galeri</span>
                    </button>
                </div>

                {{-- Preview grid --}}
                <template x-if="imagePreviews.length > 0">
                    <div class="space-y-3">
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="(img, index) in imagePreviews" :key="index">
                                <div class="relative">
                                    <img :src="img.url" class="w-full aspect-square object-cover rounded-xl border border-gray-200">
                                    <button type="button" @click="removeImage(index)" class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-600 transition">
                                        <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <p class="text-[10px] md:text-xs text-gray-400 text-center" x-text="imagePreviews.length + ' foto dipilih'"></p>
                        <button type="button" @click="uploadAll()" :disabled="uploading" class="w-full px-4 py-2.5 rounded-xl bg-primary text-white text-[10px] md:text-sm font-semibold shadow-md shadow-primary/20 hover:bg-primary-700 active:scale-[0.98] transition-all disabled:opacity-50">
                            <span x-show="!uploading">Unggah Semua Foto</span>
                            <span x-show="uploading">Mengunggah...</span>
                        </button>
                    </div>
                </template>

                {{-- Empty state --}}
                <template x-if="imagePreviews.length === 0">
                    <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center">
                        <svg class="w-8 h-8 md:w-10 md:h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M2.25 18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V6a2.25 2.25 0 00-2.25-2.25h-15A2.25 2.25 0 002.25 6v12z"/></svg>
                        <p class="text-gray-500 font-medium text-[12px]">Ambil atau pilih foto</p>
                        <p class="text-[10px] md:text-xs text-gray-400 mt-1">Gunakan tombol di atas, maks. 3MB per foto</p>
                    </div>
                </template>
            </div>

            {{-- Existing Images --}}
            @if($agenda->images->count() > 0)
                <div>
                    <h3 class="text-[10px] md:text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Foto Sebelumnya ({{ $agenda->images->count() }})</h3>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach($agenda->images as $image)
                            <div class="rounded-xl overflow-hidden border border-gray-100 shadow-sm">
                                <img src="{{ Storage::url($image->image_path) }}" alt="Dokumentasi" class="w-full aspect-square object-cover" loading="lazy">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="text-center mt-8 px-4">
            <p class="text-[10px] md:text-xs text-gray-300">D-ASSA &middot; Digital Agenda & Attendance System</p>
        </div>
    </div>
<script>
function photoUploader() {
    return {
        imagePreviews: [],
        uploading: false,

        handleFiles(event) {
            const files = event.target.files;
            for (const file of files) {
                if (file.size > 10 * 1024 * 1024) {
                    alert('Ukuran file \"' + file.name + '\" melebihi 10MB, dilewati.');
                    continue;
                }
                this.compressImage(file).then(compressed => {
                    this.imagePreviews.push({
                        url: URL.createObjectURL(compressed),
                        file: compressed
                    });
                });
            }
            event.target.value = '';
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

        async uploadAll() {
            if (!this.imagePreviews.length || this.uploading) return;
            this.uploading = true;
            const fd = new FormData();
            this.imagePreviews.forEach(p => fd.append('images[]', p.file));
            try {
                const res = await fetch('{{ route("agenda.image.store", $agenda) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: fd
                });
                if (res.ok) {
                    window.location.reload();
                } else {
                    const data = await res.json().catch(() => null);
                    alert(data?.message || 'Gagal mengunggah foto.');
                }
            } catch {
                alert('Terjadi kesalahan jaringan.');
            } finally {
                this.uploading = false;
            }
        }
    };
}
</script>
</body>

</html>
