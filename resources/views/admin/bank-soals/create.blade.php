<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.bank-soals.index') }}" class="text-gray-400 hover:text-primary transition-colors">Bank Soal</a>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            <span class="font-semibold text-gray-700">Tambah Bank Soal</span>
        </div>
    </x-slot>

    @include('admin.bank-soals._form', [
        'action' => route('admin.bank-soals.store'),
        'method' => 'POST',
        'submitLabel' => 'Simpan',
        'titleValue' => old('title'),
        'descriptionValue' => old('description'),
        'initialQuestions' => old('questions', []),
        'infoSubtitle' => 'Lengkapi judul dan deskripsi bank soal',
        'isCreate' => true,
    ])
</x-app-layout>
