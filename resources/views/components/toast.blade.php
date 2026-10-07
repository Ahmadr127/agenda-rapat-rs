@props([
    'timeout' => 5000,
])

@php
    $successMessage = session('success');
    $errorMessage = session('error');
    $validationErrors = $errors->any() ? $errors->all() : [];
    $hasToast = (bool) ($successMessage || $errorMessage || count($validationErrors));
@endphp

@if($hasToast)
<div
    x-data="{
        show: true,
        timeout: @js($timeout),
        progress: 100,
        timer: null,
        tick: null,
        start() {
            this.clear();
            const step = 100 / (this.timeout / 100);
            this.tick = setInterval(() => {
                this.progress -= step;
                if (this.progress <= 0) this.dismiss();
            }, 100);
            this.timer = setTimeout(() => this.dismiss(), this.timeout);
        },
        clear() {
            if (this.timer) clearTimeout(this.timer);
            if (this.tick) clearInterval(this.tick);
        },
        dismiss() {
            this.clear();
            this.show = false;
        },
    }"
    x-init="start()"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-x-6"
    x-transition:enter-end="opacity-100 translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-x-0"
    x-transition:leave-end="opacity-0 translate-x-6"
    @mouseenter="clear()"
    @mouseleave="start()"
    class="fixed top-20 right-4 sm:right-6 z-50 w-[calc(100vw-2rem)] max-w-sm space-y-3"
    role="status"
    aria-live="polite"
>
    @if($successMessage)
        <div class="overflow-hidden rounded-xl bg-white border border-emerald-200 shadow-lg shadow-emerald-900/5">
            <div class="flex items-start gap-3 px-4 py-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-gray-900">Berhasil</p>
                    <p class="text-sm text-gray-600 leading-snug">{{ $successMessage }}</p>
                </div>
                <button type="button" @click="dismiss()" aria-label="Tutup notifikasi" class="p-1 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="h-1 bg-emerald-100">
                <div class="h-full bg-emerald-500 transition-all duration-100" :style="'width: ' + progress + '%'"></div>
            </div>
        </div>
    @endif

    @if($errorMessage || count($validationErrors))
        <div class="overflow-hidden rounded-xl bg-white border border-rose-200 shadow-lg shadow-rose-900/5">
            <div class="flex items-start gap-3 px-4 py-3">
                <div class="w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-gray-900">Terjadi kesalahan</p>
                    @if($errorMessage)
                        <p class="text-sm text-gray-600 leading-snug">{{ $errorMessage }}</p>
                    @endif
                    @if(count($validationErrors))
                        <ul class="mt-1 space-y-0.5 text-sm text-gray-600 leading-snug list-disc list-inside">
                            @foreach($validationErrors as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <button type="button" @click="dismiss()" aria-label="Tutup notifikasi" class="p-1 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="h-1 bg-rose-100">
                <div class="h-full bg-rose-500 transition-all duration-100" :style="'width: ' + progress + '%'"></div>
            </div>
        </div>
    @endif
</div>
@endif
