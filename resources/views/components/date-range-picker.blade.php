@props([
    'dateFrom' => null,
    'dateTo' => null,
    'nameFrom' => 'date_from',
    'nameTo' => 'date_to',
])

<div
    x-data="{
        open: false,
        startDate: {{ $dateFrom ? "'" . $dateFrom . "'" : 'null' }},
        endDate: {{ $dateTo ? "'" . $dateTo . "'" : 'null' }},
        hoverDate: null,
        selecting: false,
        viewYear: null,
        viewMonth: null,
        activePreset: null,
        showCalendar: false,

        presets: [
            { key: 'today',     label: 'Hari ini' },
            { key: 'last7',     label: '7 hari terakhir' },
            { key: 'last30',    label: '30 hari terakhir' },
            { key: 'thisMonth', label: 'Bulan ini' },
            { key: 'lastMonth', label: 'Bulan lalu' },
            { key: 'custom',    label: 'Kustom' },
        ],

        get todayStr() {
            const d = new Date();
            return this.ymd(d.getFullYear(), d.getMonth() + 1, d.getDate());
        },

        init() {
            const ref = this.startDate ? new Date(this.startDate + 'T00:00:00') : new Date();
            this.viewYear = ref.getFullYear();
            this.viewMonth = ref.getMonth() + 1;
            if (this.startDate) {
                this.activePreset = 'custom';
                this.showCalendar = false;
            }
        },

        ymd(y, m, d) {
            return `${y}-${String(m).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
        },

        get monthLabel() {
            return new Date(this.viewYear, this.viewMonth - 1, 1)
                .toLocaleString('id-ID', { month: 'long', year: 'numeric' });
        },

        prevMonth() {
            if (this.viewMonth === 1) { this.viewMonth = 12; this.viewYear--; }
            else this.viewMonth--;
        },

        nextMonth() {
            if (this.viewMonth === 12) { this.viewMonth = 1; this.viewYear++; }
            else this.viewMonth++;
        },

        get days() {
            const firstDay = new Date(this.viewYear, this.viewMonth - 1, 1).getDay();
            const daysInMonth = new Date(this.viewYear, this.viewMonth, 0).getDate();
            const cells = [];
            for (let i = 0; i < firstDay; i++) cells.push(null);
            for (let d = 1; d <= daysInMonth; d++) cells.push(d);
            return cells;
        },

        applyPreset(key) {
            const now = new Date();
            const today = this.todayStr;
            this.activePreset = key;

            if (key === 'custom') {
                this.showCalendar = true;
                this.startDate = null;
                this.endDate = null;
                this.selecting = false;
                return;
            }

            this.showCalendar = false;

            if (key === 'today') {
                this.startDate = today;
                this.endDate = today;
            } else if (key === 'last7') {
                const d = new Date(now); d.setDate(d.getDate() - 6);
                this.startDate = this.ymd(d.getFullYear(), d.getMonth() + 1, d.getDate());
                this.endDate = today;
            } else if (key === 'last30') {
                const d = new Date(now); d.setDate(d.getDate() - 29);
                this.startDate = this.ymd(d.getFullYear(), d.getMonth() + 1, d.getDate());
                this.endDate = today;
            } else if (key === 'thisMonth') {
                this.startDate = this.ymd(now.getFullYear(), now.getMonth() + 1, 1);
                this.endDate = today;
            } else if (key === 'lastMonth') {
                const first = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                const last  = new Date(now.getFullYear(), now.getMonth(), 0);
                this.startDate = this.ymd(first.getFullYear(), first.getMonth() + 1, 1);
                this.endDate   = this.ymd(last.getFullYear(),  last.getMonth() + 1,  last.getDate());
            }

            this.open = false;
        },

        selectDay(d) {
            if (!d) return;
            const picked = this.ymd(this.viewYear, this.viewMonth, d);
            if (!this.selecting || (this.startDate && this.endDate)) {
                this.startDate = picked;
                this.endDate = null;
                this.selecting = true;
            } else {
                if (picked < this.startDate) {
                    this.endDate = this.startDate;
                    this.startDate = picked;
                } else {
                    this.endDate = picked;
                }
                this.selecting = false;
                this.open = false;
            }
        },

        dayState(d) {
            if (!d) return { isStart: false, isEnd: false, inRange: false, isToday: false };
            const date = this.ymd(this.viewYear, this.viewMonth, d);
            const end  = this.endDate || this.hoverDate;
            const lo   = this.startDate && end ? [this.startDate, end].sort()[0] : null;
            const hi   = this.startDate && end ? [this.startDate, end].sort()[1] : null;
            return {
                isStart: date === this.startDate,
                isEnd:   date === (this.endDate || (this.selecting ? this.hoverDate : null)),
                inRange: lo && hi && date > lo && date < hi,
                isToday: date === this.todayStr,
            };
        },

        get displayLabel() {
            const labels = {
                today:     'Hari ini',
                last7:     '7 hari terakhir',
                last30:    '30 hari terakhir',
                thisMonth: 'Bulan ini',
                lastMonth: 'Bulan lalu',
            };
            if (this.activePreset && labels[this.activePreset]) return labels[this.activePreset];
            if (!this.startDate) return 'Pilih rentang tanggal';
            const fmt = s => new Date(s + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            if (!this.endDate) return fmt(this.startDate) + ' – ...';
            return fmt(this.startDate) + ' – ' + fmt(this.endDate);
        },

        clear() {
            this.startDate = null;
            this.endDate = null;
            this.selecting = false;
            this.hoverDate = null;
            this.activePreset = null;
            this.showCalendar = false;
        },
    }"
    x-init="init()"
    @click.outside="open = false"
    class="relative shrink-0"
>
    {{-- Hidden inputs for form submission --}}
    <input type="hidden" name="{{ $nameFrom }}" :value="startDate ?? ''">
    <input type="hidden" name="{{ $nameTo }}" :value="endDate ?? ''">

    {{-- Trigger button --}}
    <div
        @click="open = !open"
        class="flex items-center gap-2 px-4 py-3 rounded-2xl border border-gray-200 bg-white text-sm text-gray-700 hover:border-gray-300 transition-colors cursor-pointer select-none"
    >
        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
        </svg>
        <span x-text="displayLabel" class="whitespace-nowrap"></span>
        <button
            type="button"
            x-show="startDate || activePreset"
            @click.stop="clear()"
            class="ml-1 text-gray-400 hover:text-gray-600 leading-none"
        >&times;</button>
    </div>

    {{-- Dropdown --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 bg-white border border-gray-200 rounded-2xl shadow-lg flex"
        style="display:none"
    >
        {{-- Shortcuts sidebar --}}
        <div class="py-2 w-40 shrink-0" :class="showCalendar ? 'border-r border-gray-100' : ''">
            <template x-for="preset in presets" :key="preset.key">
                <button
                    type="button"
                    @click="applyPreset(preset.key)"
                    :class="activePreset === preset.key
                        ? 'bg-blue-50 text-blue-600 font-semibold'
                        : 'text-gray-600 hover:bg-gray-50'"
                    class="w-full text-left px-4 py-2 text-sm transition-colors flex items-center justify-between gap-2"
                >
                    <span x-text="preset.label"></span>
                    <svg x-show="activePreset === preset.key" class="w-3.5 h-3.5 shrink-0 text-blue-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                </button>
            </template>
        </div>

        {{-- Calendar panel (Custom only) --}}
        <div x-show="showCalendar" class="p-4 w-72" style="display:none">
            {{-- Month navigation --}}
            <div class="flex items-center justify-between mb-3">
                <button type="button" @click="prevMonth()" class="p-1 rounded-lg hover:bg-gray-100 text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <span class="text-sm font-semibold text-gray-800 capitalize" x-text="monthLabel"></span>
                <button type="button" @click="nextMonth()" class="p-1 rounded-lg hover:bg-gray-100 text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            {{-- Day-of-week headers --}}
            <div class="grid grid-cols-7 mb-1">
                <template x-for="h in ['Min','Sen','Sel','Rab','Kam','Jum','Sab']">
                    <div class="text-center text-xs text-gray-400 font-medium py-1" x-text="h"></div>
                </template>
            </div>

            {{-- Day cells --}}
            <div class="grid grid-cols-7 gap-y-0.5">
                <template x-for="(d, i) in days" :key="i">
                    <div
                        class="relative flex items-center justify-center h-8"
                        :class="{
                            'bg-blue-100': d && dayState(d).inRange,
                            'rounded-l-full': d && dayState(d).inRange && (i % 7 === 0 || dayState(d).isStart),
                            'rounded-r-full': d && dayState(d).inRange && (i % 7 === 6 || dayState(d).isEnd),
                        }"
                        @mouseover="if (d && selecting) hoverDate = ymd(viewYear, viewMonth, d)"
                        @mouseleave="hoverDate = null"
                    >
                        <button
                            type="button"
                            x-show="d !== null"
                            @click="selectDay(d)"
                            :class="{
                                'bg-blue-600 text-white hover:bg-blue-700': dayState(d).isStart || dayState(d).isEnd,
                                'ring-1 ring-blue-400': dayState(d).isToday && !dayState(d).isStart && !dayState(d).isEnd,
                                'text-gray-700 hover:bg-gray-100': !dayState(d).isStart && !dayState(d).isEnd,
                            }"
                            class="w-8 h-8 rounded-full text-sm font-medium transition-colors z-10 relative"
                            x-text="d"
                        ></button>
                    </div>
                </template>
            </div>

            <p class="mt-3 text-xs text-gray-400 text-center" x-text="selecting ? 'Pilih tanggal akhir' : 'Pilih tanggal awal'"></p>
        </div>
    </div>
</div>
