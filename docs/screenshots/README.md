# Screenshots

These images are referenced by the project `README.md`.

| File | Screen |
| --- | --- |
| `public-agenda-list.png` | Public landing page — today's agendas (no login) |
| `dashboard.png` | Admin dashboard — KPIs, agenda distribution, recent activity |
| `agenda-management.png` | Agenda list (`/admin/agendas`) |
| `recap.png` | Employee attendance recap (`/admin/employee-recaps`) |
| `attendance.png` | Public self-service check-in with live search (mobile) |
| `signature-pad.png` | Digital signature modal (mobile) |
| `quiz.png` | Pre/Post-test page (mobile) |

## Regenerating

The screenshots are produced automatically by a Playwright script:
`tests/screenshots.spec.ts`.

```bash
# 1. Make sure today-dated demo agendas exist (attendance/quiz are date-gated to "today")
php artisan db:seed --class=AgendaTodaySeeder --force

# 2. Run the capture script (auto-starts `php artisan serve`)
npx playwright test tests/screenshots.spec.ts
```

Notes:
- The script logs in with the credentials in the `LOGIN` constant at the top of
  `tests/screenshots.spec.ts` — update it to a valid user in your database.
- `DIKLAT_AGENDA_ID` / `SEARCH_NAME` in the same file may need adjusting to match
  IDs and employee names present in your data.
- Desktop shots use a 1440×900 viewport; mobile shots use 390×844.
