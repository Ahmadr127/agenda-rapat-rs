<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-extrabold text-gray-900 tracking-tight">Ganti Password</h2>
            <p class="text-sm text-gray-400 mt-0.5">Ubah password untuk akun <span class="font-semibold text-gray-600">{{ $user->name }}</span></p>
        </div>
    </x-slot>

    <div class="max-w-lg">
        {{-- Card --}}
        <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">

            {{-- Card Header --}}
            <div class="px-8 py-6 bg-primary-700 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-white font-extrabold text-lg">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-white font-bold text-base">{{ $user->name }}</p>
                    <p class="text-white/70 text-sm">{{ $user->email }}</p>
                    @if($user->employee?->full_name)
                        <p class="text-white/60 text-xs mt-0.5">{{ $user->employee->full_name }} · {{ $user->employee->job_position }}</p>
                    @endif
                </div>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('admin.users.update-password', $user) }}" id="change-password-form" class="px-8 py-8 space-y-6">
                @csrf
                @method('PUT')

                {{-- New Password --}}
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Password Baru <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="Minimal 6 karakter"
                            class="block w-full px-4 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition @error('password') border-rose-400 bg-rose-50 @enderror"
                        />
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Konfirmasi Password <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Ulangi password baru"
                            class="block w-full px-4 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition"
                        />
                    </div>
                </div>

                {{-- Info box --}}
                <div class="flex items-start gap-3 p-4 rounded-2xl bg-primary-50 border border-primary-100">
                    <svg class="w-4 h-4 text-primary mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    <p class="text-xs text-primary-700 leading-relaxed">
                        Password akan langsung diperbarui. Pastikan pengguna mengetahui password barunya agar dapat masuk ke sistem.
                    </p>
                </div>

                {{-- Buttons --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                        id="submit-change-password"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-primary text-white text-sm font-bold shadow-md shadow-primary/20 hover:bg-primary-700 hover:shadow-lg active:scale-[0.98] transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                        </svg>
                        Simpan Password
                    </button>
                    <a href="{{ route('admin.users.index') }}"
                        class="px-5 py-2.5 rounded-2xl border border-gray-200 text-gray-500 text-sm font-semibold hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
