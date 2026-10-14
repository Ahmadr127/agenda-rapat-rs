{{-- Time picker 24 jam kustom (HH:mm, tanpa AM/PM).
     Alasan: <input type="time"> native mengikuti locale OS/browser sehingga di
     perangkat ber-locale en-US tetap tampil 12 jam + AM/PM. Picker ini selalu
     24 jam: kolom Jam 00-23 dan Menit 00-59 dalam popover kecil di bawah input,
     plus ketik manual HH:mm. Nilai yang dikirim selalu "HH:MM".
     Variabel: $name, $id, $value ("HH:MM" atau ""), $required (bool), $hint (opsional) --}}
@php
    $tpValue = (string) ($value ?? '');
    if (! preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $tpValue)) {
        $tpValue = '';
    }
    $tpRequired = ! empty($required);
@endphp
<style>
    /* Sembunyikan scrollbar visual, scroll wheel/touchpad/touch tetap jalan. */
    .tp-scroll { scrollbar-width: none; -ms-overflow-style: none; }
    .tp-scroll::-webkit-scrollbar { display: none; }
    /* Highlight elegan terpilih (token primary), bukan abu-abu. */
    .tp-opt.tp-selected { background: #007774; color: #ffffff; font-weight: 700; }
    /* Kolom aktif pada navigasi keyboard: ring jelas. */
    .tp-list.tp-active { outline: 2px solid rgba(0, 119, 116, 0.45); outline-offset: 1px; }
    /* Penyesuaian posisi popover agar tidak terpotong viewport. */
    .tp-pop.tp-right { left: auto; right: 0; }
    .tp-pop.tp-above { top: auto; bottom: 100%; margin-top: 0; margin-bottom: 0.5rem; }
</style>
<div class="relative" data-timepicker="{{ $id }}">
    <input
        type="text"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ $tpValue }}"
        data-committed="{{ $tpValue }}"
        placeholder="JJ:MM"
        autocomplete="off"
        autocapitalize="off"
        spellcheck="false"
        inputmode="numeric"
        maxlength="5"
        pattern="([01][0-9]|2[0-3]):[0-5][0-9]"
        title="Gunakan format 24 jam JJ:MM, contoh 14:30"
        aria-expanded="false"
        aria-label="Pilih jam (format 24 jam)"
        data-timepicker-input
        class="block w-full px-4 py-3 pr-11 rounded-2xl border border-gray-200 bg-gray-50/50 text-sm tabular-nums text-gray-900 placeholder-gray-400 transition duration-200 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none dark:bg-gray-800/60 dark:border-gray-700 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:bg-gray-800"
        @if($tpRequired) required @endif
    >
    <button type="button" data-timepicker-toggle aria-label="Buka pemilih jam"
        class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-xl text-gray-400 hover:text-primary hover:bg-primary/10 flex items-center justify-center transition-colors dark:text-gray-500 dark:hover:text-primary-200">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </button>
    <div data-timepicker-popover hidden role="dialog" aria-label="Pemilih jam 24 jam"
        class="tp-pop absolute left-0 top-full z-30 mt-2 w-52 rounded-xl border border-gray-200 bg-white p-3 shadow-lg dark:bg-gray-800 dark:border-gray-700">
        <div class="grid grid-cols-2 gap-2">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400 mb-1 text-center dark:text-gray-500">Jam</p>
                <div data-timepicker-list="hour" role="listbox" aria-label="Jam 00 sampai 23"
                    class="tp-list tp-scroll relative max-h-40 overflow-y-auto rounded-lg border border-gray-100 p-1 dark:border-gray-700">
                    @for($h = 0; $h < 24; $h++)
                        @php $hh = str_pad($h, 2, '0', STR_PAD_LEFT); @endphp
                        <button type="button" tabindex="-1" data-timepicker-option data-kind="hour" data-val="{{ $hh }}"
                            class="tp-opt block w-full rounded-lg px-2 py-[5px] text-center text-sm tabular-nums text-gray-700 hover:bg-gray-100 transition-colors focus:outline-none dark:text-gray-200 dark:hover:bg-gray-700">{{ $hh }}</button>
                    @endfor
                </div>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400 mb-1 text-center dark:text-gray-500">Menit</p>
                <div data-timepicker-list="minute" role="listbox" aria-label="Menit 00 sampai 59"
                    class="tp-list tp-scroll relative max-h-40 overflow-y-auto rounded-lg border border-gray-100 p-1 dark:border-gray-700">
                    @for($m = 0; $m < 60; $m++)
                        @php $mm = str_pad($m, 2, '0', STR_PAD_LEFT); @endphp
                        <button type="button" tabindex="-1" data-timepicker-option data-kind="minute" data-val="{{ $mm }}"
                            class="tp-opt block w-full rounded-lg px-2 py-[5px] text-center text-sm tabular-nums text-gray-700 hover:bg-gray-100 transition-colors focus:outline-none dark:text-gray-200 dark:hover:bg-gray-700">{{ $mm }}</button>
                    @endfor
                </div>
            </div>
        </div>
        <div class="mt-2 flex items-center justify-between border-t border-gray-100 pt-2 dark:border-gray-700">
            <span data-timepicker-preview class="text-xs font-bold text-primary dark:text-primary-200">--:--</span>
            @if(! $tpRequired)
                <button type="button" data-timepicker-clear class="text-xs font-semibold text-rose-500 hover:text-rose-600 hover:underline">Hapus</button>
            @endif
        </div>
    </div>
</div>
@if(! empty($hint))
    <p class="text-xs text-gray-400 mt-1 dark:text-gray-500">{{ $hint }}</p>
@endif
<script>
    if (!window.__tpPickerBound) {
        window.__tpPickerBound = true;
        var TP_RE = /^([01][0-9]|2[0-3]):[0-5][0-9]$/;

        function tpWrapOf(el) {
            return el && el.closest ? el.closest('[data-timepicker]') : null;
        }

        function tpOpenPopover() {
            var pops = document.querySelectorAll('[data-timepicker-popover]');
            for (var i = 0; i < pops.length; i++) {
                if (!pops[i].hidden) return pops[i];
            }
            return null;
        }

        function tpCloseAll() {
            document.querySelectorAll('[data-timepicker-popover]').forEach(function (p) {
                p.hidden = true;
            });
            document.querySelectorAll('[data-timepicker-input]').forEach(function (inp) {
                inp.setAttribute('aria-expanded', 'false');
            });
        }

        function tpPad(n) {
            return ('0' + n).slice(-2);
        }

        function tpPaint(wrap) {
            var input = wrap.querySelector('[data-timepicker-input]');
            var cur = input ? input.value : '';
            var parts = TP_RE.test(cur) ? cur.split(':') : [];
            var h = parts[0] || '';
            var m = parts[1] || '';
            wrap.querySelectorAll('[data-timepicker-option]').forEach(function (o) {
                var on = (o.getAttribute('data-kind') === 'hour' && o.getAttribute('data-val') === h) ||
                    (o.getAttribute('data-kind') === 'minute' && o.getAttribute('data-val') === m);
                if (on) o.classList.add('tp-selected');
                else o.classList.remove('tp-selected');
            });
            var prev = wrap.querySelector('[data-timepicker-preview]');
            if (prev) prev.textContent = TP_RE.test(cur) ? cur + ' WIB' : '--:--';
        }

        function tpCenterList(list) {
            if (!list) return;
            var sel = list.querySelector('.tp-selected');
            if (sel) {
                list.scrollTop = sel.offsetTop - (list.clientHeight / 2) + (sel.clientHeight / 2);
            } else {
                list.scrollTop = 0;
            }
        }

        function tpCenter(wrap, only) {
            wrap.querySelectorAll('[data-timepicker-list]').forEach(function (list) {
                if (!only || list.getAttribute('data-timepicker-list') === only) tpCenterList(list);
            });
        }

        function tpSetActive(wrap, col) {
            wrap._tpCol = col;
            wrap.querySelectorAll('[data-timepicker-list]').forEach(function (list) {
                if (list.getAttribute('data-timepicker-list') === col) list.classList.add('tp-active');
                else list.classList.remove('tp-active');
            });
        }

        function tpCommit(wrap, input, value, silent) {
            input.value = value;
            input.dataset.committed = value;
            tpPaint(wrap);
            if (!silent) input.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function tpBaseParts(input) {
            var src = TP_RE.test(input.value) ? input.value : (input.dataset.committed || '');
            if (!TP_RE.test(src)) return null;
            var p = src.split(':');
            return { h: parseInt(p[0], 10), m: parseInt(p[1], 10) };
        }

        function tpPlace(wrap, pop) {
            pop.classList.remove('tp-right', 'tp-above');
            var r = pop.getBoundingClientRect();
            if (r.right > window.innerWidth - 8) pop.classList.add('tp-right');
            var r2 = pop.getBoundingClientRect();
            if (r2.bottom > window.innerHeight - 8) {
                var input = wrap.querySelector('[data-timepicker-input]');
                var ir = input ? input.getBoundingClientRect() : null;
                if (ir && ir.top >= r2.height + 16) pop.classList.add('tp-above');
            }
        }

        function tpOpen(wrap) {
            var input = wrap.querySelector('[data-timepicker-input]');
            var pop = wrap.querySelector('[data-timepicker-popover]');
            if (!input || !pop) return;
            tpCloseAll();
            tpSetActive(wrap, wrap._tpCol || 'hour');
            tpPaint(wrap);
            pop.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            tpPlace(wrap, pop);
            tpCenter(wrap);
        }

        // --- Klik: buka/tutup, pilih angka, hapus ---
        document.addEventListener('click', function (e) {
            var t = e.target && e.target.closest ? e.target : null;
            var wrap = t ? tpWrapOf(t) : null;
            if (!wrap) {
                tpCloseAll();
                return;
            }
            var input = wrap.querySelector('[data-timepicker-input]');
            var pop = wrap.querySelector('[data-timepicker-popover]');
            if (!input || !pop) return;

            var clr = t.closest('[data-timepicker-clear]');
            if (clr && pop.contains(clr)) {
                tpCommit(wrap, input, '');
                tpCloseAll();
                input.focus();
                return;
            }

            var opt = t.closest('[data-timepicker-option]');
            if (opt && pop.contains(opt)) {
                var kind = opt.getAttribute('data-kind');
                var val = opt.getAttribute('data-val');
                var base = tpBaseParts(input) || { h: 0, m: 0 };
                if (kind === 'hour') base.h = parseInt(val, 10);
                else base.m = parseInt(val, 10);
                tpCommit(wrap, input, tpPad(base.h) + ':' + tpPad(base.m));
                // Pilih jam -> tetap buka untuk menit; pilih menit -> tutup.
                if (kind === 'hour') {
                    tpSetActive(wrap, 'minute');
                    tpCenter(wrap, 'minute');
                } else {
                    tpCloseAll();
                }
                return;
            }

            // Klik field / ikon jam -> buka-tutup popover.
            if (pop.hidden) tpOpen(wrap);
            else tpCloseAll();
        });

        // --- Ketik manual HH:mm (mask digit progresif) ---
        document.addEventListener('input', function (e) {
            var input = e.target && e.target.matches && e.target.matches('[data-timepicker-input]') ? e.target : null;
            if (!input) return;
            var d = input.value.replace(/\D/g, '').slice(0, 4);
            input.value = d.length > 2 ? d.slice(0, 2) + ':' + d.slice(2) : d;
        });

        // --- Validasi ketat saat selesai mengetik (blur/Enter) ---
        document.addEventListener('change', function (e) {
            var input = e.target && e.target.matches && e.target.matches('[data-timepicker-input]') ? e.target : null;
            if (!input) return;
            var wrap = tpWrapOf(input);
            if (TP_RE.test(input.value)) {
                // Nilai valid: catat sebagai committed (tanpa dispatch ulang).
                tpCommit(wrap, input, input.value, true);
            } else {
                // Nilai invalid: kembalikan ke nilai valid terakhir.
                var back = input.dataset.committed || '';
                if (input.value !== back) {
                    input.value = back;
                    tpPaint(wrap);
                }
            }
        });

        // --- Keyboard: buka, navigasi panah, Enter commit, Escape tutup ---
        document.addEventListener('keydown', function (e) {
            var field = e.target && e.target.matches && e.target.matches('[data-timepicker-input]') ? e.target : null;
            var open = tpOpenPopover();

            if (e.key === 'Escape') {
                if (open) tpCloseAll();
                return;
            }

            if (!open) {
                if (field && (e.key === 'Enter' || e.key === 'ArrowDown')) {
                    e.preventDefault();
                    tpOpen(tpWrapOf(field));
                }
                return;
            }

            var wrap = tpWrapOf(open);
            if (!wrap) return;
            if (field !== wrap.querySelector('[data-timepicker-input]') && !open.contains(e.target)) return;
            var input = wrap.querySelector('[data-timepicker-input]');
            var col = wrap._tpCol || 'hour';

            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                tpSetActive(wrap, 'hour');
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                tpSetActive(wrap, 'minute');
            } else if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                e.preventDefault();
                var dir = e.key === 'ArrowUp' ? 1 : -1;
                var base = tpBaseParts(input) || { h: 0, m: 0 };
                if (col === 'hour') base.h = (base.h + dir + 24) % 24;
                else base.m = (base.m + dir + 60) % 60;
                tpCommit(wrap, input, tpPad(base.h) + ':' + tpPad(base.m));
                tpCenter(wrap, col);
            } else if (e.key === 'Enter') {
                // Commit ketikan manual bila valid, lalu tutup (cegah submit form).
                e.preventDefault();
                if (TP_RE.test(input.value)) tpCommit(wrap, input, input.value, true);
                else {
                    input.value = input.dataset.committed || '';
                    tpPaint(wrap);
                }
                tpCloseAll();
            } else if (e.key === 'Tab') {
                tpCloseAll();
            }
        });
    }
</script>
