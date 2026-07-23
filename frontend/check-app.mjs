import { chromium } from 'playwright-core';

const browser = await chromium.launch({ executablePath: '/usr/bin/google-chrome', args: ['--no-sandbox'] });
const page = await browser.newPage();
const errors = [];
page.on('console', m => { if (m.type() === 'error') errors.push(m.text().slice(0, 300)); });
page.on('pageerror', e => errors.push('PAGEERROR ' + String(e).slice(0, 300)));

await page.goto('http://localhost:4300/', { waitUntil: 'domcontentloaded' });
await page.waitForTimeout(3000);

console.log('URL final:', page.url());
console.log('título:', await page.title());
const text = (await page.locator('body').innerText().catch(() => '')).trim();
console.log('texto visible:', JSON.stringify(text.slice(0, 400)));
console.log('inputs:', await page.locator('input').count(), '| botones:', await page.locator('button').count());
console.log('errores:', errors.length ? errors : 'ninguno');
await browser.close();
