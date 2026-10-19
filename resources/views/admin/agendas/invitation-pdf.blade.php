<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Undangan - {{ $agendaTitle }}</title>
    <style>
        /* Kertas A4 portrait, margin seimbang: kiri-kanan 2,5 cm.
           PENTING: jangan pakai selector universal (* { ... }) — di DomPDF ia
           menimpa margin @page sehingga isi menempel ke tepi kertas. */
        @page { size: A4 portrait; margin: 20mm 25mm 28mm 25mm; }
        body, p, table, tr, td, div, img { margin: 0; padding: 0; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11pt; line-height: 1.4; color: #111111; }

        /* Kop sebagai header resmi full-bleed: margin negatif menariknya ke
           tepi atas kertas (zona margin atas 20mm / sisi 25mm). In-flow
           sehingga hanya tampil di halaman 1 dan isi mengalir alami
           setelahnya tanpa tabrakan. */
        .letter-header { margin: -20mm -25mm 14pt -25mm; }
        .letter-header img { display: block; width: 100%; }

        /* Isi: ritme paragraf konsisten. Aturan turunan memakai specificity
           lebih tinggi dari .letter-body p agar tidak tertimpa shorthand-nya. */
        .letter-body p { margin: 0 0 4pt 0; }
        .letter-body p.letter-place-date { text-align: right; margin: 0 0 10pt 0; }
        table.letter-meta { width: 100%; border-collapse: collapse; margin: 4pt 0; }
        table.letter-meta td { vertical-align: top; padding: 2pt 3pt; }
        .meta-label { width: 120pt; }
        .meta-sep { width: 12pt; }
        .letter-body p.mt { margin-top: 0pt; }
        /* "Kepada Yth." → daftar nama: rapat, penerima rata kiri tanpa indent. */
        .letter-body p.yth { margin-bottom: 0; }
        .letter-body p.mt-sm { margin-top: 15pt; }
        .letter-body p.recipient { margin: 0; line-height: 1.2; }
        table.letter-schedule { margin: 6pt 0 6pt 18pt; }
        .justify { text-align: justify; }

        /* Tanda tangan: kolom kanan rata tengah. */
        table.letter-sign { width: 100%; border-collapse: collapse; margin-top: 16pt; }
        .sign-spacer { width: 50%; }
        .sign-block { text-align: center; }
        .sign-block p { margin: 0 0 4pt 0; }
        .letter-body p.sign-name { margin-top: 4pt; }
        .sign-image { display: block; height: 75pt; max-width: 170pt; margin: 4pt auto; }
        .sign-empty { height: 75pt; }
        .letter-body p.tembusan-item { margin: 0 0 4pt 18pt; }

        /* Footer: full-bleed dalam zona margin bawah (28mm) agar tak terpotong
           dan tak menimpa isi. */
        .letter-footer { position: fixed; bottom: -23mm; left: -25mm; right: -25mm; text-align: center; }
        .letter-footer img { display: block; width: 100%; }
    </style>
</head>
<body>
    @if(! empty($headerDataUri))
        <div class="letter-header">
            <img src="{{ $headerDataUri }}" alt="Kop RS AZRA">
        </div>
    @endif

    @include('admin.agendas.partials._invitation-letter-body')

    @if(! empty($footerDataUri))
        <div class="letter-footer">
            <img src="{{ $footerDataUri }}" alt="Footer RS AZRA">
        </div>
    @endif
</body>
</html>
