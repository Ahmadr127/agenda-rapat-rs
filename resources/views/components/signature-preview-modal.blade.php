{{-- Reusable modal untuk preview tanda tangan (atau gambar lain).
    Cara pakai:
      1. Letakkan <x-signature-preview-modal /> di dalam halaman yang memakai Alpine.
      2. Trigger dari mana saja (mis. @click pada thumbnail):
             $dispatch('open-signature-preview', { name: 'Nama Peserta', url: 'https://.../ttd.png' })
    Modal mandiri (state sendiri), jadi tidak perlu variabel Alpine di parent. --}}
<template x-teleport="body">
    <div x-data="{ show: false, previewName: '', previewUrl: '' }"
        x-on:open-signature-preview.window="previewName = $event.detail.name ?? ''; previewUrl = $event.detail.url ?? ''; show = true"
        x-show="show" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[100] flex items-center justify-center p-4"
        @click.self="show = false" @keydown.escape.window="show = false" @contextmenu.prevent>
        <div x-show="show"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="bg-white rounded-3xl w-full max-w-md shadow-2xl overflow-hidden" @click.stop>
            <div class="px-6 pt-6 pb-4 border-b border-gray-100 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <h3 class="text-base font-bold text-gray-900">Tanda Tangan</h3>
                    <p class="text-sm text-gray-400 mt-0.5 truncate" x-text="previewName"></p>
                </div>
                <button type="button" @click="show = false"
                    class="w-8 h-8 shrink-0 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors"
                    aria-label="Tutup">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <div class="border-2 border-dashed border-gray-200 rounded-2xl p-4 bg-gray-50/50">
                    <img :src="previewUrl" :alt="'Tanda tangan ' + previewName"
                        draggable="false" @contextmenu.prevent @dragstart.prevent
                        class="w-full h-48 object-contain bg-white rounded-xl select-none pointer-events-auto"
                        style="-webkit-touch-callout: none; -webkit-user-select: none; user-select: none;">
                </div>
            </div>
            <div class="px-6 pb-6">
                <button type="button" @click="show = false"
                    class="w-full px-4 py-2.5 rounded-2xl bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200 active:scale-[0.98] transition-all duration-200">Tutup</button>
            </div>
        </div>
    </div>
</template>
