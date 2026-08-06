<x-guest-layout>
    {{-- Left Column: Branding --}}
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-primary-700 via-primary to-primary-900">
        {{-- Animated decorative shapes --}}
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-32 -left-32 w-[500px] h-[500px] rounded-full bg-secondary/10 blur-3xl animate-pulse"></div>
            <div class="absolute top-1/4 -right-24 w-96 h-96 rounded-full bg-primary-300/10 blur-2xl"></div>
            <div class="absolute -bottom-40 left-1/3 w-[600px] h-[600px] rounded-full bg-secondary/[0.08] blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
            {{-- Geometric accents --}}
            <div class="absolute top-20 right-20 w-40 h-40 rounded-full border-2 border-primary-300/15"></div>
            <div class="absolute top-24 right-24 w-32 h-32 rounded-full border border-primary-200/10"></div>
            <div class="absolute bottom-32 left-12 w-24 h-24 rounded-full border-2 border-secondary/20"></div>
            <div class="absolute bottom-36 left-16 w-16 h-16 rounded-full border border-secondary/10"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[700px] rounded-full border border-white/[0.03]"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full border border-white/[0.05]"></div>
            {{-- Subtle grid pattern --}}
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 40px 40px;"></div>
        </div>

        {{-- Content --}}
        <div class="relative z-10 flex flex-col items-center justify-center w-full px-12 xl:px-20">
            {{-- RS AZRA Logo --}}
            <div class="mb-8">
                <div class="w-24 h-24 xl:w-28 xl:h-28 rounded-3xl bg-white/15 backdrop-blur-md border border-white/20 shadow-2xl flex items-center justify-center">
                    <svg class="w-14 h-14 xl:w-16 xl:h-16 text-white" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        {{-- Medical cross --}}
                        <rect x="24" y="8" width="16" height="48" rx="4" fill="currentColor" opacity="0.9"/>
                        <rect x="8" y="24" width="48" height="16" rx="4" fill="currentColor" opacity="0.9"/>
                        {{-- Inner highlight --}}
                        <rect x="27" y="11" width="10" height="42" rx="2" fill="currentColor" opacity="0.3"/>
                        <rect x="11" y="27" width="42" height="10" rx="2" fill="currentColor" opacity="0.3"/>
                    </svg>
                </div>
            </div>

            {{-- Glassmorphism card --}}
            <div class="backdrop-blur-xl bg-white/10 rounded-3xl p-10 xl:p-12 border border-white/20 shadow-2xl max-w-lg text-center">
                {{-- RS AZRA title --}}
                <h1 class="text-4xl xl:text-5xl font-extrabold text-white leading-tight mb-2 tracking-tight">
                    RS <span class="text-secondary-300">AZRA</span>
                </h1>
                <p class="text-white/60 text-sm font-medium tracking-[0.2em] uppercase mb-8">Rumah Sakit Azra Bogor</p>

                {{-- Divider --}}
                <div class="flex items-center gap-4 mb-8">
                    <div class="flex-1 h-px bg-gradient-to-r from-transparent to-white/20"></div>
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-secondary/20 backdrop-blur-sm text-secondary-100 text-xs font-semibold tracking-wider uppercase">
                        D-ASSA
                    </span>
                    <div class="flex-1 h-px bg-gradient-to-l from-transparent to-white/20"></div>
                </div>

                {{-- Description --}}
                <h2 class="text-xl xl:text-2xl font-bold text-white mb-4">
                    Digital Agenda &<br>Self-Service Attendance
                </h2>
                <p class="text-primary-100/70 text-sm xl:text-base leading-relaxed mb-8">
                    Platform modern untuk mengelola agenda, master data, dan absensi digital mandiri di lingkungan RS Azra.
                </p>

                {{-- Feature highlights --}}
                <div class="grid grid-cols-3 gap-3">
                    <div class="rounded-2xl bg-white/10 backdrop-blur-sm border border-white/10 p-4">
                        <svg class="w-6 h-6 text-secondary-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-white/80 text-xs font-medium">Agenda</span>
                    </div>
                    <div class="rounded-2xl bg-white/10 backdrop-blur-sm border border-white/10 p-4">
                        <svg class="w-6 h-6 text-secondary-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <span class="text-white/80 text-xs font-medium">Absensi</span>
                    </div>
                    <div class="rounded-2xl bg-white/10 backdrop-blur-sm border border-white/10 p-4">
                        <svg class="w-6 h-6 text-secondary-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="text-white/80 text-xs font-medium">Laporan</span>
                    </div>
                </div>
            </div>

            {{-- Bottom tagline --}}
            <p class="mt-8 text-primary-200/50 text-xs tracking-wider uppercase">
                &copy; {{ date('Y') }} RS Azra &mdash; Melayani dengan Hati
            </p>
        </div>
    </div>

    {{-- Right Column: Login Form --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center bg-white px-6 py-12 sm:px-12">
        <div class="w-full max-w-md">
            {{-- Mobile logo --}}
            <div class="lg:hidden mb-8 text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-gradient-to-r from-primary to-secondary">
                    <span class="text-white font-extrabold text-xl">D-ASSA</span>
                </div>
            </div>

            {{-- Header --}}
            <div class="mb-10">
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Selamat Datang,</h2>
                <p class="mt-2 text-base text-gray-500">Silakan masuk ke akun Anda.</p>
            </div>

            {{-- Session Status --}}
            <x-auth-session-status class="mb-6" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="mb-5">
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Alamat Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="nama@email.com"
                        class="block w-full px-4 py-3.5 rounded-2xl border border-gray-200 bg-gray-50/50 text-gray-900 text-sm placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none"
                    >
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                {{-- Password --}}
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-sm font-semibold text-gray-700">Kata Sandi</label>

                        {{-- Forgot password link --}}

                    </div>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Masukkan kata sandi"
                        class="block w-full px-4 py-3.5 rounded-2xl border border-gray-200 bg-gray-50/50 text-gray-900 text-sm placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none"
                    >
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                {{-- Remember Me --}}

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full py-3.5 px-6 rounded-2xl bg-primary text-white text-sm font-bold tracking-wide shadow-lg shadow-primary/25 hover:bg-primary-700 hover:shadow-xl hover:shadow-primary/30 active:scale-[0.98] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:ring-offset-2"
                >
                    Masuk
                </button>
            </form>

            {{-- Register link --}}

            {{-- Footer --}}
            <p class="mt-12 text-center text-xs text-gray-400">
                &copy; {{ date('Y') }} D-ASSA. Digital Agenda & Self-Service Attendance.
            </p>
        </div>
    </div>
</x-guest-layout>
