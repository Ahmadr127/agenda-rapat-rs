@props([
    'name',
    'searchUrl',
    'selectedId' => null,
    'selectedLabel' => null,
    'placeholder' => 'Cari...',
    'required' => false,
    'bgColor' => 'bg-gray-50/50',
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

        notifyChange() {
            this.$dispatch('searchable-select-change', {
                name: this.name,
                value: this.selectedId,
                label: this.selectedLabel,
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

        select(item) {
            this.selectedId = item.id;
            this.search = item.name;
            this.selectedLabel = item.name;
            this.open = false;
            this.$refs.searchInput.setCustomValidity('');
            this.notifyChange();
        },

        onInput() {
            this.selectedId = '';
            this.selectedLabel = '';
            this.open = true;
            this.$refs.searchInput.setCustomValidity(this.requiredMessage);
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
                if (!this.selectedId) this.search = '';
                this.open = false;
            }, 200);
        },

        init() {
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
    <input type="hidden" name="{{ $name }}" :value="selectedId">

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

    <div
        x-show="open && (loading || fetched)"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-2xl shadow-lg max-h-48 overflow-y-auto"
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
                <template x-for="item in items" :key="item.id">
                    <button
                        type="button"
                        @click="select(item)"
                        class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-primary/5 hover:text-primary transition-colors"
                        x-text="item.name"
                    ></button>
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
