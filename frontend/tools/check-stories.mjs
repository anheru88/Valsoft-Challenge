/**
 * Smoke test for the catalogue: opens every story in a real browser and fails if
 * any of them renders nothing or logs a console error.
 *
 * Compiling is not rendering — a template can compile and still break at runtime
 * (a pipe that was never imported, a guard that aborts the bootstrap). This is
 * what makes Storybook the component contract PRD 8.5 asks for.
 *
 * Usage: start `npm run storybook`, then run `npm run stories:check`.
 */
import { chromium } from 'playwright-core';

const base = 'http://localhost:6006';
const index = await (await fetch(`${base}/index.json`)).json();
const ids = Object.values(index.entries)
  .filter(e => e.type === 'story')
  .map(e => ({ id: e.id, title: e.title, name: e.name }));

const browser = await chromium.launch({ executablePath: '/usr/bin/google-chrome', args: ['--no-sandbox'] });
const page = await browser.newPage();

let ok = 0;
const failures = [];

for (const { id, title, name } of ids) {
  const errors = [];
  const onErr = m => { if (m.type() === 'error') errors.push(m.text().slice(0, 200)); };
  const onPageErr = e => errors.push(String(e).slice(0, 200));
  page.on('console', onErr);
  page.on('pageerror', onPageErr);

  // networkidle never fires: the dev server keeps the HMR socket open.
  await page.goto(`${base}/iframe.html?id=${id}&viewMode=story`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  // Wait for the story to paint something, or for Storybook to reveal its error
  // panel (which is always in the DOM: it only counts once the body shows it).
  await page.waitForFunction(
    () => {
      const root = document.querySelector('#storybook-root');
      return document.body.classList.contains('sb-show-errordisplay')
        || (root && root.innerHTML.trim().length > 0);
    },
    { timeout: 15000 },
  ).catch(() => {});
  await page.waitForTimeout(120);

  const root = await page.locator('#storybook-root').innerHTML().catch(() => '');
  const sbError = await page.locator('body.sb-show-errordisplay').count();

  page.off('console', onErr);
  page.off('pageerror', onPageErr);

  const broken = sbError > 0 || root.trim().length < 20 || errors.length > 0;
  if (broken) failures.push({ title, name, id, chars: root.trim().length, errors: errors.slice(0, 2) });
  else ok++;
}

console.log(`\nRendered: ${ok}/${ids.length}`);
if (failures.length) {
  console.log('\nFailures:');
  for (const f of failures) console.log(`  ✗ ${f.title} › ${f.name} (${f.chars} chars) ${f.errors.join(' | ')}`);
}
await browser.close();

if (failures.length) process.exitCode = 1;
