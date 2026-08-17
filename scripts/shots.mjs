import { chromium } from 'playwright-core';
import fs from 'node:fs';

const EXE = '/opt/pw-browsers/chromium-1194/chrome-linux/chrome';
const BASE = 'http://127.0.0.1:8000';
const OUT = process.argv[2] || 'baseline';
const ONLY = process.argv[3]; // optional: comma list of page keys
const THEME = process.argv[4] || 'light'; // light | dark
const DIR = `/tmp/claude-0/-home-user-summer-2026/28d54cf2-73f3-5743-9556-498aac6b3b2a/scratchpad/shots/${OUT}`;
fs.mkdirSync(DIR, { recursive: true });

const pages = [
    { key: 'landing', url: '/', auth: false },
    { key: 'login', url: '/login', auth: false },
    { key: 'register', url: '/register', auth: false },
    { key: 'forgot', url: '/forgot-password', auth: false },
    { key: 'dashboard', url: '/dashboard', auth: true },
    { key: 'transactions', url: '/transactions', auth: true },
    { key: 'reports', url: '/reports', auth: true },
    { key: 'categories', url: '/categories', auth: true },
    { key: 'assistant', url: '/assistant', auth: true },
    { key: 'settings-profile', url: '/settings/profile', auth: true },
    { key: 'settings-security', url: '/settings/security', auth: true },
    { key: 'settings-appearance', url: '/settings/appearance', auth: true },
];

const viewports = {
    mobile: { width: 390, height: 844 },
    desktop: { width: 1440, height: 900 },
};

const themeInit = (theme) => `
    try {
        localStorage.setItem('appearance', '${theme}');
        document.cookie = 'appearance=${theme};path=/;max-age=31536000;SameSite=Lax';
        if ('${theme}' === 'dark') document.documentElement.classList.add('dark');
    } catch (e) {}
`;

async function login(context) {
    const p = await context.newPage();
    await p.goto(`${BASE}/login`, { waitUntil: 'networkidle' });
    await p.fill('input[type=email]', 'test@example.com');
    await p.fill('input[type=password]', 'password');
    await Promise.all([
        p.waitForURL('**/dashboard', { timeout: 15000 }).catch(() => {}),
        p.click('button[type=submit]'),
    ]);
    await p.waitForTimeout(1500);
    await p.close();
}

async function shoot(context, pg, vpName) {
    const page = await context.newPage();
    try {
        await page.goto(`${BASE}${pg.url}`, {
            waitUntil: 'networkidle',
            timeout: 20000,
        });
        await page.waitForTimeout(1200);
        await page.screenshot({
            path: `${DIR}/${pg.key}-${vpName}.png`,
            fullPage: true,
        });
        console.log(`✓ ${pg.key}-${vpName}`);
    } catch (e) {
        console.log(`✗ ${pg.key}-${vpName}: ${e.message}`);
    }
    await page.close();
}

const run = async () => {
    const browser = await chromium.launch({ executablePath: EXE });
    const only = ONLY ? ONLY.split(',') : null;

    for (const [vpName, vp] of Object.entries(viewports)) {
        const ctxOpts = {
            viewport: vp,
            deviceScaleFactor: 2,
            locale: 'ar',
        };

        // Anonymous context for public/auth pages
        const anon = await browser.newContext(ctxOpts);
        await anon.addInitScript(themeInit(THEME));
        // Authenticated context for app pages
        const authed = await browser.newContext(ctxOpts);
        await authed.addInitScript(themeInit(THEME));
        await login(authed);

        for (const pg of pages) {
            if (only && !only.includes(pg.key)) continue;
            await shoot(pg.auth ? authed : anon, pg, vpName);
        }
        await anon.close();
        await authed.close();
    }
    await browser.close();
    console.log(`\nDone → ${DIR} (${THEME})`);
};

run();
