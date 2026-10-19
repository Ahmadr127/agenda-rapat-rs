{{-- ============================================================
     ISI SURAT UNDANGAN OTOMATIS (dipakai pratinjau HTML & PDF).
     Struktur modular per blok; properti layout kritis (lebar kolom,
     table-layout, alignment, ukuran gambar) ditulis inline agar
     IDENTIK di DomPDF & browser. Ritme tipografi mengikuti
     stylesheet tiap konteks (pt = PDF, px = pratinjau).
     Tanpa flexbox/grid/spasi manual.
     Variabel: $letterPlaceDate, $letterNumber, $recipients,
     $eventDayDate, $timeRange, $location, $agendaTitle,
     $leaderName, $leaderTitle, $signatureDataUri, $tembusan.
     Blok 1 (kop/header) ada di wrapper: invitation-pdf.blade.php
     (PDF) dan invitation-preview.blade.php (pratinjau browser).
     ============================================================ --}}
<div class="letter-body">
    {{-- 2. Tanggal, nomor & perihal --}}
    <p class="letter-place-date">{{ $letterPlaceDate }}</p>

    <table class="letter-meta">
        <tr>
            <td class="meta-label">No</td>
            <td class="meta-sep">:</td>
            <td>{{ $letterNumber }}</td>
        </tr>
        <tr>
            <td class="meta-label">Hal</td>
            <td class="meta-sep">:</td>
            <td>Undangan</td>
        </tr>
    </table>

    {{-- 3. Penerima & salam pembuka --}}
    <p class="mt yth">Kepada Yth.</p>
    @foreach($recipients as $recipient)
        <p class="recipient">{{ $recipient }}</p>
    @endforeach
    {{-- Jarak "Daftar Penerima → Di tempat" diatur penuh oleh .mt-sm di
         stylesheet tiap konteks (tanpa <br> agar ritme terkendali & rapat). --}}
    {{-- <br> --}}
    <p class="mt-sm">Di tempat</p>

    <p class="mt">Dengan hormat,</p>

    {{-- 4. Isi undangan --}}
    <p class="justify">Bersama ini saya mengundang bapak dan ibu untuk dapat menghadiri pertemuan yang akan
        dilaksanakan pada :</p>

    {{-- 5. Detail rapat: kolom label & separator eksplisit, fixed layout --}}
    <table class="letter-meta letter-schedule">
        <tr>
            <td class="meta-label">Hari/Tanggal</td>
            <td class="meta-sep">:</td>
            <td>{{ $eventDayDate }}</td>
        </tr>
        <tr>
            <td class="meta-label">Pukul</td>
            <td class="meta-sep">:</td>
            <td>{{ $timeRange }}</td>
        </tr>
        <tr>
            <td class="meta-label">Lokasi</td>
            <td class="meta-sep">:</td>
            <td>{{ $location }}</td>
        </tr>
        <tr>
            <td class="meta-label">Agenda</td>
            <td class="meta-sep">:</td>
            <td>{{ $agendaTitle }}</td>
        </tr>
    </table>

    {{-- 6. Paragraf penutup --}}
    <p class="justify mt">Mengingat pentingnya acara ini, diharapkan bapak/ibu untuk hadir tepat waktu. Atas perhatiannya
        saya ucapkan terimakasih.</p>

    {{-- 7. Blok tanda tangan: SATU komponen struktural — kolom kanan
         eksplisit 50/50 (fixed layout) sehingga judul, gambar TTD,
         dan nama berbagi batas kanan yang sama. Gambar dibungkus
         wrapper rata kanan (tanpa margin otomatis) dengan ukuran
         ratio-safe agar proporsi aslinya terjaga. --}}
    <table class="letter-sign">
        <tr>
            <td class="sign-spacer" style="width:50%;"></td>
            <td class="sign-block" style="width:50%;text-align:right;vertical-align:top;">
                <p>{{ $leaderTitle }},</p>
                @if(! empty($signatureDataUri))
                    <div style="text-align:right;margin:4pt 0;">
                        <img src="{{ $signatureDataUri }}" alt="Tanda tangan {{ $leaderName }}" class="sign-image" style="display:inline;width:auto;height:auto;max-width:170pt;max-height:75pt;">
                    </div>
                @else
                    <div class="sign-empty"></div>
                @endif
                <p class="sign-name font-bold"><u>{{ $leaderName }}</u></p>
            </td>
        </tr>
    </table>

    {{-- 8. Tembusan --}}
    <p class="mt text-[12px]">Tembusan :</p>
    @foreach($tembusan as $i => $copy)
        <p class="tembusan-item text-[12px]">{{ $i + 1 }}. {{ $copy }}</p>
    @endforeach
</div>
