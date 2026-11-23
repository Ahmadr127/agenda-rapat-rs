@props([
    'name',
    'searchUrl',
    'selectedId' => null,
    'selectedLabel' => null,
    'placeholder' => 'Cari...',
    'required' => false,
    'bgColor' => 'bg-gray-50/50',
    'multiple' => false,
    'selectedIds' => [],
])

<div
    x-data="{
        open: false,
        search: '',
        name: @js($name),
        selectedId: @js($selectedId ?? ''),
        selectedLabel: @js($selectedLabel ?? ''),
        searchUrl: @js($searchUrl),
        requiredMessage: @js($required ? 'Pilih salah satu opsi' : ''),
        items: [],
        hasMore: false,
        loading: false,
        fetched: false,
        debounceTimer: null,
        multiple: @js($multiple),
        selectedMembers: @js($selectedIds ?? []),

        get selectedCount() {
            return this.selectedMembers.length;
        },

        get allVisibleSelected() {
            if (!this.items.length) return false;
            return this.items.every((item) => this.selectedMembers.some((m) => String(m.id) === String(item.id)));
        },

        notifyChange() {
            const value = this.multiple
                ? this.selectedMembers.map((m) => m.id)
                : (this.selectedId ?? '');

            this.$dispatch('searchable-select-change', {
                name: this.name,
                value: value,
                label: this.multiple
                    ? this.selectedMembers.map((m) => m.name).join('\n')
                    : this.selectedLabel,
            });
        },

        async fetchItems(query = '') {
            this.loading = true;
            try {
                const params = query ? { q: query } : {};
                const { data } = await axios.get(this.searchUrl, { params });
                this.items = data.items;
                this.hasMore = data.has_more;
                this.fetched = true;
            } finally {
                this.loading = false;
            }
        },

        async resolveLabel() {
            if (!this.selectedId) return;
            try {
                const { data } = await axios.get(this.searchUrl, { params: { id: this.selectedId } });
                if (data.items.length) {
                    this.search = data.items[0].name;
                    this.selectedLabel = data.items[0].name;
                    this.notifyChange();
                }
            } catch {}
        },

        syncMemberInputs() {
            const container = this.$refs.memberContainer;

            if (!container) return;

            container.innerHTML = '';

            this.selectedMembers.forEach((member) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = this.name;
                input.value = member.id;
                container.appendChild(input);
            });
        },

        toggleMember(item) {
            const exists = this.selectedMembers.some((m) => String(m.id) === String(item.id));

            if (exists) {
                this.selectedMembers = this.selectedMembers.filter((m) => String(m.id) !== String(item.id));
            } else {
                this.selectedMembers = [...this.selectedMembers, {
                    id: item.id,
                    name: item.name,
                    job_position: item.job_position,
                    structural_role: item.structural_role,
                    profession: item.profession,
                }];
            }

            this.syncMemberInputs();
            this.notifyChange();
            this.open = false;
        },

        removeMember(id) {
            this.selectedMembers = this.selectedMembers.filter((m) => String(m.id) !== String(id));
            this.syncMemberInputs();
            this.notifyChange();
        },

        toggleAllVisible() {
            if (!this.items.length) return;

            const visibleIds = new Set(this.items.map((item) => String(item.id)));

            if (this.allVisibleSelected) {
                this.selectedMembers = this.selectedMembers.filter((m) => !visibleIds.has(String(m.id)));
            } else {
                const existingIds = new Set(this.selectedMembers.map((m) => String(m.id)));
                const toAdd = this.items
                    .filter((item) => !existingIds.has(String(item.id)))
                    .map((item) => ({
                        id: item.id,
                        name: item.name,
                        job_position: item.job_position,
                        structural_role: item.structural_role,
                        profession: item.profession,
                    }));

                this.selectedMembers = [...this.selectedMembers, ...toAdd];
            }

            this.syncMemberInputs();
            this.notifyChange();
        },

        select(item) {
            if (this.multiple) {
                this.toggleMember(item);
                return;
            }

            this.selectedId = item.id;
            this.search = item.name;
            this.selectedLabel = item.name;
            this.open = false;
            this.$refs.searchInput.setCustomValidity('');
            this.notifyChange();
        },

        onInput() {
            this.open = true;
            this.$refs.searchInput.setCustomValidity(this.requiredMessage);

            if (this.multiple) {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => this.fetchItems(this.search), 300);
                return;
            }

            this.selectedId = '';
            this.selectedLabel = '';
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => this.fetchItems(this.search), 300);
            this.notifyChange();
        },

        onFocus() {
            this.open = true;
            if (!this.items.length && !this.loading) {
                this.fetchItems(this.search);
            }
        },

        onEnter() {
            if (this.items.length === 1) {
                this.select(this.items[0]);
            }
        },

        onBlur() {
            setTimeout(() => {
                if (!this.selectedId && !this.multiple) this.search = '';
                this.open = false;
            }, 200);
        },

        async init() {
            if (this.multiple) {
                const rawIds = Array.isArray(this.selectedMembers) ? this.selectedMembers : [];
                const resolved = [];

                for (const id of rawIds) {
                    if (typeof id === 'object' && id !== null) {
                        resolved.push(id);
                        continue;
                    }

                    try {
                        const { data } = await axios.get(this.searchUrl, { params: { id } });
                        const item = (data.items || [])[0];

                        resolved.push(item ? {
                            id: item.id,
                            name: item.name,
                            job_position: item.job_position,
                            structural_role: item.structural_role,
                            profession: item.profession,
                        } : { id, name: String(id) });
                    } catch {
                        resolved.push({ id, name: String(id) });
                    }
                }

                this.selectedMembers = resolved;
                this.syncMemberInputs();
                return;
            }

            if (this.selectedId && this.selectedLabel) {
                this.search = this.selectedLabel;
                this.notifyChange();
            } else if (this.selectedId) {
                this.resolveLabel();
            }

            @if($required)
            this.$watch('selectedId', (val) => {
                this.$refs.searchInput.setCustomValidity(val ? '' : this.requiredMessage);
            });
            if (!this.selectedId) {
                this.$nextTick(() => this.$refs.searchInput.setCustomValidity(this.requiredMessage));
            }
            @endif
        }
    }"
    @click.outside="open = false"
    class="relative"
>
    @if($multiple)
        <div x-ref="memberContainer"></div>
    @else
        <input type="hidden" name="{{ $name }}" :value="selectedId">
    @endif

    <div class="relative">
        <input
            type="text"
            x-ref="searchInput"
            x-model="search"
            @focus="onFocus()"
            @click="onFocus()"
            @input="onInput()"
            @blur="onBlur()"
            @keydown.enter.prevent="onEnter()"
            @keydown.escape="open = false"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            class="block w-full px-4 py-3 pr-10 rounded-2xl border border-gray-200 {{ $bgColor }} text-sm text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none"
        >
        <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
    </div>

    <!-- Anggota yang sudah dipilih -->
    @if($multiple)
        <div class="mt-3" x-show="selectedMembers.length" x-cloak>
            <p class="text-xs font-semibold text-gray-500 mb-2">
                Anggota terpilih (<span x-text="selectedCount"></span>)
            </p>
            <div class="flex flex-wrap gap-2">
                <template x-for="member in selectedMembers" :key="member.id">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-primary/10 text-primary text-xs font-semibold">
                        <span x-text="member.name"></span>
                        <span class="text-[10px] text-primary/60" x-text="member.job_position || ''"></span>
                        <button type="button" @click="removeMember(member.id)" class="w-4 h-4 rounded-full bg-primary/20 hover:bg-primary/40 flex items-center justify-center transition">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </span>
                </template>
            </div>
        </div>
    @endif

    <div
        x-show="open && (loading || fetched)"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-2xl shadow-lg max-h-60 overflow-y-auto"
        style="display: none;"
    >
        <div
            x-show="loading"
            class="px-4 py-2.5 text-sm text-gray-400 text-center animate-pulse"
        >
            Memuat...
        </div>

        <template x-if="!loading">
            <div>
                <div
                    x-show="items.length > 0 && multiple"
                    class="px-4 py-2 border-b border-gray-100 flex items-center justify-between"
                >
                    <button
                        type="button"
                        @click="toggleAllVisible()"
                        class="text-xs font-semibold text-primary hover:text-primary-700 transition"
                        x-text="allVisibleSelected ? 'Batalkan semua' : 'Pilih semua'"
                    ></button>
                    <span class="text-[10px] text-gray-400" x-text="items.length + ' hasil'"></span>
                </div>

                <template x-for="item in items" :key="item.id">
                    <button
                        type="button"
                        @click="select(item)"
                        class="w-full text-left px-4 py-2.5 hover:bg-primary/5 transition-colors flex items-center gap-2"
                    >
                        <span
                            x-show="multiple"
                            class="w-4 h-4 rounded border flex-shrink-0 flex items-center justify-center"
                            :class="selectedMembers.some((m) => String(m.id) === String(item.id)) ? 'bg-primary border-primary text-white' : 'border-gray-300'"
                        >
                            <svg x-show="selectedMembers.some((m) => String(m.id) === String(item.id))" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <span class="block text-sm text-gray-700 truncate" x-text="item.name"></span>
                            <span class="block text-[10px] text-gray-400 font-medium truncate" x-text="item.job_position || item.structural_role || item.profession || ''"></span>
                        </div>
                    </button>
                </template>

                <div
                    x-show="hasMore"
                    class="px-4 py-2.5 text-xs text-gray-400 border-t border-gray-100 text-center"
                >
                    Ketik lebih spesifik untuk hasil lainnya
                </div>

                <div
                    x-show="items.length === 0"
                    class="px-4 py-2.5 text-sm text-gray-400 text-center"
                >
                    Tidak ditemukan
                </div>
            </div>
        </template>
    </div>
</div>
