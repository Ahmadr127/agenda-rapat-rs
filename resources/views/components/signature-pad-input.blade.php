{{-- Input TTD digital reusable: kanvas goresan + alternatif upload PNG.
     Nilai kanvas tersimpan sebagai dataURL pada hidden input.
     Props: canvasId (wajib, unik per halaman), inputName, fileName,
     removeName, existingUrl, existingLabel.
--}}
@props([
    'canvasId',
    'inputName' => 'signature_data',
    'fileName' => 'signature',
    'removeName' => 'remove_signature',
    'existingUrl' => null,
    'existingLabel' => 'Tanda tangan tersimpan',
])
<div x-data="officialSignaturePad('{{ $canvasId }}')" x-init="init()" class="space-y-2">
    @if($existingUrl)
        <div>
            <p class="text-xs font-semibold text-gray-500 mb-1">{{ $existingLabel }}</p>
            <img src="{{ $existingUrl }}" alt="{{ $existingLabel }}" class="h-16 object-contain bg-white border border-gray-200 rounded-xl px-3 py-1 mb-2">
            <label class="inline-flex items-center gap-2 text-xs font-semibold text-rose-600 cursor-pointer">
                <input type="checkbox" name="{{ $removeName }}" value="1" @checked(old($removeName)) class="w-4 h-4 rounded text-rose-600 border-gray-300 focus:ring-rose-500">
                Hapus tanda tangan tersimpan
            </label>
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
        <canvas id="{{ $canvasId }}" class="block w-full cursor-crosshair" style="height: 180px; touch-action: none;"></canvas>
    </div>
    <input type="hidden" name="{{ $inputName }}" id="{{ $canvasId }}-data" value="">

    <div class="flex flex-wrap items-center gap-2">
        <button type="button" data-pad-clear class="px-3 py-2 rounded-xl bg-gray-100 text-gray-600 text-xs font-semibold hover:bg-gray-200 transition-colors">Bersihkan kanvas</button>
        <label class="inline-flex items-center gap-2 text-xs font-semibold text-primary cursor-pointer hover:underline">
            atau upload PNG
            <input type="file" name="{{ $fileName }}" accept=".png,.jpg,.jpeg" data-pad-file class="block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
        </label>
    </div>
    <p class="text-xs text-gray-400">Gambar tanda tangan di kanvas, atau upload file PNG transparan. Menggambar akan mengosongkan pilihan file dan sebaliknya.</p>
</div>
<script>
    // Pabrik komponen Alpine untuk kanvas TTD (didefinisikan sekali walau
    // partial di-include berulang; state milik tiap x-data).
    if (!window.__officialPadDefined) {
        window.__officialPadDefined = true;
        window.officialSignaturePad = function (canvasId) {
            return {
                pad: null,
                init() {
                    const canvas = document.getElementById(canvasId);
                    if (!canvas || !window.SignaturePad) return;
                    const box = canvas.parentElement;
                    const rect = box.getBoundingClientRect();
                    canvas.width = Math.max(rect.width || 300, 300);
                    canvas.height = 180;
                    const hidden = document.getElementById(canvasId + '-data');
                    const fileInput = this.$el.querySelector('[data-pad-file]');
                    const clearBtn = this.$el.querySelector('[data-pad-clear]');
                    this.pad = new window.SignaturePad(canvas, {
                        backgroundColor: 'rgb(255, 255, 255)',
                        penColor: 'rgb(17, 24, 39)',
                    });
                    const sync = () => {
                        if (hidden) hidden.value = this.pad.isEmpty() ? '' : this.pad.toDataURL('image/png');
                    };
                    // signature_pad v5 memakai CustomEvent, bukan opsi onBegin/onEnd.
                    this.pad.addEventListener('beginStroke', () => {
                        if (fileInput) fileInput.value = '';
                    });
                    this.pad.addEventListener('endStroke', sync);
                    // Pengaman: sinkronkan juga saat form disubmit.
                    const form = canvas.closest('form');
                    if (form) form.addEventListener('submit', sync);
                    if (clearBtn) clearBtn.addEventListener('click', () => {
                        this.pad.clear();
                        if (hidden) hidden.value = '';
                    });
                    if (fileInput) fileInput.addEventListener('change', () => {
                        if (fileInput.files && fileInput.files.length > 0) {
                            this.pad.clear();
                            if (hidden) hidden.value = '';
                        }
                    });
                },
            };
        };
    }
</script>
