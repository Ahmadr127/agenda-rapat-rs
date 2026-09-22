<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.users.index') }}" class="text-gray-400 hover:text-primary transition-colors">Manajemen Akun</a>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            <span class="font-semibold text-gray-700">Ubah Akun</span>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900">Ubah Akun</h3>
                <p class="text-sm text-gray-400 mt-0.5">
                    Tertaut ke pegawai: <span class="font-semibold text-gray-600">{{ $user->employee?->full_name ?? '— (tidak tertaut)' }}</span>
                </p>
            </div>
            <div class="p-8">
                <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nama Akun</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" required>
                        @error('name') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                            <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" required>
                            @error('username') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" required>
                            @error('email') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    @if($user->employee)
                        <div>
                            <label for="unit_id" class="block text-sm font-semibold text-gray-700 mb-2">Unit</label>
                            <x-searchable-select
                                name="unit_id"
                                search-url="{{ route('admin.units.search') }}"
                                :selected-id="old('unit_id', $user->employee->unit_id)"
                                :selected-label="old('unit_id') ? null : $user->employee->unit?->name"
                                placeholder="Cari unit..."
                                required
                            />
                            <p class="text-xs text-gray-400 mt-1">Mengubah unit pegawai yang tertaut.</p>
                            @error('unit_id') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                        </div>
                    @else
                        <p class="text-xs text-gray-400">Akun ini tidak tertaut ke pegawai, jadi tidak punya unit.</p>
                    @endif

                    <div class="flex items-center gap-3 pt-3">
                        <button type="submit" class="px-6 py-3 rounded-2xl bg-primary text-white text-sm font-bold shadow-md shadow-primary/20 hover:bg-primary-700 hover:shadow-lg active:scale-[0.98] transition-all duration-200">Simpan</button>
                        <a href="{{ route('admin.users.index') }}" class="px-5 py-3 rounded-2xl bg-gray-100 text-gray-600 text-sm font-semibold hover:bg-gray-200 transition-colors">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
