<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.bank-soals.index') }}" class="text-gray-400 hover:text-primary transition-colors">Bank Soal</a>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            <span class="font-semibold text-gray-700">Ubah Bank Soal</span>
        </div>
    </x-slot>

    @php
        $existingQuestions = old('questions', $bankSoal->questions->map(fn($q) => [
            'question_text' => $q->question_text,
            'option_a' => $q->option_a,
            'option_b' => $q->option_b,
            'option_c' => $q->option_c,
            'option_d' => $q->option_d,
            'option_e' => $q->option_e,
            'correct_option' => $q->correct_option,
        ])->toArray());
    @endphp

    @include('admin.bank-soals._form', [
        'action' => route('admin.bank-soals.update', $bankSoal),
        'method' => 'PUT',
        'submitLabel' => 'Perbarui',
        'titleValue' => old('title', $bankSoal->title),
        'descriptionValue' => old('description', $bankSoal->description),
        'initialQuestions' => $existingQuestions,
        'infoSubtitle' => 'Ubah judul dan deskripsi bank soal',
        'isCreate' => false,
    ])
</x-app-layout>
