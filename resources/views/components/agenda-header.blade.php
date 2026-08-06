@props(['agenda'])

<div class="bg-gradient-to-br from-primary to-primary-700 text-white px-6 pt-8 pb-5 rounded-b-3xl shadow-lg">
    <div class="flex items-center justify-between mb-3">
        <a href="{{ route('home') }}"
            class="inline-flex items-center gap-1.5 text-primary-100/80 text-[10px] md:text-xs font-medium hover:text-white transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali
        </a>
        {{ $actions ?? '' }}
    </div>
    <h1 class="text-sm md:text-lg font-bold leading-snug">{{ $agenda->title }}</h1>
    <table class="w-full mt-3">
        <tbody>
            <tr class="text-[10px] md:text-[11px]">
                <td class="text-white/60 font-medium py-0.5 pr-3 whitespace-nowrap align-top">Tanggal</td>
                <td class="text-white/60 py-0.5 pr-1 align-top">:</td>
                <td class="text-white font-semibold py-0.5 break-words">{{ $agenda->event_date->translatedFormat('d M Y') }}</td>
            </tr>
            <tr class="text-[10px] md:text-[11px]">
                <td class="text-white/60 font-medium py-0.5 pr-3 whitespace-nowrap align-top">Waktu</td>
                <td class="text-white/60 py-0.5 pr-1 align-top">:</td>
                <td class="text-white font-semibold py-0.5 break-words">{{ \Carbon\Carbon::parse($agenda->event_time)->format('H:i') }}@if($agenda->event_end_time) – {{ \Carbon\Carbon::parse($agenda->event_end_time)->format('H:i') }}@endif WIB</td>
            </tr>
            <tr class="text-[10px] md:text-[11px]">
                <td class="text-white/60 font-medium py-0.5 pr-3 whitespace-nowrap align-top">Ruangan</td>
                <td class="text-white/60 py-0.5 pr-1 align-top">:</td>
                <td class="text-white font-semibold py-0.5 break-words">{{ $agenda->room->room_name ?? '-' }}</td>
            </tr>
            <tr class="text-[10px] md:text-[11px]">
                <td class="text-white/60 font-medium py-0.5 pr-3 whitespace-nowrap align-top">Penyelenggara</td>
                <td class="text-white/60 py-0.5 pr-1 align-top">:</td>
                <td class="text-white font-semibold py-0.5 break-words">{{ $agenda->unit?->name ?? '-' }}</td>
            </tr>
            <tr class="text-[10px] md:text-[11px]">
                <td class="text-white/60 font-medium py-0.5 pr-3 whitespace-nowrap align-top">Pimpinan {{$agenda->type}}</td>
                <td class="text-white/60 py-0.5 pr-1 align-top">:</td>
                <td class="text-white font-semibold py-0.5 break-words">{{ $agenda->eventLeader?->full_name ?? '-' }}</td>
            </tr>
            <tr class="text-[10px] md:text-[11px]">
                <td class="text-white/60 font-medium py-0.5 pr-3 whitespace-nowrap align-top">Deskripsi</td>
                <td class="text-white/60 py-0.5 pr-1 align-top">:</td>
                <td class="text-white font-semibold py-0.5 break-words whitespace-pre-line">{{ $agenda->description ?? '-' }}</td>
            </tr>
        </tbody>
    </table>
</div>
