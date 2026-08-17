import { chromium } from 'playwright-core';
import fs from 'node:fs';

const EXE = '/opt/pw-browsers/chromium-1194/chrome-linux/chrome';
const BASE = 'http://127.0.0.1:8000';
const THEME = process.argv[2] || 'light';
const DIR = `/tmp/claude-0/-home-user-summer-2026/28d54cf2-73f3-5743-9556-498aac6b3b2a/scratchpad/shots/interact`;
fs.mkdirSync(DIR, { recursive: true });

const themeInit = (t) => `try{localStorage.setItem('appearance','${t}');document.cookie='appearance=${t};path=/';if('${t}'==='dark')document.documentElement.classList.add('dark');}catch(e){}`;

async function login(context) {
    const p = await context.newPage();
    await p.goto(`${BASE}/login`, { waitUntil: 'networkidle' });
    await p.fill('input[type=email]', 'test@example.com');
    await p.fill('input[type=password]', 'password');
    await Promise.all([p.waitForURL('**/dashboard').catch(() => {}), p.click('button[type=submit]')]);
    await p.waitForTimeout(1500);
    await p.close();
}

async function shot(page, name) {
    await page.screenshot({ path: `${DIR}/${name}-${THEME}.png` });
    console.log(`✓ ${name}-${THEME}`);
}

const run = async () => {
    const browser = await chromium.launch({ executablePath: EXE });
    for (const [vp, size] of Object.entries({ desktop: { width: 1440, height: 900 }, mobile: { width: 390, height: 844 } })) {
        const ctx = await browser.newContext({ viewport: size, deviceScaleFactor: 2, locale: 'ar' });
        await ctx.addInitScript(themeInit(THEME));
        await login(ctx);

        // Add-transaction drawer from dashboard
        let page = await ctx.newPage();
        await page.goto(`${BASE}/dashboard`, { waitUntil: 'networkidle' });
        await page.waitForTimeout(800);
        try {
            await page.getByRole('button', { name: /إضافة معاملة|إضافة/ }).first().click();
            await page.waitForTimeout(900);
            await shot(page, `drawer-${vp}`);
        } catch (e) { console.log('drawer fail', vp, e.message); }
        await page.close();

        // Category add dialog
        page = await ctx.newPage();
        await page.goto(`${BASE}/categories`, { waitUntil: 'networkidle' });
        await page.waitForTimeout(800);
        try {
            await page.getByRole('button', { name: /إضافة فئة|إضافة/ }).first().click();
            await page.waitForTimeout(900);
            await shot(page, `category-dialog-${vp}`);
        } catch (e) { console.log('cat dialog fail', vp, e.message); }
        await page.close();

        await ctx.close();
    }
    await browser.close();
    console.log(`Done → ${DIR}`);
};
run();
