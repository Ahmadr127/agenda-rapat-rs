# Plan — Rapat Internal/Eksternal, Anggota Rapat & Notulensi

Status: **IN PROGRESS** — fase 1 (skema & config) sudah selesai; fase 2+ sedang dikerjakan.
Tanggal: 2026-11-23

---

## 1. Analisa Logic Saat Ini

### 1.1 Form Buat Agenda

Alur: `GET /admin/agendas/create` → `AgendaController@create` → `resources/views/admin/agendas/create.blade.php`
→ `POST /admin/agendas` → `AgendaRequest` → `AgendaController@store`.

- Tipe agenda: `rapat`, `diklat`, `pelatihan`. Dibatasi permission `agendas.type-*` lewat `App\Support\AgendaTypeAccess`.
- Field tersimpan: `title`, `description`, `unit_id`, `event_date`, `event_time`, `event_end_time`,
  `event_leader_id`, `room_id`, `letter_number`, `letter_recipients`, `letter_file_path`,
  `material_file_path`, `type`, `bank_soal_id`.
- Khusus `rapat`: surat undangan digenerate otomatis (`InvitationLetterService`), `letter_number` auto-suggest.
- **Tidak ada konsep lingkup internal/eksternal.**
- **Tidak ada daftar anggota rapat.** Siapa pun (semua `employees`) bisa absen di halaman publik.
- Form create dan edit adalah dua file terpisah yang isinya hampir sama (`create.blade.php` 384 baris, `edit.blade.php` 413 baris) — setiap perubahan field harus dikerjakan di dua tempat.

### 1.2 Absensi Publik

- `GET /absen/{agenda}` → `AttendanceController@show`, hanya bisa diakses **hari-H** (`event_date->isToday()`).
- Daftar yang bisa dicari: `Employee::with('unit')->orderBy('full_name')->get()` → **seluruh pegawai**.
- `POST /absen/{agenda}/sign`: validasi hanya `employee_id => exists:employees,id` dan file tanda tangan.
  Artinya pegawai mana pun bisa menandatangani kehadiran agenda mana pun.
- Baris pivot `agenda_employee` **dibuat saat orang menandatangani** (`attach`), bukan saat agenda dibuat.
- `signature_image_path` null = belum hadir; terisi = hadir.
- `DELETE /absen/{agenda}/sign` menghapus baris pivot (koreksi panitia), terkunci setelah jam selesai.

### 1.3 Notulensi

- Tabel `agenda_notes`: `agenda_id`, `topic`, `decision`, `remarks` (nullable), `pj`, timestamps.
- Hanya untuk tipe `rapat` (`Agenda::allowsNotes()`).
- **Detail admin** (`admin/agendas/show.blade.php`) sekarang: tabel notulensi + tombol **Edit** inline per baris
  (route `PUT admin/agendas/{agenda}/notes/{note}`).
- **Halaman publik** (`/agenda/{agenda}/note`, `PublicAgendaInputController`): kini **satu tabel** dengan kolom
  `No | Topik | Pembahasan | Kesimpulan | PJ | Waktu | Aksi` (bentuk sama dengan export PDF), tombol **Edit** inline
  per baris, tombol **Tambah Notulensi** untuk baris baru, tombol **Hapus** per baris.
- **Export PDF** (`admin/agendas/export-pdf.blade.php` baris 60–89) sudah memakai tabel:
  `No | Topik | Pembahasan | Kesimpulan | PJ | Waktu`.

### 1.4 Halaman Publik (Daftar Agenda)

- `GET /` → `PublicAgendaController@index`: semua agenda **hari ini**, semua tipe, tanpa filter domain/lingkup.
- Kartu agenda menampilkan tipe + jumlah `signed_count`, lalu klik menuju `/absen/{agenda}`.

### 1.5 RBAC & Policy

- Permission relevan: `agendas.manage`, `agendas.manage-all`, `agendas.type-rapat`, `agendas.type-diklat`, `agendas.type-pelatihan`
  (`app/Models/Permission.php`).
- `AgendaPolicy@update`: `agendas.manage-all`, atau `agendas.manage` + unit sama.
- Tidak ada permission khusus notulensi/anggota.

### 1.6 Konfigurasi Domain

- `.env` saat ini hanya punya `APP_URL`, `SESSION_DOMAIN`.
- Sudah ada `config/agenda.php` + variabel `AGENDA_INTERNAL_DOMAIN`, `AGENDA_EXTERNAL_DOMAIN`, `AGENDA_DEFAULT_SCOPE`.

---

## 2. Rancangan Final (sesudah konfirmasi alur)

### 2.1 Lingkup Agenda (internal/eksternal)

- Kolom baru `agendas.scope` (`string(20)`, default `internal`, index).
- Kolom pendukung `agendas.has_members` (`boolean`, default `false`): true = agenda rapat punya roster anggota definitif.
- Form create & edit: radio **Lingkup**: Internal (default) / Eksternal — tampil untuk tipe `rapat`.
- Validasi `AgendaRequest`: `scope => nullable|in:internal,external`; `member_ids => nullable|array`,
  `member_ids.* => nullable|distinct|exists:employees,id`.
- Label UI: "Rapat Internal" / "Rapat Eksternal".

### 2.2 Domain Internal/Eksternal (`.env`)

```dotenv
AGENDA_INTERNAL_DOMAIN=https://rsazra.local
AGENDA_EXTERNAL_DOMAIN=https://agenda.rsazra.co.id
AGENDA_DEFAULT_SCOPE=internal
```

- `App\Support\AgendaScope`: `fromHost(?string $host)`, `label(string $scope)`, `domainFor(string $scope)`,
  `urlFor(Agenda $agenda, string $routeName, array $params = [])`.

### 2.3 Anggota Rapat

- **Reused pivot `agenda_employee`** sebagai daftar anggota (sudah diputuskan, D4 = Opsi A).
- Admin memilih anggota via checkbox list (`member_ids[]`) pada form rapat; roster kosong = agenda terbuka untuk semua pegawai.
- `AgendaController@store/update` → `syncAgendaMembers()`:
  - roster kosong → detach semua + `has_members = false` (buka akses);
  - roster terisi → `has_members = true`, detach non-anggota yang belum menandatangani, pertahankan riwayat TTD,
    attach anggota baru.
- `AttendanceController@show`: bila `hasMemberRoster()` → daftar pencarian = anggota agenda; else semua pegawai.
- `AttendanceController@sign`: bila roster → 403 bila `employee_id` bukan anggota (verifikasi server-side).
- `AttendanceService::removeAttendee`: bila roster → koreksi hanya nolkan signature + hapus jawaban kuis,
  anggota tetap tercatat (tidak hapus pivot).

### 2.4 Edit Notulensi di Detail Admin

- Route: `PUT /admin/agendas/{agenda}/notes/{note}` → `AgendaController@updateNote` (Gate `update` + scope note).
- Inline edit di tabel notulensi (kolom baru **Aksi** dengan tombol Edit/Simpan/Batal).
- Admin bisa edit **kapan saja** (setelah rapat selesai) — keputusan D6 = (a).

### 2.5 Form Input Notulensi Publik → Satu Tabel

- Kolom: `No | Topik | Pembahasan | Kesimpulan | PJ | Waktu | Aksi`.
- Tombol **Edit** per baris → baris berubah jadi input inline (form per baris + Simpan/Batal via fetch).
- Baris **Tambah** (`editingId === 'new'`) → form tambah via fetch ke `agenda.note.store`.
- Tetap memakai endpoint yang ada (`agenda.note.store`, `agenda.note.update`, `agenda.note.destroy`).

### 2.6 Export Laporan Agenda

- Tabel notulensi tidak berubah bentuk (sudah sesuai referensi).

---

## 3. Daftar Keputusan Final (sesudah konfirmasi user)

| ID | Pertanyaan | Kepputusan Final |
|----|-----------|------------------|
| D1 | `scope` berlaku untuk tipe apa? | **Hanya `rapat`** |
| D2 | Domain eksternal menampilkan apa? | **Hanya agenda `external`**; domain internal melihat semua |
| D2b | Domain internal menampilkan apa? | **Semua agenda** |
| D3 | Link publik mengikuti domain scope? | **Tidak** — domain dihapus; link publik memakai `route()` biasa |
| D4 | Model anggota rapat? | **Reuse `agenda_employee`** (pivot = anggota + TTD) |
| D5 | Agenda lama tanpa anggota? | **Fallback: semua pegawai boleh absen** (`has_members = false`) |
| D6 | Edit notulensi admin? | **Kapan saja** |
| D7 | Edit/hapus notulensi publik? | **Siapa pun dengan link** (tetap, hari-H) |
| D8 | Host tidak dikenal? | **Diperlakukan internal** |

---

## 4. File yang Akan Terdampak (sudah/ sedang)

**Migrasi**
- `database/migrations/2026_11_23_000001_add_scope_and_has_members_to_agendas_table.php` ✅

**Config & Support**
- `config/agenda.php` ✅
- `app/Support/AgendaScope.php` ✅
- `app/Support/AgendaNote.php` ✅

**Model**
- `app/Models/Agenda.php` ✅ (`scope`, `has_members`, `hasMemberRoster()`)

**Request & Controller**
- `app/Http/Requests/AgendaRequest.php` ✅ (`scope`, `member_ids`)
- `app/Http/Controllers/AgendaController.php` ✅ (store/update, roster sync, `updateNote`)
- `app/Http/Controllers/PublicAgendaController.php` ✅ (filter domain)
- `app/Http/Controllers/AttendanceController.php` ✅ (anggota-only)
- `app/Http/Controllers/PublicAgendaInputController.php` ✅ (aturan notulensi bersama)
- `app/Services/AttendanceService.php` ✅ (koreksi roster-aware)
- `routes/web.php` ✅ (`admin.agendas.notes.update`)

**View**
- `resources/views/admin/agendas/create.blade.php` ✅ (lingkup + anggota)
- `resources/views/admin/agendas/edit.blade.php` ✅ (lingkup + anggota)
- `resources/views/admin/agendas/index.blade.php` ✅ (badge)
- `resources/views/admin/agendas/show.blade.php` ✅ (edit notulensi)
- `resources/views/public/agenda-today.blade.php` ✅ (badge + link domain)
- `resources/views/public/agenda-note-input.blade.php` ✅ (satu tabel + inline edit)
- `resources/views/components/agenda-header.blade.php` ✅ (badge lingkup)
- `resources/views/attendance/show.blade.php` ✅ (link domain)
- `resources/views/attendance/quiz.blade.php` ✅ (link domain)
- `resources/views/public/agenda-image-input.blade.php` ✅ (link domain)

**Test**
- `tests/Feature/AgendaExternalScopeTest.php` ✅

**Belum selesai**
- Export PDF menampilkan baris lingkup (opsional, menunggu keputusan).
- `npm run build` / `php artisan migrate` / `php artisan test` verifikasi akhir.
