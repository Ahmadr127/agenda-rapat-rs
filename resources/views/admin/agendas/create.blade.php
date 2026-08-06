@php
    $oldPresenterIds = array_values(array_filter((array) old('presenter_ids', []), fn ($id) => $id !== null && $id !== ''));
    $initialPresenterCount = count($oldPresenterIds);
    $maxPresenters = 10;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.agendas.index') }}" class="text-gray-400 hover:text-primary transition-colors">Agenda</a>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            <span class="font-semibold text-gray-700">Buat Agenda Baru</span>
        </div>
    </x-slot>

    <div>
        <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-primary-500">
                <h3 class="text-lg font-bold text-gray-900">Buat Agenda Baru</h3>
                <p class="text-sm text-gray-400 mt-0.5">Pilih tipe agenda terlebih dahulu, lalu lengkapi form sesuai kebutuhan.</p>
            </div>
            <div class="p-8" x-data="{ type: '{{ old('type', $userUnit?->name === 'SDM' ? 'rapat' : '') }}', presenterCount: {{ $initialPresenterCount }} }">
                <form action="{{ route('admin.agendas.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <!--section 1-->
                    @if($userUnit?->name === 'SDM')
                        {{-- SDM unit: type is locked to Rapat, selector is hidden --}}
                        <input type="hidden" name="type" value="rapat">
                        <div class="pb-4 border-b border-primary-500">
                            <div class="flex items-center gap-2 rounded-2xl bg-primary/5 border border-primary/20 px-4 py-3">
                                <svg class="w-4 h-4 text-primary flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.75 6.75A2.25 2.25 0 017 4.5h10a2.25 2.25 0 012.25 2.25v10A2.25 2.25 0 0117 19H7a2.25 2.25 0 01-2.25-2.25v-10z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9h7.5M8.25 12h7.5M8.25 15h4.5"/></svg>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">Tipe Agenda: <span class="text-primary">Rapat</span></p>
                                    <p class="text-xs text-gray-400">Unit SDM hanya dapat membuat agenda bertipe Rapat.</p>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Non-SDM units: show Diklat & Pelatihan only --}}
                        <div class="pb-4 border-b border-primary-500">
                            <h4 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.75 6.75A2.25 2.25 0 017 4.5h10a2.25 2.25 0 012.25 2.25v10A2.25 2.25 0 0117 19H7a2.25 2.25 0 01-2.25-2.25v-10z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9h7.5M8.25 12h7.5M8.25 15h4.5"/></svg>
                                Tipe Agenda
                            </h4>

                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="type" value="diklat" x-model="type" class="w-4 h-4 text-primary border-gray-300 focus:ring-primary" required>
                                    <span class="text-sm font-medium text-gray-700">Diklat</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="type" value="pelatihan" x-model="type" class="w-4 h-4 text-primary border-gray-300 focus:ring-primary">
                                    <span class="text-sm font-medium text-gray-700">Pelatihan</span>
                                </label>
                            </div>
                            <p class="text-xs text-gray-400 mt-2">Field form akan menyesuaikan setelah tipe agenda dipilih.</p>
                            @error('type') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div x-show="type || '{{ $userUnit?->name }}' === 'SDM'" x-transition.opacity class="space-y-5" x-cloak>
                        <!--section 2-->
                        <div class="pb-4 border-b border-primary-500">
                            <h4 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                <span x-text="'Informasi ' + (type === 'diklat' ? 'Diklat' : (type === 'pelatihan' ? 'Pelatihan' : 'Rapat'))"></span>
                            </h4>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="title" class="block text-sm font-semibold text-gray-700 mb-2"><span x-text="'Judul ' + (type === 'diklat' ? 'Diklat' : (type === 'pelatihan' ? 'Pelatihan' : 'Rapat'))"></span></label>
                                    <input type="text" name="title" id="title" value="{{ old('title') }}" placeholder="Masukkan judul agenda" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" required>
                                    @error('title') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="event_date" class="block text-sm font-semibold text-gray-700 mb-2"><span x-text="'Tanggal ' + (type === 'diklat' ? 'Diklat' : (type === 'pelatihan' ? 'Pelatihan' : 'Rapat'))"></span></label>
                                    <input type="date" name="event_date" id="event_date" value="{{ old('event_date') }}" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" required>
                                    @error('event_date') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                </div>

                                <template x-if="type === 'diklat' || type === 'pelatihan'">
                                    <div>
                                        <label for="event_end_time" class="block text-sm font-semibold text-gray-700 mb-2">Pukul Selesai</label>
                                        <input type="time" name="event_end_time" id="event_end_time" value="{{ old('event_end_time') }}" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none">
                                        <p class="text-xs text-gray-400 mt-1">Wajib diisi untuk agenda diklat dan pelatihan.</p>
                                        @error('event_end_time') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                    </div>
                                </template>

                                <template x-if="type === 'rapat'">
                                    <div>
                                        <label for="event_end_time" class="block text-sm font-semibold text-gray-700 mb-2">Pukul Selesai</label>
                                        <input type="time" id="event_end_time" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" readOnly disabled>
                                        <p class="text-xs text-gray-400 mt-1">Agenda rapat tidak memakai batasan pukul selesai.</p>
                                        @error('event_end_time') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                    </div>
                                </template>

                                <input type="hidden" name="unit_id" value="{{ old('unit_id', $userUnit->id) }}">

                                <div>
                                    <label for="event_leader_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span x-text="'Pimpinan ' + (type === 'diklat' ? 'Diklat' : (type === 'pelatihan' ? 'Pelatihan' : 'Rapat'))"></span>
                                    </label>
                                    <x-searchable-select
                                        name="event_leader_id"
                                        search-url="{{ route('admin.employees.search') }}"
                                        :selected-id="old('event_leader_id')"
                                        placeholder="Cari pegawai..."
                                        required
                                    />
                                    @error('event_leader_id') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
                                    <textarea name="description" id="description" rows="3" placeholder="Deskripsikan agenda..." class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none">{{ old('description') }}</textarea>
                                    @error('description') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <!--section 3-->
                        <div x-show="type === 'diklat' || type === 'pelatihan'" x-transition class="pb-4 border-b border-primary-500">
                            <h4 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                                <span x-text="'Detail ' + (type === 'diklat' ? 'Diklat' : (type === 'pelatihan' ? 'Pelatihan' : 'Rapat'))"></span>
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

                                    <p class="text-xs text-gray-400 mb-3">Opsional. Tambahkan satu atau lebih pemateri dari data karyawan.</p>

                                    <div class="space-y-3">
                                        @for ($i = 0; $i < $maxPresenters; $i++)
                                            <template x-if="presenterCount > {{ $i }}">
                                                <div>
                                                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Pemateri {{ $i + 1 }}</label>
                                                    <x-searchable-select
                                                        name="presenter_ids[]"
                                                        search-url="{{ route('admin.employees.search') }}"
                                                        :selected-id="old('presenter_ids.' . $i)"
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
                                        :selected-id="old('bank_soal_id')"
                                        placeholder="Cari template bank soal..."
                                    />
                                    <p class="text-xs text-gray-400 mt-1">Opsional. Soal akan disalin ke agenda ini sebagai snapshot.</p>
                                    @error('bank_soal_id') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div x-show="type === 'rapat'" x-transition class="pb-4 border-b border-primary-500">
                            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50/70 px-4 py-4">
                                <p class="text-sm font-semibold text-gray-700">Catatan Tipe Rapat</p>
                                <p class="text-xs text-gray-500 mt-1">Notulensi diinput setelah agenda berjalan. Form rapat tidak menampilkan pemateri, template bank soal, atau pukul selesai.</p>
                            </div>
                        </div>

                        <!--section 4-->
                        <div class="pb-4 border-b border-primary-500">
                            <h4 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                <span x-text="'Berkas ' + (type === 'diklat' ? 'Diklat' : (type === 'pelatihan' ? 'Pelatihan' : 'Rapat'))"></span>
                            </h4>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="letter_file" class="block text-sm font-semibold text-gray-700 mb-2">Surat Undangan</label>
                                    <input type="file" name="letter_file" id="letter_file" accept=".pdf" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none file:mr-4 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                                    <p class="text-xs text-gray-400 mt-1">Format PDF, maksimal 500kb.</p>
                                    @error('letter_file') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="material_file" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span x-text="'Materi ' + (type === 'diklat' ? 'Diklat' : (type === 'pelatihan' ? 'Pelatihan' : 'Rapat'))"></span>
                                    </label>
                                    <input type="file" name="material_file" id="material_file" accept=".pdf" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none file:mr-4 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                                    <p class="text-xs text-gray-400 mt-1">Format PDF, maksimal 10MB.</p>
                                    @error('material_file') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pt-3">
                            <button type="submit" class="px-6 py-3 rounded-2xl bg-primary text-white text-sm font-bold shadow-md shadow-primary/20 hover:bg-primary-700 hover:shadow-lg active:scale-[0.98] transition-all duration-200">Simpan</button>
                            <a href="{{ route('admin.agendas.index') }}" class="px-5 py-3 rounded-2xl bg-gray-100 text-gray-600 text-sm font-semibold hover:bg-gray-200 transition-colors">Batal</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
