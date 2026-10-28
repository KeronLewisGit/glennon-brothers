// Stills of the motion layer for the proposal's "Motion and interaction" page. CSS animations are
// paused at a chosen moment (document.getAnimations), so every still is the same on every run.
// Usage: part of npm run proposal (dev server on 8111 with demo data).
import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';

const BASE = process.env.BASE_URL ?? 'http://127.0.0.1:8111';
const OUT = path.resolve('dist/proposal/shots');
const CHROME = process.env.CHROME_PATH ?? '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const W = 1440, H = 900;

const browser = await puppeteer.launch({ executablePath: CHROME, headless: 'new', args: ['--hide-scrollbars', '--no-sandbox'] });
const page = await browser.newPage();
await page.setViewport({ width: W, height: H, deviceScaleFactor: 1 });
await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'no-preference' }, { name: 'prefers-color-scheme', value: 'light' }]);

const wait = (ms) => new Promise((r) => setTimeout(r, ms));
// Freeze every running CSS animation at `ms` from its start (delays included).
const freeze = (ms) => page.evaluate((ms) => document.getAnimations().filter((a) => a.timeline === document.timeline).forEach((a) => { a.pause(); a.currentTime = ms; }), ms);
const thaw = () => page.evaluate(() => document.getAnimations().filter((a) => a.timeline === document.timeline).forEach((a) => a.play()));
const scrollTo = async (selector, block = 'center') => {
  await page.evaluate((s, b) => document.querySelector(s).scrollIntoView({ block: b, behavior: 'instant' }), selector, block);
  await wait(250);
};
const stills = [];
const shot = async (name, caption, clip) => {
  const file = path.join(OUT, `motion-${name}.jpg`);
  await page.screenshot({ path: file, type: 'jpeg', quality: 70, ...(clip ? { clip } : {}) });
  stills.push({ name, file, caption });
  console.log(`✓ motion ${name}`);
};

await page.goto(BASE + '/', { waitUntil: 'networkidle0' });
await page.evaluate(() => document.fonts.ready);
// Lazy images further down must be there when we scroll to them.
await page.evaluate(() => document.querySelectorAll('img[loading="lazy"]').forEach((i) => { i.loading = 'eager'; }));

// 1. Hero entrance, mid-way: the photo settling, the twin firs drawing in, the headline arriving word by word.
await freeze(780);
await shot('hero', 'Hero entrance: the photo settles, the twin firs from the logo draw themselves, and the headline arrives word by word.');
await freeze(6000);

// 2. The four-mill map, routes drawing out from Longford.
await scrollTo('.plant-map');
await wait(200);
await freeze(1350);
await shot('map', 'The four mills on a map of Ireland and Scotland. Routes draw out from head office in Longford; pins pulse and light up with the matching mill card.');
await freeze(8000);
await thaw();

// 3–4. The scroll story: the illustration follows the step in the middle of the screen.
for (const [n, at, caption] of [
  ['2', 800, 'From log to frame, as a scroll story. Step 2: the saw spins and boards come off the line while the step text scrolls past.'],
  ['5', 1000, 'Step 5: a DTE timber frame house draws itself line by line. Five scenes, all simple vector drawings, no video.'],
]) {
  await page.evaluate((n) => {
    const step = document.querySelector(`.process-step[data-step="${n}"]`);
    const r = step.getBoundingClientRect();
    window.scrollBy({ top: r.top + r.height / 2 - window.innerHeight / 2, behavior: 'instant' });
  }, n);
  await wait(250);
  await freeze(at);
  await shot('story-' + n, caption);
  await thaw();
}

// 5. Count-ups for the published figures (requestAnimationFrame, so caught by time).
await page.evaluate(() => {
  const el = document.querySelector('#figures-title').closest('section');
  window.scrollTo({ top: el.getBoundingClientRect().top + window.scrollY - 120, behavior: 'instant' });
});
await wait(650);
await shot('figures', 'Published figures count up once as they scroll into view: 450,000 m³ sawn and 220,000 m³ kiln-drying capacity, six TTJ awards. Screen readers hear the final number.');

// 6. Theme toggle: the dark theme spreads out from the button in a circle.
// Scrolled to the welcome section, so the circle's edge crosses light content (it grows from the
// toggle in the bar above).
await page.evaluate(() => window.scrollTo({ top: document.querySelector('#welcome-title').getBoundingClientRect().top + window.scrollY - 420, behavior: 'instant' }));
await wait(300);
await page.evaluate(() => document.querySelector('.topbar [data-theme-toggle]').click());
await wait(60);
// Only the view transition's own animations: the hero's entrance has finished and stays put.
await page.evaluate(() => document.getAnimations().filter((a) => String(a.effect?.pseudoElement ?? '').includes('view-transition')).forEach((a) => { a.pause(); a.currentTime = 200; }));
await shot('theme', 'Light and dark themes, both built from the group\'s red and slate. The switch spreads the new theme out from the button in a circle.');
await thaw();
await wait(900);

// 7. About: the history line draws down the page as you scroll.
await page.evaluate(() => { document.documentElement.removeAttribute('data-theme'); localStorage.removeItem('theme'); });
await page.goto(BASE + '/about', { waitUntil: 'networkidle0' });
await page.evaluate(() => document.querySelectorAll('img[loading="lazy"]').forEach((i) => { i.loading = 'eager'; }));
await page.evaluate(() => {
  const t = document.querySelector('[data-timeline]');
  window.scrollTo({ top: t.getBoundingClientRect().top + window.scrollY - 330, behavior: 'instant' });
});
await wait(500);
await page.evaluate(() => window.scrollBy({ top: 120, behavior: 'instant' }));
await wait(900);
await shot('timeline', 'About: the red history line draws down the page as you scroll, and each year pops onto it in turn.');

// 8. Page transitions and the menu on a phone: the tabs collapse into a red Menu tab.
await page.setViewport({ width: 390, height: 844, deviceScaleFactor: 2, isMobile: true, hasTouch: true });
await page.goto(BASE + '/', { waitUntil: 'networkidle0' });
await freeze(5000);
await page.click('.tab-menu');
await wait(400);
await shot('phone-menu', 'On a phone the red tabs fold into a Menu tab; pages cross-fade into each other where the browser supports it.', { x: 0, y: 0, width: 390, height: 640 });

fs.writeFileSync(path.join(OUT, 'motion.json'), JSON.stringify(stills, null, 1));
await browser.close();
console.log('motion stills done');
