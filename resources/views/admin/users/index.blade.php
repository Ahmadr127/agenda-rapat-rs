<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-extrabold text-gray-900 tracking-tight">Manajemen Akun</h2>
            <p class="text-sm text-gray-400 mt-0.5">Daftar semua akun pengguna sistem</p>
        </div>
    </x-slot>

    {{-- Header Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
        <div class="flex items-center gap-2">
            <div class="w-10 h-10 rounded-2xl bg-primary-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
            </div>
            <span class="text-sm font-semibold text-gray-500">{{ $users->total() }} akun</span>
        </div>
    </div>

    {{-- Search --}}
    <div class="mb-6">
        <x-search-filter
            :action="route('admin.users.index')"
            :q="$q"
            placeholder="Cari nama atau email..."
        />
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-primary-700 text-white">
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Nama Akun</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Nama Pegawai</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Jabatan</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Dibuat</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $index => $user)
                        <tr class="group transition-colors hover:bg-primary-50/40 {{ $index % 2 === 0 ? 'bg-white' : 'bg-slate-50' }}">
                            {{-- Name + Avatar --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-primary-50 flex items-center justify-center text-xs font-bold text-primary flex-shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span class="text-sm font-semibold text-gray-800">{{ $user->name }}</span>
                                </div>
                            </td>

                            {{-- Email --}}
                            <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ $user->email }}</td>

                            {{-- Employee full name --}}
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $user->employee?->full_name ?? '—' }}
                            </td>

                            {{-- Job position --}}
                            <td class="px-6 py-4">
                                @if($user->employee?->job_position)
                                    <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-semibold
                                        {{ $user->employee->job_position === 'MANAGER IT' ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $user->employee->job_position }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-sm">—</span>
                                @endif
                            </td>

                            {{-- Created at --}}
                            <td class="px-6 py-4 text-sm text-gray-400">
                                {{ $user->created_at->format('d M Y') }}
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.users.change-password', $user) }}"
                                        id="change-password-{{ $user->id }}"
                                        title="Ganti Password"
                                        class="p-2 rounded-xl hover:bg-primary-50 text-gray-400 hover:text-primary transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-400">Tidak ada akun ditemukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">{{ $users->links() }}</div>
        @endif
    </div>
</x-app-layout>
