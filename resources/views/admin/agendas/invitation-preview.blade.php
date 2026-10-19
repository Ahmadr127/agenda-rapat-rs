<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.agendas.show', $agenda) }}" class="text-gray-400 hover:text-primary transition-colors">Detail Agenda</a>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            <span class="font-semibold text-gray-700">Pratinjau Surat Undangan</span>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-4">
        <div class="flex items-center justify-end gap-2">
            <a href="{{ route('admin.agendas.invitation-letter', ['agenda' => $agenda->id, 'download' => 1]) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-rose-500 text-white text-sm font-bold shadow-md hover:bg-rose-600 transition-all">
                Unduh PDF
            </a>
        </div>

        {{-- Lembaran kertas asli: sudut tajam tanpa border radius + bayangan kertas --}}
        <div class="bg-white border border-gray-200 shadow-lg overflow-hidden">
            @if(! empty($headerDataUri))
                <img src="{{ $headerDataUri }}" alt="Kop RS AZRA" class="w-full">
            @endif
            <div class="px-8 md:px-12 py-8 text-[15px] leading-7 text-gray-900">
                @include('admin.agendas.partials._invitation-letter-body')
            </div>
            @if(! empty($footerDataUri))
                <img src="{{ $footerDataUri }}" alt="Footer RS AZRA" class="w-full">
            @endif
        </div>
    </div>

    <style>
        .letter-body p { margin: 0 0 5px 0; }
        .letter-place-date { text-align: right; margin: 0 0 12px 0; }
        table.letter-meta { width: 100%; border-collapse: collapse; margin: 5px 0; }
        .letter-meta td { vertical-align: top; padding: 2px 3px; }
        .meta-label { width: 150px; }
        .meta-sep { width: 14px; }
        table.letter-schedule { margin: 8px 0 8px 22px; }
        .mt { margin-top: 6px; }
        {{-- .mt-sm/.recipient/.yth polos tertimpa shorthand .letter-body p, makanya
             memakai selector bercabang agar aturan di bawah benar-benar aktif. --}}
        .letter-body p.yth { margin-bottom: 0; }
        .letter-body p.mt-sm { margin-top: 15px;}
        .letter-body p.recipient { margin: 0; line-height: 1.2; }
        .justify { text-align: justify; }
        table.letter-sign { width: 100%; margin-top: 20px; border-collapse: collapse; }
        .sign-spacer { width: 50%; }
        .sign-block { text-align: center; }
        .sign-block p { margin: 0 0 5px 0; }
        .sign-image { display: block; height: 90px; max-width: 240px; margin: 4px auto; }
        .sign-empty { height: 90px; }
        .sign-name { margin-top: 4px; }
        .tembusan-item { margin-left: 22px; }
    </style>
</x-app-layout>
