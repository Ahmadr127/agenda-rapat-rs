<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'D-ASSA') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap"
        rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div x-data="{ sidebarOpen: true, mobileSidebar: false }" class="min-h-screen bg-gray-50/80">

        {{-- Mobile Overlay --}}
        <div x-show="mobileSidebar" x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click="mobileSidebar = false"
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-40 lg:hidden" x-cloak></div>

        {{-- ===== SIDEBAR ===== --}}
        <aside :class="mobileSidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed top-0 left-0 z-50 h-screen transition-all duration-300 ease-in-out flex flex-col bg-[#007774] lg:z-30"
            :style="sidebarOpen ? 'width: 272px' : 'width: 80px'">
            {{-- Branding --}}
            <div class="flex items-center gap-3 px-5 h-[72px] border-b border-white/10 flex-shrink-0">
                <div
                    class="w-10 h-10 rounded-2xl bg-white/15 backdrop-blur-sm flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21" />
                    </svg>
                </div>
                <div x-show="sidebarOpen" x-transition:enter="transition-opacity duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="overflow-hidden">
                    <h1 class="text-white font-extrabold text-lg leading-tight tracking-tight">RS AZRA</h1>
                    <p class="text-white/50 text-[10px] font-medium leading-tight">Digital Agenda & Attendance</p>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto scrollbar-hide px-3 py-6 space-y-1.5">
                <p x-show="sidebarOpen"
                    class="px-3 mb-3 text-[10px] font-bold text-white/30 uppercase tracking-[0.15em]">Menu Utama</p>

                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}"
                    class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                              {{ request()->routeIs('dashboard') ? 'bg-white/15 text-white shadow-lg shadow-black/10' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <div
                        class="w-9 h-9 rounded-xl {{ request()->routeIs('dashboard') ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' }} flex items-center justify-center flex-shrink-0 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                        </svg>
                    </div>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Beranda</span>
                </a>

                {{-- Pegawai --}}
                <a href="{{ route('admin.employees.index') }}"
                    class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                              {{ request()->routeIs('admin.employees.*') ? 'bg-white/15 text-white shadow-lg shadow-black/10' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <div
                        class="w-9 h-9 rounded-xl {{ request()->routeIs('admin.employees.*') ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' }} flex items-center justify-center flex-shrink-0 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Pegawai</span>
                </a>

                {{-- Rekap Karyawan --}}
                <a href="{{ route('admin.employee-recaps.index') }}"
                    class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                              {{ request()->routeIs('admin.employee-recaps.*') ? 'bg-white/15 text-white shadow-lg shadow-black/10' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <div
                        class="w-9 h-9 rounded-xl {{ request()->routeIs('admin.employee-recaps.*') ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' }} flex items-center justify-center flex-shrink-0 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 13.125C3 12.503 3.503 12 4.125 12h2.25c.622 0 1.125.503 1.125 1.125V20.25H3v-7.125zM9.75 8.625c0-.622.503-1.125 1.125-1.125h2.25c.622 0 1.125.503 1.125 1.125v11.625H9.75V8.625zM16.5 4.125C16.5 3.503 17.003 3 17.625 3h2.25C20.497 3 21 3.503 21 4.125V20.25h-4.5V4.125z" />
                        </svg>
                    </div>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Rekap Karyawan</span>
                </a>

                {{-- Ruangan --}}
                <a href="{{ route('admin.rooms.index') }}"
                    class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                              {{ request()->routeIs('admin.rooms.*') ? 'bg-white/15 text-white shadow-lg shadow-black/10' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <div
                        class="w-9 h-9 rounded-xl {{ request()->routeIs('admin.rooms.*') ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' }} flex items-center justify-center flex-shrink-0 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21" />
                        </svg>
                    </div>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Ruangan</span>
                </a>

                {{-- Unit --}}
                <a href="{{ route('admin.units.index') }}"
                    class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                              {{ request()->routeIs('admin.units.*') ? 'bg-white/15 text-white shadow-lg shadow-black/10' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <div
                        class="w-9 h-9 rounded-xl {{ request()->routeIs('admin.units.*') ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' }} flex items-center justify-center flex-shrink-0 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                    </div>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Unit</span>
                </a>

                {{-- Agenda --}}
                <a href="{{ route('admin.agendas.index') }}"
                    class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                              {{ request()->routeIs('admin.agendas.*') ? 'bg-white/15 text-white shadow-lg shadow-black/10' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <div
                        class="w-9 h-9 rounded-xl {{ request()->routeIs('admin.agendas.*') ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' }} flex items-center justify-center flex-shrink-0 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                    </div>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Agenda</span>
                </a>

                {{-- Bank Soal --}}
                <a href="{{ route('admin.bank-soals.index') }}"
                    class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                              {{ request()->routeIs('admin.bank-soals.*') ? 'bg-white/15 text-white shadow-lg shadow-black/10' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <div
                        class="w-9 h-9 rounded-xl {{ request()->routeIs('admin.bank-soals.*') ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' }} flex items-center justify-center flex-shrink-0 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                        </svg>
                    </div>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Bank Soal</span>
                </a>

                {{-- Manajemen Akun (MANAGER IT only) --}}
                @php $currentEmployee = Auth::user()->employee; @endphp
                @if($currentEmployee && $currentEmployee->job_position === 'MANAGER IT')
                <a href="{{ route('admin.users.index') }}"
                    class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                              {{ request()->routeIs('admin.users.*') ? 'bg-white/15 text-white shadow-lg shadow-black/10' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <div
                        class="w-9 h-9 rounded-xl {{ request()->routeIs('admin.users.*') ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' }} flex items-center justify-center flex-shrink-0 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                        </svg>
                    </div>
                    <span x-show="sidebarOpen" class="text-sm font-semibold">Manajemen Akun</span>
                </a>
                @endif
            </nav>

            {{-- User Section --}}
            <div class="flex-shrink-0 border-t border-white/10 p-3">
                <a href="{{ route('profile.edit') }}"
                    class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200
                              {{ request()->routeIs('profile.*') ? 'bg-white/15 text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                    <div
                        class="w-9 h-9 rounded-xl bg-gradient-to-br from-secondary/80 to-secondary flex items-center justify-center flex-shrink-0 text-white text-sm font-bold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div x-show="sidebarOpen" class="overflow-hidden min-w-0">
                        @php $employee = Auth::user()->employee; @endphp
                        <p class="text-sm font-semibold text-white truncate">
                            {{ $employee?->full_name ?? Auth::user()->name }}
                        </p>
                        <p class="text-[11px] text-white/40 truncate">
                            {{ $employee?->job_position ?? Auth::user()->email }}
                        </p>
                    </div>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="mt-1.5">
                    @csrf
                    <button type="submit"
                        class="group flex items-center gap-3 px-3 py-2.5 rounded-xl w-full text-white/50 hover:bg-rose-500/15 hover:text-rose-300 transition-all duration-200">
                        <div
                            class="w-9 h-9 rounded-xl bg-white/5 group-hover:bg-rose-500/20 flex items-center justify-center flex-shrink-0 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                        </div>
                        <span x-show="sidebarOpen" class="text-sm font-semibold">Keluar</span>
                    </button>
                </form>
            </div>

            {{-- Collapse Toggle (Desktop only) --}}
            <button @click="sidebarOpen = !sidebarOpen"
                class="hidden lg:flex absolute -right-3 top-20 w-6 h-6 rounded-full bg-white shadow-lg shadow-gray-200/50 border border-gray-100 items-center justify-center text-gray-400 hover:text-primary transition-colors z-50">
                <svg :class="!sidebarOpen && 'rotate-180'" class="w-3.5 h-3.5 transition-transform duration-300"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </button>
        </aside>

        {{-- ===== MAIN CONTENT ===== --}}
        <div class="transition-all duration-300 ease-in-out"
            :style="sidebarOpen ? 'margin-left: 272px' : 'margin-left: 80px'" :class="'lg:ml-0'">

            {{-- Top Bar --}}
            <header
                class="sticky top-0 z-20 h-[72px] bg-white/70 backdrop-blur-xl border-b border-gray-100/80 flex items-center justify-between px-6 lg:px-8">
                {{-- Mobile hamburger --}}
                <button @click="mobileSidebar = true"
                    class="lg:hidden w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                {{-- Page Title --}}
                <div class="hidden lg:block">
                    @isset($header)
                        {{ $header }}
                    @endisset
                </div>

                {{-- Right Actions --}}
                <div class="flex items-center gap-3">
                </div>
            </header>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="mx-6 lg:mx-8 mt-4">
                    <div
                        class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-secondary-50 border border-secondary-200 text-secondary-800">
                        <svg class="w-5 h-5 text-secondary flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            {{-- Page Content --}}
            <main class="p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Hide scrollbar but keep scrolling */
        .scrollbar-hide {
            -ms-overflow-style: none;  /* IE & Edge */
            scrollbar-width: none;     /* Firefox */
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;             /* Chrome, Safari, Opera */
        }
    </style>
</body>

</html>
