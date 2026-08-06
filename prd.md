# Role
You are an Expert Full-Stack Developer specializing in Laravel, Alpine.js, and Tailwind CSS. 

# Project Goal
Build "D-ASSA" (Digital Agenda & Self-Service Attendance), a web-based system for managing meeting agendas, generating formal PDF invitations with dual official signatures, and facilitating mobile check-ins via a digital signature pad.

# Technical Stack
- **Framework:** Laravel (Latest)
- **Authentication:** Laravel Breeze
- **Database:** PostgreSQL
- **Frontend:** Blade, Alpine.js (for reactive search/state), Tailwind CSS
- **PDF Engine:** `barryvdh/laravel-dompdf`
- **Testing:** PHPUnit or Pest for Unit Testing

# Brand Guidelines (UI Requirements)
- **Primary Color:** `#007774` (Deep Teal)
- **Secondary Color:** `#81bd41` (Apple Green)
- **UI Language:** Full Indonesian (Bahasa Indonesia) for all labels, buttons, and messages.
- **Styling:** Professional, clean, utilizing gradients of the primary/secondary colors for headers and action buttons.

# 1. Database Architecture (PostgreSQL)

Please implement these models and migrations with strict foreign key constraints:

1. **`signers` (Master Data):** - `id`, `name`, `position`, `signature_path` (stores path to transparent PNG of official signature).

2. **`participants` (Master Data):** - `id`, `name`, `identifier_number` (NIP/ID), `position`, `department`.

3. **`invitation_templates`:** - `id`, `name`, `body_content` (Text/HTML with placeholders like `[JUDUL_AGENDA]`, `[TANGGAL]`, `[TEMPAT]`, `[WAKTU]`).

4. **`agendas` (Transaction Table):**
   - `id`, `title`, `description`, `location`, `event_date`, `event_time`, `status` (draft/active/completed).
   - `template_id` (FK to invitation_templates).
   - `created_by_signer_id` (FK to signers) -> Position: "Hormat Kami".
   - `validated_by_signer_id` (FK to signers) -> Position: "Mengetahui".

5. **`agenda_participant` (Pivot Table):**
   - `agenda_id`, `participant_id`, `signature_path` (for check-in image), `signed_at` (timestamp).

# 2. Functional Requirements

## Feature A: Admin Dashboard (Protected by Laravel Breeze)
- **Master CRUDs:** Manage Participants and Signers (with transparent PNG uploads).
- **Template Builder:** Create invitation templates with the specified placeholders.
- **Agenda Management:** - Create agenda and select participants using an Alpine.js-powered multi-select UI.
    - Select two distinct signers from the `signers` table for the invitation footer.
- **PDF Invitation:** - Generate a formal PDF (No Logo). 
    - Use absolute CSS positioning to place the `signers`' transparent PNGs over their respective name lines (Left: Creator, Right: Validator).

## Feature B: Mobile Self-Service Attendance (Public Web)
- **Route:** `/absen/{agenda_id}`.
- **Live Search (Alpine.js):** Participants type their name to filter the invitee list instantly.
- **Signature Pad:** Upon selecting a name, a modal opens with an HTML5 `<canvas>`.
- **Submission:** Convert Canvas to Base64 -> Save as PNG in PostgreSQL/Storage -> Update `agenda_participant` pivot table.

# 3. Unit Testing Requirements
Ensure the following logic is covered by Unit Tests:
1. **Placeholder Service:** A test to verify that `[JUDUL_AGENDA]` and other tags are correctly replaced with real data in the PDF string.
2. **Signature Storage:** A test to verify that Base64 canvas data is correctly decoded and stored as a file in the `storage` directory.
3. **Double Attendance Prevention:** A test to ensure a participant cannot sign in twice for the same agenda.

# 4. Implementation Steps
1. Initialize Laravel with Breeze and configure PostgreSQL.
2. Generate Migrations, Models, and Factories (including the dual-FK relationship in Agendas).
3. Build the Admin CRUDs for Master Data.
4. Implement the `dompdf` service with a formal Blade layout.
5. Build the Mobile-First Attendance view with Alpine.js (Search + Canvas Signature).
6. Write and execute the specified Unit Tests.