import { test, expect } from '@playwright/test';

/**
 * Generates the README screenshots into docs/screenshots/.
 * Run with:  npx playwright test tests/screenshots.spec.ts
 *
 * Prerequisites (already prepared):
 *  - Today-dated agendas seeded (php artisan db:seed --class=AgendaTodaySeeder)
 *  - A login user exists (see LOGIN constant below)
 */

const LOGIN = {
  email: 'muhamad.miftahudin@rsazra.co.id',
  password: 'password123',
};

const DESKTOP = { width: 1440, height: 900 };
const MOBILE = { width: 390, height: 844 };

// Today-dated agenda IDs (from AgendaTodaySeeder)
const DIKLAT_AGENDA_ID = 22; // has quiz + pretest
const SEARCH_NAME = 'ADE'; // matches an existing employee for the signature flow

const OUT = 'docs/screenshots';

test('capture README screenshots', async ({ page }) => {
  test.setTimeout(120000);
  // ---------- 1. Login ----------
  await page.setViewportSize(DESKTOP);
  await page.goto('/login');
  await page.fill('input[name="email"]', LOGIN.email);
  await page.fill('input[name="password"]', LOGIN.password);
  await Promise.all([
    page.waitForURL('**/dashboard', { timeout: 15000 }),
    page.click('button[type="submit"]'),
  ]);

  // ---------- 2. Dashboard ----------
  await page.goto('/dashboard');
  await page.waitForLoadState('networkidle');
  await page.screenshot({ path: `${OUT}/dashboard.png`, fullPage: true });

  // ---------- 3. Agenda management ----------
  await page.goto('/admin/agendas');
  await page.waitForLoadState('networkidle');
  await page.screenshot({ path: `${OUT}/agenda-management.png`, fullPage: true });

  // ---------- 4. Employee recap ----------
  await page.goto('/admin/employee-recaps');
  await page.waitForLoadState('networkidle');
  await page.screenshot({ path: `${OUT}/recap.png`, fullPage: true });

  // ---------- 5. Public attendance (mobile) ----------
  await page.setViewportSize(MOBILE);
  await page.goto(`/absen/${DIKLAT_AGENDA_ID}`, { waitUntil: 'domcontentloaded' });
  const searchInput = page.locator('input[placeholder*="Ketik nama"]');
  await searchInput.waitFor({ state: 'visible', timeout: 15000 });
  await searchInput.fill(SEARCH_NAME);
  // wait for the live-search result list to render
  await page.waitForTimeout(800);
  await page.screenshot({ path: `${OUT}/attendance.png` });

  // ---------- 6. Signature pad (mobile) ----------
  // open the modal by clicking the first matching employee row
  await page.locator('div[\\@click="openSignModal(p)"]').first().click();
  await expect(page.locator('#signature-canvas')).toBeVisible();
  await page.waitForTimeout(300);
  // draw a simple signature so the pad looks used
  const box = await page.locator('#signature-canvas').boundingBox();
  if (box) {
    const y = box.y + box.height / 2;
    await page.mouse.move(box.x + 30, y);
    await page.mouse.down();
    await page.mouse.move(box.x + 70, y - 35);
    await page.mouse.move(box.x + 110, y + 25);
    await page.mouse.move(box.x + 150, y - 20);
    await page.mouse.move(box.x + 200, y + 10);
    await page.mouse.up();
  }
  await page.waitForTimeout(200);
  await page.screenshot({ path: `${OUT}/signature-pad.png` });

  // ---------- 7. Quiz / posttest (mobile) ----------
  await page.goto(`/absen/${DIKLAT_AGENDA_ID}/quiz`, { waitUntil: 'domcontentloaded' });
  await page.waitForTimeout(800);
  await page.screenshot({ path: `${OUT}/quiz.png` });

  // ---------- 8. Public agenda list — home page (desktop) ----------
  await page.setViewportSize(DESKTOP);
  await page.goto('/', { waitUntil: 'domcontentloaded' });
  await page.getByText('Agenda Hari Ini').first().waitFor({ state: 'visible', timeout: 15000 });
  await page.waitForTimeout(600);
  await page.screenshot({ path: `${OUT}/public-agenda-list.png`, fullPage: true });
});
