<section>
    <header class="mb-6">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-10 h-10 rounded-2xl bg-[#007774]/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-[#007774]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">Informasi Profil</h2>
                <p class="text-sm text-gray-500">Perbarui nama dan alamat email akun Anda.</p>
            </div>
        </div>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name', $user->name) }}"
                required
                autofocus
                autocomplete="name"
                placeholder="Masukkan nama lengkap"
                class="block w-full px-4 py-3.5 rounded-2xl border border-gray-200 bg-gray-50/50 text-gray-900 text-sm placeholder-gray-400 transition duration-200 focus:bg-white focus:border-[#007774] focus:ring-2 focus:ring-[#007774]/20 focus:outline-none"
            >
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Alamat Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email', $user->email) }}"
                required
                autocomplete="username"
                placeholder="nama@email.com"
                class="block w-full px-4 py-3.5 rounded-2xl border border-gray-200 bg-gray-50/50 text-gray-900 text-sm placeholder-gray-400 transition duration-200 focus:bg-white focus:border-[#007774] focus:ring-2 focus:ring-[#007774]/20 focus:outline-none"
            >
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3">
                    <p class="text-sm text-gray-700">
                        Alamat email Anda belum diverifikasi.
                        <button form="send-verification" class="font-semibold text-[#007774] hover:text-[#005c5a] underline transition-colors">
                            Kirim ulang email verifikasi.
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-[#81bd41]">
                            Link verifikasi baru telah dikirim ke email Anda.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button
                type="submit"
                class="px-6 py-3 rounded-2xl bg-[#007774] text-white text-sm font-bold shadow-md shadow-[#007774]/20 hover:bg-[#005c5a] hover:shadow-lg hover:shadow-[#007774]/25 active:scale-[0.98] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#007774]/50 focus:ring-offset-2"
            >
                Simpan Perubahan
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm font-semibold text-[#007774]"
                >Tersimpan!</p>
            @endif
        </div>
    </form>
</section>
