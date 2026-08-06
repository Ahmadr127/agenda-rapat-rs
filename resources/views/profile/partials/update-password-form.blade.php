<section>
    <header class="mb-6">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-10 h-10 rounded-2xl bg-[#007774]/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-[#007774]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">Update Kata Sandi</h2>
                <p class="text-sm text-gray-500">Pastikan akun Anda menggunakan kata sandi yang panjang dan acak.</p>
            </div>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-sm font-semibold text-gray-700 mb-2">Kata Sandi Saat Ini</label>
            <input
                id="update_password_current_password"
                name="current_password"
                type="password"
                autocomplete="current-password"
                placeholder="Masukkan kata sandi saat ini"
                class="block w-full px-4 py-3.5 rounded-2xl border border-gray-200 bg-gray-50/50 text-gray-900 text-sm placeholder-gray-400 transition duration-200 focus:bg-white focus:border-[#007774] focus:ring-2 focus:ring-[#007774]/20 focus:outline-none"
            >
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password" class="block text-sm font-semibold text-gray-700 mb-2">Kata Sandi Baru</label>
            <input
                id="update_password_password"
                name="password"
                type="password"
                autocomplete="new-password"
                placeholder="Masukkan kata sandi baru"
                class="block w-full px-4 py-3.5 rounded-2xl border border-gray-200 bg-gray-50/50 text-gray-900 text-sm placeholder-gray-400 transition duration-200 focus:bg-white focus:border-[#007774] focus:ring-2 focus:ring-[#007774]/20 focus:outline-none"
            >
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Konfirmasi Kata Sandi</label>
            <input
                id="update_password_password_confirmation"
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
                placeholder="Ulangi kata sandi baru"
                class="block w-full px-4 py-3.5 rounded-2xl border border-gray-200 bg-gray-50/50 text-gray-900 text-sm placeholder-gray-400 transition duration-200 focus:bg-white focus:border-[#007774] focus:ring-2 focus:ring-[#007774]/20 focus:outline-none"
            >
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button
                type="submit"
                class="px-6 py-3 rounded-2xl bg-[#007774] text-white text-sm font-bold shadow-md shadow-[#007774]/20 hover:bg-[#005c5a] hover:shadow-lg hover:shadow-[#007774]/25 active:scale-[0.98] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#007774]/50 focus:ring-offset-2"
            >
                Perbarui Kata Sandi
            </button>

            @if (session('status') === 'password-updated')
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
