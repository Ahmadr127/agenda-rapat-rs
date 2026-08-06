@props([
    'action',
    'q' => '',
    'placeholder' => 'Cari...',
])

<form method="GET" action="{{ $action }}" class="flex flex-col sm:flex-row gap-3">
    <div class="flex-1">
        <input
            type="text"
            name="q"
            value="{{ $q }}"
            placeholder="{{ $placeholder }}"
            class="block w-full px-4 py-3 rounded-2xl border border-gray-200 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none"
        >
    </div>

    {{ $filters ?? '' }}

    <div class="flex gap-2 shrink-0">
        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-md shadow-primary/20 hover:bg-primary-700 hover:shadow-lg active:scale-[0.98] transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            Cari
        </button>
        <a href="{{ $action }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-gray-100 text-gray-600 text-sm font-bold hover:bg-gray-200 active:scale-[0.98] transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            Reset
        </a>
    </div>
</form>
