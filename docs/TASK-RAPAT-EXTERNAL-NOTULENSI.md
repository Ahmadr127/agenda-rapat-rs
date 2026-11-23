# Task — Rapat Internal/Eksternal, Anggota Rapat & Notulensi

Status: **IN PROGRESS** — fase 1 selesai, fase 2–6 sedang dikerjakan.
Legenda: `[ ]` belum · `[~]` dikerjakan · `[x]` selesai · `[!]` terblokir keputusan

## Konfirmasi Alur (sudah dikunci)

1. Scope hanya untuk tipe **rapat**.
2. Domain internal = semua agenda; domain eksternal = hanya agenda `external`; host tidak dikenal = internal.
3. Anggota disimpan via reuse pivot `agenda_employee`; roster kosong = semua pegawai boleh absen.
4. Admin bisa edit notulensi kapan saja.
5. Notulensi publik: siapa pun dengan link (tetap, hari-H).
6. Link publik mengikuti domain sesuai scope agenda.

## Fase 0 — Konfirmasi

- [x] D1 — `scope` hanya untuk `rapat`
- [x] D2 — isi daftar publik di domain eksternal & internal
- [x] D3 — link publik mengikuti domain scope
- [x] D4 — model anggota: reuse `agenda_employee`
- [x] D5 — agenda tanpa anggota → semua pegawai boleh absen
- [x] D6 — edit notulensi admin kapan saja
- [x] D7 — notulensi publik: siapa pun dengan link
- [x] D8 — host tidak dikenal = internal

## Fase 1 — Skema & Konfigurasi

- [x] Migrasi `agendas.scope` (string 20, default `internal`, index) + `agendas.has_members` (boolean)
- [ ] Backfill data anggota untuk agenda lama (diputuskan: tidak diperlukan, fallback semua pegawai)
- [x] `config/agenda.php` (internal_domain, external_domain, default_scope)
- [x] `.env.example` + `.env` (3 variabel AGENDA_*)
- [x] `app/Support/AgendaScope.php`
- [ ] Jalankan migrasi di lingkungan target

## Fase 2 — Form Agenda (Create & Edit)

- [x] `AgendaRequest`: rule `scope` + `member_ids` (+ pesan)
- [x] `AgendaController@create/edit`: kirim daftar pegawai + nilai lama
- [x] `create.blade.php`: radio Lingkup (default Internal) + section Anggota Rapat (searchable select multi)
- [x] `edit.blade.php`: radio Lingkup + nilai lama + searchable select multi anggota
- [x] `AgendaController@store`: simpan `scope` + sync anggota
- [x] `AgendaController@update`: simpan `scope` + sync anggota
- [ ] `AgendaController@index`: filter `scope` + badge di tabel (badge done; filter belum)

## Fase 3 — Domain & Halaman Publik

- [x] `PublicAgendaController@index`: filter berdasarkan `AgendaScope::fromHost(request()->host())`
- [x] `public/agenda-today.blade.php`: badge Internal/Eksternal
- [x] `components/agenda-header.blade.php`: badge lingkup
- [x] `resources/views/attendance/*.blade.php`, `public/agenda-image-input.blade.php`: link biasa (route)
- [ ] Test: host internal vs eksternal menampilkan daftar yang benar

## Fase 4 — Anggota & Absensi

- [x] `Agenda` model: `hasMemberRoster()`
- [x] `AttendanceController@show`: daftar pencarian hanya anggota agenda
- [x] `AttendanceController@sign`: tolak 403 bila bukan anggota
- [x] `AttendanceService`: koreksi roster-aware (jangan hapus anggota)
- [ ] `attendance/show.blade.php`: teks kosong disesuaikan (tergantung UI)
- [ ] Test: non-anggota 403, anggota bisa TTD, anggota tanpa TTD tampil "-"

## Fase 5 — Notulensi Admin

- [x] Route `PUT /admin/agendas/{agenda}/notes/{note}` → `admin.agendas.notes.update`
- [x] `AgendaController@updateNote` + otorisasi
- [x] `show.blade.php`: tombol Edit per baris + form inline/modal (inline table row)
- [x] Validasi notulensi sama dengan publik (via `App\Support\AgendaNote`)
- [ ] Route tambah/hapus notulensi admin (opsional, di luar permintaan)

## Fase 6 — Notulensi Publik (Satu Tabel)

- [x] `agenda-note-input.blade.php`: satu tabel `No | Topik | Pembahasan | Kesimpulan | PJ | Waktu | Aksi`
- [x] Tombol **Edit** per baris → baris berubah jadi input inline + Simpan/Batal
- [x] Tombol **Tambah Notulensi** → baris input baru di tabel
- [x] State Alpine: `editingId`, `saveEdit`, `saveCreate`, `startCreate`, `startEdit`, `cancelEdit`
- [x] Tetap memakai `agenda.note.store` / `agenda.note.update` / `agenda.note.destroy`
- [ ] Validasi anggota di controller publik (diputuskan: tidak, siapa pun dengan link)

## Fase 7 — Export & Polish

- [ ] `export-pdf.blade.php`: baris **Lingkup** di header (opsional)
- [ ] `exportCsv`: kolom Lingkup (opsional)
- [ ] Jalankan: `php artisan test --compact`, `vendor/bin/pint --dirty --format agent`, `npm run build`, `php artisan migrate`

## Definition of Done

- Agenda baru default `internal`, bisa dipilih `external`.
- Halaman publik memfilter agenda sesuai domain yang diakses.
- Hanya anggota rapat yang bisa tanda tangan kehadiran (diverifikasi di server, bukan hanya UI).
- Admin bisa edit notulensi dari detail agenda.
- Form & daftar notulensi publik menjadi satu tabel dengan edit inline.
- Semua test lama tetap hijau + test baru untuk aturan di atas.

## Di Luar Scope (kecuali diminta)

- Perubahan desain surat undangan.
- Import anggota dari file Excel.
- Notifikasi/email undangan ke anggota.
- Perubahan mekanisme pretest/posttest.
