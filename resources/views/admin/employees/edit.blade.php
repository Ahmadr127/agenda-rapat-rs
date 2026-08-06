<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.employees.index') }}" class="text-gray-400 hover:text-primary transition-colors">Pegawai</a>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            <span class="font-semibold text-gray-700">Ubah Pegawai</span>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Ubah Data Pegawai</h3>
                <p class="text-sm text-gray-400 mt-0.5">Perbarui informasi pegawai</p>
            </div>
            <div class="p-8">
                <form action="{{ route('admin.employees.update', $employee) }}" method="POST" class="space-y-4">
                    @csrf @method('PUT')

                    <div>
                        <label for="nip" class="block text-sm font-semibold text-gray-700 mb-2">NIP</label>
                        <input type="text" name="nip" id="nip" value="{{ old('nip', $employee->nip) }}" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" required>
                        @error('nip') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="full_name" class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                        <input type="text" name="full_name" id="full_name" value="{{ old('full_name', $employee->full_name) }}" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" required>
                        @error('full_name') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="unit_id" class="block text-sm font-semibold text-gray-700 mb-2">Unit</label>
                        <x-searchable-select
                            name="unit_id"
                            search-url="{{ route('admin.units.search') }}"
                            :selected-id="old('unit_id', $employee->unit_id)"
                            :selected-label="old('unit_id') ? null : $employee->unit?->name"
                            placeholder="Cari unit..."
                            required
                        />
                        @error('unit_id') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="job_position" class="block text-sm font-semibold text-gray-700 mb-2">Posisi Pekerjaan</label>
                            <input type="text" name="job_position" id="job_position" value="{{ old('job_position', $employee->job_position) }}" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" required>
                            @error('job_position') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="structural_role" class="block text-sm font-semibold text-gray-700 mb-2">Jabatan</label>
                            <input type="text" name="structural_role" id="structural_role" value="{{ old('structural_role', $employee->structural_role) }}" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" required>
                            @error('structural_role') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="profession" class="block text-sm font-semibold text-gray-700 mb-2">Profesi</label>
                        <input type="text" name="profession" id="profession" value="{{ old('profession', $employee->profession) }}" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" required>
                        @error('profession') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-3 pt-3">
                        <button type="submit" class="px-6 py-3 rounded-2xl bg-primary text-white text-sm font-bold shadow-md shadow-primary/20 hover:bg-primary-700 hover:shadow-lg active:scale-[0.98] transition-all duration-200">Perbarui</button>
                        <a href="{{ route('admin.employees.index') }}" class="px-5 py-3 rounded-2xl bg-gray-100 text-gray-600 text-sm font-semibold hover:bg-gray-200 transition-colors">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
