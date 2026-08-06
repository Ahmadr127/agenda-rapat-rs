<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.bank-soals.index') }}" class="text-gray-400 hover:text-primary transition-colors">Bank Soal</a>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            <span class="font-semibold text-gray-700">Detail Bank Soal</span>
        </div>
    </x-slot>

    <div class="max-w-4xl space-y-6">
        {{-- Header Card --}}
        <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ $bankSoal->title }}</h3>
                    @if($bankSoal->description)
                        <p class="text-sm text-gray-500 mt-1">{{ $bankSoal->description }}</p>
                    @endif
                    <div class="flex items-center gap-3 mt-3">
                        <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-semibold bg-primary-50 text-primary">{{ $bankSoal->questions->count() }} soal</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.bank-soals.edit', $bankSoal) }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-md shadow-primary/20 hover:bg-primary-700 hover:shadow-lg active:scale-[0.98] transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                        Ubah
                    </a>
                    <form action="{{ route('admin.bank-soals.destroy', $bankSoal) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus bank soal ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-2xl bg-rose-50 text-rose-600 text-sm font-bold hover:bg-rose-100 active:scale-[0.98] transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Questions List --}}
        @foreach($bankSoal->questions as $index => $question)
            <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100">
                    <h4 class="text-sm font-bold text-gray-700">Soal {{ $index + 1 }}</h4>
                </div>
                <div class="p-8">
                    <p class="text-sm text-gray-800 font-medium mb-4">{{ $question->question_text }}</p>
                    <div class="space-y-2">
                        @foreach(['a', 'b', 'c', 'd', 'e'] as $opt)
                            <div class="flex items-center gap-3 px-4 py-2.5 rounded-xl {{ $question->correct_option === $opt ? 'bg-primary-50 border border-primary/20' : 'bg-gray-50' }}">
                                <span class="w-7 h-7 rounded-lg {{ $question->correct_option === $opt ? 'bg-primary text-white' : 'bg-white text-gray-500 border border-gray-200' }} flex items-center justify-center text-xs font-bold uppercase">{{ $opt }}</span>
                                <span class="text-sm {{ $question->correct_option === $opt ? 'text-primary font-semibold' : 'text-gray-600' }}">{{ $question->{'option_' . $opt} }}</span>
                                @if($question->correct_option === $opt)
                                    <svg class="w-4 h-4 text-primary ml-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
