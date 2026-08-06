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
    <style>[x-cloak] { display: none !important; }
        .single-line-textarea {
            white-space: nowrap;
            overflow: hidden;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen font-sans">
    <div class="max-w-6xl mx-auto pb-8">
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
            {{-- Add Note Form --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-3 md:px-4 py-3 border-b border-gray-100">
                    <h3 class="text-[10px] md:text-xs font-bold text-[#0F766E] uppercase tracking-wider">Tambah Notulensi Baru</h3>
                </div>
                <form action="{{ route('agenda.note.store', $agenda) }}" method="POST">
                    @csrf
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-[#0F766E] to-[#0D9488]">
                            <tr class="border-b border-gray-100">
                                <th class="px-1 md:px-2 py-1 md:py-2 text-left text-[8px] md:text-[10px] font-semibold text-white uppercase tracking-wider w-5 md:w-7">No</th>
                                <th class="px-1 md:px-2 py-1 md:py-2 text-left text-[8px] md:text-[10px] font-semibold text-white uppercase tracking-wider">Topik</th>
                                <th class="px-1 md:px-2 py-1 md:py-2 text-left text-[8px] md:text-[10px] font-semibold text-white uppercase tracking-wider">Pembahasan</th>
                                <th class="px-1 md:px-2 py-1 md:py-2 text-left text-[8px] md:text-[10px] font-semibold text-white uppercase tracking-wider">Kesimpulan</th>
                                <th class="px-1 md:px-2 py-1 md:py-2 text-left text-[8px] md:text-[10px] font-semibold text-white uppercase tracking-wider">PJ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 bg-[#F8FAFC]">
                            <tr>
                                <td class="px-1 md:px-2 py-[9px] md:py-2 text-[8px] md:text-[12px] text-gray-400 align-top">{{ $agenda->notes->count() + 1 }}</td>
                                <td class="px-1 md:px-2 py-[3px] md:py-2 align-top">
                                    <textarea name="topic" id="topic" rows="1" placeholder="Topik" class="w-full border-0 p-0 text-[8px] md:text-sm bg-transparent focus:ring-0 focus:border-0 placeholder-gray-400 text-slate-700 resize-none overflow-hidden single-line-textarea" required>{{ old('topic') }}</textarea>
                                    @error('topic') <p class="text-rose-500 text-[8px] md:text-xs mt-1">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-1 md:px-2 py-[3px] md:py-2 align-top">
                                    <textarea name="decision" id="decision" rows="2" placeholder="Pembahasan" class="w-full border-0 p-0 text-[8px] md:text-sm bg-transparent focus:ring-0 focus:border-0 placeholder-gray-400 text-slate-700 resize-none" required>{{ old('decision') }}</textarea>
                                    @error('decision') <p class="text-rose-500 text-[8px] md:text-xs mt-1">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-1 md:px-2 py-[3px] md:py-2 align-top">
                                    <textarea name="remarks" id="remarks" rows="2" placeholder="Kesimpulan" class="w-full border-0 p-0 text-[8px] md:text-sm bg-transparent focus:ring-0 focus:border-0 placeholder-gray-400 text-slate-700 resize-none">{{ old('remarks') }}</textarea>
                                    @error('remarks') <p class="text-rose-500 text-[8px] md:text-xs mt-1">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-1 md:px-2 py-[3px] md:py-2 align-top">
                                    <textarea name="pj" id="pj" rows="1" placeholder="Nama PJ" class="w-full border-0 p-0 text-[8px] md:text-sm bg-transparent focus:ring-0 focus:border-0 placeholder-gray-400 text-slate-700 resize-none overflow-hidden single-line-textarea" required>{{ old('pj') }}</textarea>
                                    @error('pj') <p class="text-rose-500 text-[8px] md:text-xs mt-1">{{ $message }}</p> @enderror
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="px-2 py-1 md:px-4 md:py-3 border-t border-gray-100 bg-gray-50">
                        <button type="submit" class="w-full px-3 py-1.5 md:px-4 md:py-2.5 rounded-xl bg-[#0D9488] text-white text-[10px] md:text-sm font-semibold shadow-md hover:bg-[#0F766E] active:scale-[0.98] transition-all">
                            Simpan Notulensi
                        </button>
                    </div>
                </form>
            </div>

            {{-- Existing Notes --}}
            @if($agenda->notes->count() > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden pb-0.5">
                    <div class="px-3 md:px-4 py-3 border-b border-gray-100">
                        <h3 class="text-[10px] md:text-xs font-bold text-[#0F766E] uppercase tracking-wider">Catatan Sebelumnya ({{ $agenda->notes->count() }})</h3>
                    </div>
                    <table class="w-full table-fixed">
                        <thead class="bg-gradient-to-r from-[#0F766E] to-[#0D9488]">
                            <tr>
                                <th class="px-1 md:px-2 py-1 md:py-2 text-left text-[8px] md:text-[10px] font-semibold text-white uppercase tracking-wider w-5 md:w-7">No</th>
                                <th class="px-1 md:px-2 py-1 md:py-2 text-left text-[8px] md:text-[10px] font-semibold text-white uppercase tracking-wider">Topik</th>
                                <th class="px-1 md:px-2 py-1 md:py-2 text-left text-[8px] md:text-[10px] font-semibold text-white uppercase tracking-wider">Pembahasan</th>
                                <th class="px-1 md:px-2 py-1 md:py-2 text-left text-[8px] md:text-[10px] font-semibold text-white uppercase tracking-wider">Kesimpulan</th>
                                <th class="px-1 md:px-2 py-1 md:py-2 text-left text-[8px] md:text-[10px] font-semibold text-white uppercase tracking-wider">PJ</th>
                                <th class="px-1 md:px-2 py-1 md:py-2 text-left text-[8px] md:text-[10px] font-semibold text-white uppercase tracking-wider w-10 md:w-16">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($agenda->notes->sortByDesc('created_at') as $index => $note)
                                <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-teal-50 transition-colors">
                                    <td class="px-1 md:px-2 py-[3px] md:py-2 text-[8px] md:text-[10px] text-gray-400 align-top">{{ $index + 1 }}</td>
                                    <td class="px-1 md:px-2 py-[3px] md:py-2 text-[8px] md:text-[10px] font-semibold text-gray-900 align-top break-words">{{ $note->topic }}</td>
                                    <td class="px-1 md:px-2 py-[3px] md:py-2 text-[8px] md:text-[10px] text-gray-600 align-top break-words">{{ $note->decision }}</td>
                                    <td class="px-1 md:px-2 py-[3px] md:py-2 text-[8px] md:text-[10px] text-gray-600 align-top break-words">{{ $note->remarks ?? '—' }}</td>
                                    <td class="px-1 md:px-2 py-[3px] md:py-2 text-[8px] md:text-[10px] text-gray-600 align-top break-words">{{ $note->pj ?? '—' }}</td>
                                    <td class="px-1 md:px-2 py-[3px] md:py-2 text-[8px] md:text-[10px] text-gray-400 align-top">{{ $note->created_at->format('H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="text-center mt-8 px-4">
            <p class="text-[10px] md:text-xs text-gray-300">D-ASSA &middot; Digital Agenda & Attendance System</p>
        </div>
    </div>
</body>

</html>
