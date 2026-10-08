@php
    $presenterSelections = old('presenter_ids', isset($agenda->presenters) ? $agenda->presenters->pluck('id')->values()->all() : []);
    $presenterSelections = array_values(array_filter((array) $presenterSelections, fn ($id) => $id !== null && $id !== ''));
    $presenterLabels = old('presenter_ids') ? [] : (isset($agenda->presenters) ? $agenda->presenters->pluck('full_name')->values()->all() : []);
    $initialPresenterCount = count($presenterSelections);
    $maxPresenters = 10;
    $allowedTypes = $allowedTypes ?? [];
    $soleType = count($allowedTypes) === 1 ? $allowedTypes[0]['id'] : null;
    $soleTypeLabel = count($allowedTypes) === 1 ? $allowedTypes[0]['name'] : null;
    // Default dinamis: old() dulu, lalu tipe agenda saat ini bila masih diizinkan,
    // lalu tipe satu-satunya / opsi pertama agar satu radio selalu terpilih.
    $allowedTypeIds = collect($allowedTypes)->pluck('id')->all();
    $candidateType = old('type', $soleType ?? $agenda->type);
    $defaultType = in_array($candidateType, $allowedTypeIds, true)
        ? $candidateType
        : ($allowedTypeIds[0] ?? $candidateType);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.agendas.index') }}" class="text-gray-400 hover:text-primary transition-colors">Agenda</a>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            <span class="font-semibold text-gray-700">Ubah Agenda</span>
        </div>
    </x-slot>

    <div>
        <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Ubah Agenda</h3>
                <p class="text-sm text-gray-400 mt-0.5">Pilih tipe agenda di bagian atas lalu perbarui seluruh detail yang relevan.</p>
            </div>
            <div class="p-8" x-data="{ type: '{{ $defaultType }}', presenterCount: {{ $initialPresenterCount }} }">
                <form action="{{ route('admin.agendas.update', $agenda) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    @method('PUT')

                    @if($soleType)
                        {{-- Hanya satu izin tipe: otomatis terpilih, selector disembunyikan --}}
                        <input type="hidden" name="type" value="{{ $soleType }}">
                        <div class="pb-4 border-b border-gray-100">
                            <div class="flex items-center gap-2 rounded-2xl bg-primary/5 border border-primary/20 px-4 py-3">
                                <svg class="w-4 h-4 text-primary flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.75 6.75A2.25 2.25 0 017 4.5h10a2.25 2.25 0 012.25 2.25v10A2.25 2.25 0 0117 19H7a2.25 2.25 0 01-2.25-2.25v-10z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9h7.5M8.25 12h7.5M8.25 15h4.5"/></svg>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">Tipe Agenda: <span class="text-primary">{{ $soleTypeLabel }}</span></p>
                                    <p class="text-xs text-gray-400">Hak akses Anda hanya mencakup tipe {{ $soleTypeLabel }} sehingga otomatis terpilih.</p>
                                </div>
                            </div>
                        </div>
                    @elseif(count($allowedTypes) > 1)
                        {{-- Pilihan tipe dibatasi permission milik user --}}
                        <div class="pb-4 border-b border-gray-100">
                            <h4 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.75 6.75A2.25 2.25 0 017 4.5h10a2.25 2.25 0 012.25 2.25v10A2.25 2.25 0 0117 19H7a2.25 2.25 0 01-2.25-2.25v-10z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9h7.5M8.25 12h7.5M8.25 15h4.5"/></svg>
                                Tipe Agenda
                            </h4>

                            <div class="flex gap-4">
                                @foreach($allowedTypes as $allowedType)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="type" value="{{ $allowedType['id'] }}" x-model="type" @checked($defaultType === $allowedType['id']) class="w-4 h-4 text-primary border-gray-300 focus:ring-primary" @if($loop->first) required @endif>
                                        <span class="text-sm font-medium text-gray-700">{{ $allowedType['name'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-400 mt-2">Mengubah tipe agenda akan menyesuaikan field yang ditampilkan di bawah.</p>
                            @error('type') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                        </div>
                    @else
                        {{-- User tidak memegang izin tipe apa pun --}}
                        <div class="pb-4 border-b border-gray-100">
                            <div class="flex items-center gap-2 rounded-2xl bg-rose-50 border border-rose-200 px-4 py-3">
                                <div>
                                    <p class="text-sm font-semibold text-rose-600">Tidak ada tipe agenda yang dapat dipilih</p>
                                    <p class="text-xs text-gray-400">Akun Anda tidak memiliki izin tipe agenda. Hubungi administrator.</p>
                                </div>
                            </div>
                            @error('type') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div x-show="type" x-transition.opacity class="space-y-5" x-cloak>
                        <div class="pb-4 border-b border-gray-100">
                            <h4 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                <span x-text="'Informasi ' + (type === 'diklat' ? 'Diklat' : (type === 'pelatihan' ? 'Pelatihan' : 'Rapat'))">Informasi Agenda</span>
                            </h4>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="title" class="block text-sm font-semibold text-gray-700 mb-2"><span x-text="'Judul ' + (type === 'diklat' ? 'Diklat' : (type === 'pelatihan' ? 'Pelatihan' : 'Rapat'))">Judul Agenda</span></label>
                                    <input type="text" name="title" id="title" value="{{ old('title', $agenda->title) }}" placeholder="Judul agenda" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" required>
                                    @error('title') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="event_date" class="block text-sm font-semibold text-gray-700 mb-2"><span x-text="'Tanggal ' + (type === 'diklat' ? 'Diklat' : (type === 'pelatihan' ? 'Pelatihan' : 'Rapat'))">Tanggal Acara</span></label>
                                    <input type="date" name="event_date" id="event_date" value="{{ old('event_date', $agenda->event_date->format('Y-m-d')) }}" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" required>
                                    @error('event_date') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="room_id" class="block text-sm font-semibold text-gray-700 mb-2">Ruangan</label>
                                    <x-searchable-select
                                        name="room_id"
                                        search-url="{{ route('admin.rooms.search') }}"
                                        :selected-id="old('room_id', $agenda->room_id)"
                                        :selected-label="old('room_id') ? null : $agenda->room?->room_name"
                                        placeholder="Cari ruangan..."
                                        required
                                    />
                                    @error('room_id') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="event_time" class="block text-sm font-semibold text-gray-700 mb-2">Pukul Mulai</label>
                                    <input type="time" name="event_time" id="event_time" value="{{ old('event_time', $agenda->event_time ? \Carbon\Carbon::parse($agenda->event_time)->format('H:i') : '') }}" lang="id" step="60" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" required>
                                    @error('event_time') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                </div>

                                <template x-if="type === 'diklat' || type === 'pelatihan'">
                                    <div>
                                        <label for="event_end_time" class="block text-sm font-semibold text-gray-700 mb-2">Pukul Selesai</label>
                                        <input type="time" name="event_end_time" id="event_end_time" value="{{ old('event_end_time', $agenda->event_end_time ? \Carbon\Carbon::parse($agenda->event_end_time)->format('H:i') : '') }}" lang="id" step="60" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none">
                                        <p class="text-xs text-gray-400 mt-1">Digunakan untuk diklat dan pelatihan (format 24 jam) dan harus setelah pukul mulai.</p>
                                        @error('event_end_time') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                    </div>
                                </template>

                                <template x-if="type === 'rapat'">
                                    <div>
                                        <label for="event_end_time" class="block text-sm font-semibold text-gray-700 mb-2">Pukul Selesai</label>
                                        <input type="time" name="event_end_time" id="event_end_time" value="{{ old('event_end_time', $agenda->event_end_time ? \Carbon\Carbon::parse($agenda->event_end_time)->format('H:i') : '') }}" lang="id" step="60" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none">
                                        <p class="text-xs text-gray-400 mt-1">Opsional untuk agenda rapat, tetapi bila diisi harus setelah pukul mulai.</p>
                                        @error('event_end_time') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                    </div>
                                </template>

                                @if($canChooseUnit ?? false)
                                    <div>
                                        <label for="unit_id" class="block text-sm font-semibold text-gray-700 mb-2">Unit Penyelenggara</label>
                                        <x-searchable-select
                                            name="unit_id"
                                            search-url="{{ route('admin.units.search') }}"
                                            :selected-id="old('unit_id', $agenda->unit_id)"
                                            :selected-label="old('unit_id') ? null : $agenda->unit?->name"
                                            placeholder="Cari unit..."
                                            required
                                        />
                                        <p class="text-xs text-gray-400 mt-1">Anda memiliki izin penuh sehingga dapat memilih unit mana pun.</p>
                                        @error('unit_id') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                    </div>
                                @else
                                    <input type="hidden" name="unit_id" value="{{ old('unit_id', $userUnit?->id) }}">
                                @endif

                                <div>
                                    <label for="event_leader_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span x-text="'Pimpinan ' + (type === 'diklat' ? 'Diklat' : (type === 'pelatihan' ? 'Pelatihan' : 'Rapat'))"></span>
                                    </label>
                                    <x-searchable-select
                                        name="event_leader_id"
                                        search-url="{{ route('admin.employees.search') }}"
                                        :selected-id="old('event_leader_id', $agenda->event_leader_id)"
                                        :selected-label="old('event_leader_id') ? null : $agenda->eventLeader?->full_name"
                                        placeholder="Cari pegawai..."
                                        required
                                    />
                                    @error('event_leader_id') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
                                    <textarea name="description" id="description" rows="3" placeholder="Deskripsikan agenda..." class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none">{{ old('description', $agenda->description) }}</textarea>
                                    @error('description') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div x-show="type === 'diklat' || type === 'pelatihan'" x-transition class="pb-4 border-b border-gray-100">
                            <h4 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                                Detail Diklat/Pelatihan
                            </h4>

                            <div class="space-y-4">
                                <div>
                                    <div class="flex items-center justify-between gap-3 mb-2">
                                        <label class="block text-sm font-semibold text-gray-700">Pemateri</label>
                                        <div class="flex items-center gap-2">
                                            <button type="button" class="px-3 py-2 rounded-xl bg-gray-100 text-gray-600 text-xs font-semibold hover:bg-gray-200 transition-colors" @click="if (presenterCount > 0) presenterCount--">Kurangi</button>
                                            <button type="button" class="px-3 py-2 rounded-xl bg-primary/10 text-primary text-xs font-semibold hover:bg-primary/20 transition-colors" @click="if (presenterCount < {{ $maxPresenters }}) presenterCount++">Tambah Pemateri</button>
                                        </div>
                                    </div>

                                    <p class="text-xs text-gray-400 mb-3">Opsional. Pilih satu atau lebih pemateri dari data karyawan.</p>

                                    <div class="space-y-3">
                                        @for ($i = 0; $i < $maxPresenters; $i++)
                                            <template x-if="presenterCount > {{ $i }}">
                                                <div>
                                                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Pemateri {{ $i + 1 }}</label>
                                                    <x-searchable-select
                                                        name="presenter_ids[]"
                                                        search-url="{{ route('admin.employees.search') }}"
                                                        :selected-id="$presenterSelections[$i] ?? null"
                                                        :selected-label="old('presenter_ids') ? null : ($presenterLabels[$i] ?? null)"
                                                        placeholder="Cari pegawai..."
                                                    />
                                                </div>
                                            </template>
                                        @endfor
                                    </div>

                                    @error('presenter_ids') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                    @error('presenter_ids.*') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="bank_soal_id" class="block text-sm font-semibold text-gray-700 mb-2">Template Bank Soal</label>
                                    <x-searchable-select
                                        name="bank_soal_id"
                                        search-url="{{ route('admin.bank-soals.search') }}"
                                        :selected-id="old('bank_soal_id', $agenda->bank_soal_id)"
                                        :selected-label="old('bank_soal_id') ? null : $agenda->bankSoal?->title"
                                        placeholder="Cari template bank soal..."
                                    />
                                    <p class="text-xs text-gray-400 mt-1">Opsional. Soal akan disalin ulang dari template yang dipilih.</p>
                                    @error('bank_soal_id') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div x-show="type === 'rapat'" x-transition class="pb-4 border-b border-gray-100">
                            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50/70 px-4 py-4">
                                <p class="text-sm font-semibold text-gray-700">Catatan Tipe Rapat</p>
                                <p class="text-xs text-gray-500 mt-1">Tipe rapat tidak menampilkan pemateri, template bank soal, atau pukul selesai. Notulensi dikelola setelah agenda berjalan.</p>
                            </div>
                        </div>

                        <div class="pb-4 border-b border-gray-100">
                            <h4 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                <span x-text="'Berkas ' + (type === 'diklat' ? 'Diklat' : (type === 'pelatihan' ? 'Pelatihan' : 'Rapat'))"></span>
                            </h4>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="letter_file" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <span class="pr-3"> Surat Undangan </span>
                                    @if($agenda->letter_file_path)
                                        <a href="{{ Storage::url($agenda->letter_file_path) }}" class="text-xs mb-2 text-blue-500 hover:underline visited:text-purple-500">Lihat file saat ini</a>
                                    @endif
                                    </label>
                                    <input type="file" name="letter_file" id="letter_file" accept=".pdf" onchange="validateAgendaFile(this, 'letter_file_error')" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none file:mr-4 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                                    <div class="flex items-center justify-between gap-2 mt-1">
                                        <p class="text-xs text-gray-400">Format PDF, maksimal 2MB.</p>
                                        <button type="button" onclick="clearAgendaFile('letter_file', 'letter_file_error')" class="text-xs font-semibold text-rose-500 hover:text-rose-600 hover:underline flex-shrink-0">Hapus lampiran</button>
                                    </div>
                                    @if($agenda->letter_file_path)
                                        <label class="mt-2 inline-flex items-center gap-2 text-xs font-semibold text-rose-600 cursor-pointer">
                                            <input type="checkbox" name="remove_letter_file" value="1" @checked(old('remove_letter_file')) class="w-4 h-4 rounded text-rose-600 border-gray-300 focus:ring-rose-500">
                                            Hapus file tersimpan saat disimpan
                                        </label>
                                    @endif
                                    <p id="letter_file_error" class="hidden text-rose-500 text-xs font-medium mt-1.5"></p>
                                    @error('letter_file') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="material_file" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span class="pr-3" x-text="'Materi ' + (type === 'diklat' ? 'Diklat' : (type === 'pelatihan' ? 'Pelatihan' : 'Rapat'))"></span>
                                        @if($agenda->material_file_path)
                                            <a href="{{ Storage::url($agenda->material_file_path) }}" class="text-xs mb-2 text-blue-500 hover:underline">Lihat file saat ini</a>
                                        @endif
                                    </label>
                                    <input type="file" name="material_file" id="material_file" accept=".pdf" onchange="validateAgendaFile(this, 'material_file_error')" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none file:mr-4 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                                    <div class="flex items-center justify-between gap-2 mt-1">
                                        <p class="text-xs text-gray-400">Format PDF, maksimal 2MB.</p>
                                        <button type="button" onclick="clearAgendaFile('material_file', 'material_file_error')" class="text-xs font-semibold text-rose-500 hover:text-rose-600 hover:underline flex-shrink-0">Hapus lampiran</button>
                                    </div>
                                    @if($agenda->material_file_path)
                                        <label class="mt-2 inline-flex items-center gap-2 text-xs font-semibold text-rose-600 cursor-pointer">
                                            <input type="checkbox" name="remove_material_file" value="1" @checked(old('remove_material_file')) class="w-4 h-4 rounded text-rose-600 border-gray-300 focus:ring-rose-500">
                                            Hapus file tersimpan saat disimpan
                                        </label>
                                    @endif
                                    <p id="material_file_error" class="hidden text-rose-500 text-xs font-medium mt-1.5"></p>
                                    @error('material_file') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <script>
                                function validateAgendaFile(input, errorId) {
                                    var MAX_BYTES = 2 * 1024 * 1024;
                                    var err = document.getElementById(errorId);
                                    var file = input.files && input.files[0];
                                    if (!file) {
                                        err.classList.add('hidden');
                                        return;
                                    }
                                    if (file.size > MAX_BYTES) {
                                        var mb = (file.size / 1048576).toFixed(2);
                                        err.textContent = 'File terlalu besar (' + mb + ' MB). Maksimal 2 MB — pilih file yang lebih kecil.';
                                        err.classList.remove('hidden');
                                        input.value = '';
                                    } else {
                                        err.textContent = '';
                                        err.classList.add('hidden');
                                    }
                                }
                                function clearAgendaFile(inputId, errorId) {
                                    var input = document.getElementById(inputId);
                                    if (input) input.value = '';
                                    var err = document.getElementById(errorId);
                                    if (err) {
                                        err.textContent = '';
                                        err.classList.add('hidden');
                                    }
                                }
                            </script>
                            <script>
                                // Pukul selesai tidak boleh mundur dari pukul mulai (semua tipe agenda).
                                (function () {
                                    function syncEndTimeMin() {
                                        var start = document.getElementById('event_time');
                                        var end = document.getElementById('event_end_time');
                                        if (start && end && start.value) end.min = start.value;
                                    }
                                    function validateEndTime() {
                                        var start = document.getElementById('event_time');
                                        var end = document.getElementById('event_end_time');
                                        if (!start || !end) return true;
                                        if (start.value && end.value && end.value <= start.value) {
                                            end.setCustomValidity('Pukul selesai harus setelah pukul mulai.');
                                            return false;
                                        }
                                        end.setCustomValidity('');
                                        return true;
                                    }
                                    document.addEventListener('input', function (e) {
                                        if (e.target && (e.target.id === 'event_time' || e.target.id === 'event_end_time')) {
                                            syncEndTimeMin();
                                            validateEndTime();
                                        }
                                    });
                                    document.addEventListener('submit', function (e) {
                                        if (e.target && e.target.matches('form[action*="agendas"]') && !validateEndTime()) {
                                            e.preventDefault();
                                            var end = document.getElementById('event_end_time');
                                            if (end) end.reportValidity();
                                        }
                                    }, true);
                                    // Field dirender Alpine (x-if) setelah init — sinkron awal beberapa kali.
                                    var tries = 0;
                                    var t = setInterval(function () {
                                        syncEndTimeMin();
                                        if (document.getElementById('event_end_time') || ++tries >= 10) clearInterval(t);
                                    }, 300);
                                })();
                            </script>
                        </div>

                        <div class="flex items-center gap-3 pt-3">
                            <button type="submit" class="px-6 py-3 rounded-2xl bg-primary text-white text-sm font-bold shadow-md shadow-primary/20 hover:bg-primary-700 hover:shadow-lg active:scale-[0.98] transition-all duration-200">Perbarui</button>
                            <a href="{{ route('admin.agendas.index') }}" class="px-5 py-3 rounded-2xl bg-gray-100 text-gray-600 text-sm font-semibold hover:bg-gray-200 transition-colors">Batal</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
