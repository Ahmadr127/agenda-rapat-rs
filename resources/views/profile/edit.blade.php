<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-extrabold text-gray-900 tracking-tight">Pengaturan Profil</h2>
            <p class="text-sm text-gray-400 mt-0.5">Kelola informasi akun dan keamanan Anda</p>
        </div>
    </x-slot>

    <div x-data="{ activeTab: window.location.hash.substring(1) || 'general' }" class="flex flex-col lg:flex-row gap-6">

        {{-- LEFT: Profile Card + Tab Navigation --}}
        <div class="lg:w-72 flex-shrink-0 space-y-5">
            {{-- User Card --}}
            <div class="bg-white rounded-3xl border border-gray-100 p-6 text-center">
                <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-primary to-primary-700 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-primary/20">
                    <span class="text-2xl font-extrabold text-white">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                </div>
                <h3 class="text-base font-bold text-gray-900">{{ $user->name }}</h3>
                <p class="text-sm text-gray-400 mt-0.5">{{ $user->email }}</p>
                <div class="mt-4 px-3 py-1.5 rounded-xl bg-primary-50 inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                    <span class="text-xs font-semibold text-primary">Aktif</span>
                </div>
            </div>

            {{-- Tab Navigation --}}
            <div class="bg-white rounded-3xl border border-gray-100 p-3">
                <nav class="space-y-1">
                    <button @click="activeTab = 'general'; window.location.hash = 'general'"
                            :class="activeTab === 'general' ? 'bg-primary-50 text-primary' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-semibold transition-all duration-200">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        Umum
                    </button>
                    <button @click="activeTab = 'security'; window.location.hash = 'security'"
                            :class="activeTab === 'security' ? 'bg-primary-50 text-primary' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-semibold transition-all duration-200">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        Keamanan
                    </button>
                    <button @click="activeTab = 'danger'; window.location.hash = 'danger'"
                            :class="activeTab === 'danger' ? 'bg-rose-50 text-rose-600' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700'"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-semibold transition-all duration-200">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                        Zona Bahaya
                    </button>
                </nav>
            </div>
        </div>

        {{-- RIGHT: Content Panes --}}
        <div class="flex-1 min-w-0">
            {{-- General Tab --}}
            <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
                    <div class="p-6 sm:p-8">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            </div>

            {{-- Security Tab --}}
            <div x-show="activeTab === 'security'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
                    <div class="p-6 sm:p-8">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>

            {{-- Danger Zone Tab --}}
            <div x-show="activeTab === 'danger'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="bg-white rounded-3xl border border-rose-100 overflow-hidden">
                    <div class="p-6 sm:p-8">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
