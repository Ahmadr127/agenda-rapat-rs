
<div class="letter-body">
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

    <p class="mt">Kepada Yth.</p>
    @foreach($recipients as $recipient)
        <p class="recipient font-bold">{{ $recipient }}</p>
    @endforeach
    <p class="mt-sm">Di tempat</p>

    <p class="mt">Dengan hormat,</p>
    <p class="justify">Bersama ini saya mengundang bapak dan ibu untuk dapat menghadiri pertemuan yang akan
        dilaksanakan pada :</p>

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

    <p class="justify mt">Mengingat pentingnya acara ini, diharapkan bapak/ibu untuk hadir tepat waktu. Atas perhatiannya
        saya ucapkan terimakasih.</p>

    <table class="letter-sign">
        <tr>
            <td class="sign-spacer"></td>
            <td class="sign-block" style="text-align:right;">
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

    <p class="mt text-[12px]">Tembusan :</p>
    @foreach($tembusan as $i => $copy)
        <p class="tembusan-item text-[12px]">{{ $i + 1 }}. {{ $copy }}</p>
    @endforeach
</div>
