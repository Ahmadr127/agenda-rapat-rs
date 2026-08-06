<section class="space-y-6">
    <header>
        <div class="flex items-center gap-3 mb-1">
            <div class="w-10 h-10 rounded-2xl bg-rose-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">Hapus Akun</h2>
                <p class="text-sm text-gray-500">Setelah akun dihapus, semua data akan hilang secara permanen.</p>
            </div>
        </div>
    </header>

    <div class="p-4 rounded-2xl bg-rose-50/50 border border-rose-100">
        <p class="text-sm text-rose-700 leading-relaxed">
            Sebelum menghapus akun, pastikan Anda sudah mengunduh data atau informasi yang ingin disimpan. Tindakan ini tidak dapat dibatalkan.
        </p>
    </div>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="px-6 py-3 rounded-2xl bg-white text-rose-600 text-sm font-bold border-2 border-rose-200 hover:bg-rose-50 hover:border-rose-300 active:scale-[0.98] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-rose-500/30 focus:ring-offset-2"
    >
        Hapus Akun Saya
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-8">
            @csrf
            @method('delete')

            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Konfirmasi Penghapusan</h2>
                    <p class="text-sm text-gray-500">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
            </div>

            <p class="text-sm text-gray-600 leading-relaxed mb-6">
                Setelah akun Anda dihapus, semua data dan informasi akan dihapus secara permanen. Masukkan kata sandi Anda untuk mengonfirmasi penghapusan akun.
            </p>

            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Kata Sandi</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="Masukkan kata sandi Anda"
                    class="block w-full px-4 py-3.5 rounded-2xl border border-gray-200 bg-gray-50/50 text-gray-900 text-sm placeholder-gray-400 transition duration-200 focus:bg-white focus:border-rose-400 focus:ring-2 focus:ring-rose-400/20 focus:outline-none"
                >
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-8 flex items-center justify-end gap-3">
                <button
                    type="button"
                    x-on:click="$dispatch('close')"
                    class="px-5 py-2.5 rounded-2xl bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200 active:scale-[0.98] transition-all duration-200"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-2xl bg-rose-500 text-white text-sm font-bold shadow-md shadow-rose-500/20 hover:bg-rose-600 hover:shadow-lg active:scale-[0.98] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-rose-500/50 focus:ring-offset-2"
                >
                    Ya, Hapus Akun
                </button>
            </div>
        </form>
    </x-modal>
</section>
