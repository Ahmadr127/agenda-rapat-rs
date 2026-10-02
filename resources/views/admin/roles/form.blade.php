<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.roles.index') }}" class="text-gray-400 hover:text-primary transition-colors">Role & Izin</a>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            <span class="font-semibold text-gray-700">{{ isset($role) ? 'Ubah Role' : 'Tambah Role' }}</span>
        </div>
    </x-slot>

    <form id="role-form"
        action="{{ isset($role) ? route('admin.roles.update', $role) : route('admin.roles.store') }}"
        method="POST"
        x-data="{
            name: @js($initialName),
            selected: @js($initialSelected),
            search: '',
            copyFrom: '',
            groups: @js($permissionGroups),
            presets: @js($presets),
            copyRoles: @js($copyRoles),
            menuMap: {
                'users.manage': ['Manajemen Akun'],
                'roles.manage': ['Role & Izin'],
                'units.manage': ['Tulis: Unit'],
                'rooms.manage': ['Tulis: Ruangan'],
                'bank-soals.manage': ['Tulis: Bank Soal'],
                'agendas.manage': ['Buat agenda (unit sendiri)'],
                'agendas.manage-all': ['Kelola agenda (semua unit)'],
                'employees.manage': ['Tambah pegawai (unit sendiri)'],
                'employees.manage-all': ['Kelola pegawai (semua unit)'],
            },
            baseMenus: ['Beranda', 'Lihat: Pegawai', 'Lihat: Agenda', 'Lihat: Rekap & Export'],
            keyOf(id) {
                for (const g of this.groups)
                    for (const p of g.items)
                        if (String(p.id) === String(id)) return p.key;
                return null;
            },
            labelOf(id) {
                for (const g of this.groups)
                    for (const p of g.items)
                        if (String(p.id) === String(id)) return p.label;
                return id;
            },
            groupIds(group) {
                return group.items.map(p => String(p.id));
            },
            groupSelected(group) {
                return this.groupIds(group).filter(id => this.selected.includes(id));
            },
            toggleAll() {
                this.selected = this.allChecked ? [] : this.groups.flatMap(g => this.groupIds(g));
            },
            applyPreset(preset) {
                this.selected = [...preset.permission_ids.map(String)];
            },
            applyCopy() {
                const role = this.copyRoles.find(r => String(r.id) === String(this.copyFrom));
                if (role) this.selected = [...role.permission_ids.map(String)];
            },
            filteredItems(group) {
                const q = this.search.toLowerCase().trim();
                if (!q) return group.items;
                return group.items.filter(p =>
                    p.label.toLowerCase().includes(q) || p.key.toLowerCase().includes(q));
            },
            get allIds() {
                return this.groups.flatMap(g => this.groupIds(g));
            },
            get allChecked() {
                return this.allIds.length > 0 && this.allIds.every(id => this.selected.includes(id));
            },
            get unlockedMenus() {
                const menus = new Set();
                for (const id of this.selected) {
                    const key = this.keyOf(id);
                    (this.menuMap[key] || []).forEach(m => menus.add(m));
                }
                return [...menus];
            },
        }"
        class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        @csrf
        @if(isset($role)) @method('PUT') @endif

        {{-- ===== Kolom utama ===== --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Info dasar --}}
            <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">{{ isset($role) ? 'Ubah Role' : 'Tambah Role Baru' }}</h3>
                        <p class="text-sm text-gray-400 mt-0.5">Tentukan nama role dan centang izin yang dimiliki.</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary-50 text-xs font-bold text-primary">
                        <span x-text="selected.length"></span>&nbsp;izin dipilih
                    </span>
                </div>
                <div class="p-8 grid grid-cols-1 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nama Role</label>
                        <input type="text" name="name" id="name" x-model="name" value="{{ $initialName }}" placeholder="cth: Operator Radiologi" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none" required>
                        @error('name') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="px-8 pb-8">
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
                    <input type="text" name="description" id="description" value="{{ old('description', $role->description ?? '') }}" placeholder="Keterangan singkat role" class="block w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none">
                    @error('description') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
                </div>
            </div>

     
            {{-- Daftar izin per grup --}}
            <div class="space-y-4">
                <template x-for="group in groups" :key="group.group">
                    <div x-show="filteredItems(group).length > 0" class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
                        <div class="w-full px-8 py-5 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-bold text-gray-900 uppercase tracking-wider" x-text="group.group"></span>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-lg"
                                    :class="groupSelected(group).length === groupIds(group).length && groupIds(group).length > 0 ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-500'"
                                    x-text="groupSelected(group).length + '/' + groupIds(group).length"></span>
                            </div>
                        </div>
                        <div class="px-8 pb-6 grid grid-cols-1 md:grid-cols-2 gap-2.5">
                            <template x-for="p in filteredItems(group)" :key="p.id">
                                <label class="flex items-start gap-3 cursor-pointer rounded-2xl border px-4 py-3 transition-all duration-150"
                                    :class="selected.includes(String(p.id)) ? 'border-primary bg-primary-50/50' : 'border-gray-100 hover:border-gray-200'">
                                    <input type="checkbox" name="permission_ids[]" :value="p.id" x-model="selected"
                                        class="mt-0.5 w-4 h-4 rounded text-primary border-gray-300 focus:ring-primary">
                                    <span>
                                        <span class="block text-sm font-medium text-gray-800" x-text="p.label"></span>
                                        <span class="block text-xs text-gray-400 font-mono" x-text="p.key"></span>
                                    </span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>
                @error('permission_ids') <p class="text-rose-500 text-xs font-medium mt-1.5">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- ===== Preview live ===== --}}
        <aside class="lg:sticky lg:top-24 space-y-4">
            <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
               
                <div class="px-6 py-5 border-t border-gray-100 flex items-center gap-2">
                    <button type="submit" class="flex-1 px-6 py-3 rounded-2xl bg-primary text-white text-sm font-bold shadow-md shadow-primary/20 hover:bg-primary-700 hover:shadow-lg active:scale-[0.98] transition-all duration-200">
                        Simpan Role
                    </button>
                    <a href="{{ route('admin.roles.index') }}" class="px-5 py-3 rounded-2xl bg-gray-100 text-gray-600 text-sm font-semibold hover:bg-gray-200 transition-colors">Batal</a>
                </div>
            </div>
        </aside>
    </form>
</x-app-layout>
