/**
 * Prueba de humo del catálogo: abre cada historia en un navegador real y falla
 * si alguna no pinta nada o escupe un error de consola.
 *
 * Compilar no es renderizar — una plantilla puede compilar y romperse al
 * ejecutarse (un pipe sin importar, un guard que aborta el arranque). Esto es lo
 * que convierte a Storybook en el contrato de componente que pide el PRD 8.5.
 *
 * Uso: arranca `npm run storybook` y lanza `npm run stories:check`.
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

  // networkidle nunca llega: el servidor mantiene abierto el socket de HMR.
  await page.goto(`${base}/iframe.html?id=${id}&viewMode=story`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  // Espera a que la historia pinte algo, o a que Storybook muestre su panel de
  // error (que existe siempre en el DOM: solo cuenta si el body lo revela).
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

console.log(`\nRenderizan: ${ok}/${ids.length}`);
if (failures.length) {
  console.log('\nFallos:');
  for (const f of failures) console.log(`  ✗ ${f.title} › ${f.name} (${f.chars} chars) ${f.errors.join(' | ')}`);
}
await browser.close();

if (failures.length) process.exitCode = 1;
