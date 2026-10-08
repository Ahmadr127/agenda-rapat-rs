@props([
    'paginator',
    'perPage' => 10,
    'perPageOptions' => [10, 20, 50, 100],
    'rounded' => 'rounded-3xl',
    'stickyHeader' => false,
    'maxHeight' => '65vh',
])

@php
    $isCursor = $paginator instanceof \Illuminate\Pagination\CursorPaginator;
    $hasPages = $paginator->hasPages();
    $firstItem = !$isCursor ? $paginator->firstItem() : null;
    $lastItem = !$isCursor ? $paginator->lastItem() : null;
    $total = !$isCursor ? $paginator->total() : null;
    $count = $paginator->count();
@endphp

@if($stickyHeader)
@once
<style>
    /* Scrollbar tipis + track transparan agar radius sudut header tidak tertutup kotak */
    .dt-sticky {
        scrollbar-width: thin;
        scrollbar-color: rgba(0, 0, 0, .35) transparent;
    }
    .dt-sticky::-webkit-scrollbar { width: 10px; height: 10px; }
    .dt-sticky::-webkit-scrollbar-track { background: transparent; }
    .dt-sticky::-webkit-scrollbar-thumb { background: rgba(0, 0, 0, .3); border-radius: 999px; }
    .dt-sticky::-webkit-scrollbar-thumb:hover { background: rgba(0, 0, 0, .5); }
    .dt-sticky::-webkit-scrollbar-corner { background: transparent; }
</style>
@endonce
@endif

<div class="bg-white {{ $rounded }} border border-gray-100 overflow-hidden">
    <div class="{{ $stickyHeader ? 'overflow-auto dt-sticky' : 'overflow-x-auto' }}" @if($stickyHeader) style="max-height: {{ $maxHeight }}; background: linear-gradient(to bottom, #005c5a 0, #005c5a 40px, #ffffff 40px, #ffffff 100%);" @endif>
        <table class="w-full {{ $stickyHeader ? '[&_thead_th]:sticky [&_thead_th]:top-0 [&_thead_th]:z-10 [&_thead_th]:bg-primary-700' : '' }}">
            <thead class="bg-primary-700 text-white {{ $stickyHeader ? 'sticky top-0 z-10 shadow-sm' : '' }}">
                <tr class="border-b border-gray-100">
                    {{ $header }}
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t border-gray-100 flex flex-col lg:flex-row lg:items-center gap-4 {{ $hasPages ? 'lg:justify-between' : 'lg:justify-start' }}">
        {{-- Info + per page selector --}}
        <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500">
            @if(!$isCursor && $total !== null && $total > 0)
                <span>
                    Menampilkan
                    <span class="font-bold text-gray-800">{{ $firstItem }}–{{ $lastItem }}</span>
                    dari
                    <span class="font-bold text-gray-800">{{ number_format($total) }}</span>
                    data
                </span>
                <span class="text-gray-200">|</span>
            @elseif($count > 0)
                <span>
                    <span class="font-bold text-gray-800">{{ $count }}</span> data ditampilkan
                </span>
                <span class="text-gray-200">|</span>
            @endif

            <form method="GET" action="{{ url()->current() }}" class="inline-flex items-center gap-2">
                {{-- Pertahankan semua filter/search aktif, kecuali kunci paging --}}
                @foreach(request()->except(['page', 'per_page', 'agenda_cursor']) as $key => $value)
                    @if(is_array($value))
                        @foreach($value as $v)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                        @endforeach
                    @elseif($value !== null && $value !== '')
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <span class="whitespace-nowrap">Tampilkan</span>
                <select
                    name="per_page"
                    onchange="this.form.requestSubmit()"
                    class="rounded-xl border-gray-200 text-sm font-semibold text-gray-700 focus:border-primary focus:ring-primary py-1.5 pl-3 pr-8 cursor-pointer"
                >
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}" @selected((int) $perPage === (int) $option)>{{ $option }}</option>
                    @endforeach
                </select>
                <span class="whitespace-nowrap">per halaman</span>
            </form>
        </div>

        {{-- Pagination links --}}
        @if($hasPages)
            <div class="[&_nav]:justify-end">
                {{ $paginator->links() }}
            </div>
        @endif
    </div>
</div>
