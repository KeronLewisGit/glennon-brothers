// Builds the client proposal (letter-size PDF) from the captured screenshots, in the site's own
// palette and fonts (the group's red #de0a2a and slate #162e38; Montserrat and Inter).
// Usage: npm run proposal (or node scripts/proposal/build.mjs after a capture).
import puppeteer from 'puppeteer-core';
import fs from 'node:fs';
import path from 'node:path';
import { execFileSync } from 'node:child_process';

const CHROME = process.env.CHROME_PATH ?? '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const ROOT = path.resolve('.');
const SHOTS = path.resolve('dist/proposal/shots');
const MOTION = JSON.parse(fs.readFileSync(path.join(SHOTS, 'motion.json'), 'utf8'));
const still = (name) => { const m = MOTION.find((x) => x.name === name); if (!m) throw new Error(`Motion still missing: ${name}`); return m; };
const chunks = JSON.parse(fs.readFileSync(path.join(SHOTS, 'chunks.json'), 'utf8'));
const OUT_HTML = path.resolve('dist/proposal/proposal.html');
const OUT_PDF = path.resolve('dist/proposal/Glennon-Brothers-Website-Proposal.pdf');
const MAX_MB = 9.5;
const DATE = new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
const TESTS = process.env.TEST_COUNT ?? '91';
const img = (f) => 'file://' + f;
// Chrome embeds a WebP background losslessly (about 4 MB), so the cover photo goes in as a JPEG.
const COVER = path.resolve('dist/proposal/cover.jpg');
execFileSync('sips', ['-s', 'format', 'jpeg', '-s', 'formatOptions', '70', path.join(ROOT, 'public/img/troon-harbour-1920.webp'), '--out', COVER], { stdio: 'ignore' });
const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;');

// The site's self-hosted fonts (built by the Vite fonts plugin into public/build/assets).
const ASSETS = path.join(ROOT, 'public/build/assets');
const font = (prefix) => {
  const f = fs.readdirSync(ASSETS).find((n) => n.startsWith(prefix) && n.endsWith('.woff2'));
  if (!f) throw new Error(`Font ${prefix} not found in public/build/assets; run npm run build first.`);
  return 'file://' + path.join(ASSETS, f);
};
const FONTS = [
  ['Montserrat', 500, 'montserrat-500-normal'], ['Montserrat', 600, 'montserrat-600-normal'], ['Montserrat', 700, 'montserrat-700-normal'],
  ['Inter', 400, 'inter-400-normal'], ['Inter', 500, 'inter-500-normal'], ['Inter', 600, 'inter-600-normal'], ['Inter', 700, 'inter-700-normal'],
].map(([fam, w, p]) => `@font-face { font-family: "${fam}"; font-weight: ${w}; font-style: normal; src: url("${font(p)}") format("woff2"); }`).join('\n');

// The site's logo (<x-logo>): GLENNON and BROTHERS in the given colour, the twin firs in the brand red.
const LOGO_PATHS = [...fs.readFileSync(path.join(ROOT, 'resources/views/components/logo.blade.php'), 'utf8').matchAll(/<path [^>]*\/?>(<\/path>)?/g)].map((m) => m[0]);
if (LOGO_PATHS.length !== 3) throw new Error('Logo paths not found in resources/views/components/logo.blade.php');
const LOGO = (h, color) => `<svg viewBox="0 0 331 62" height="${h}" width="${Math.round(h * 331 / 62)}" style="color:${color}">${LOGO_PATHS.join('')}</svg>`;
const TREE_PATH = 'M16.4 40.5 16.4 35.6 9 35.6 13.6 25.5 16.4 25.5 16.4 20.5 11 20.5 18.1 7.6 25.2 20.5 19.8 20.5 19.8 25.5 22.6 25.5 27.2 35.6 19.8 35.6 19.8 40.5 35.5 40.5 36.2 40.5 51.9 40.5 51.9 35.6 44.5 35.6 49.1 25.5 51.9 25.5 51.9 20.5 46.5 20.5 53.6 7.6 60.7 20.5 55.3 20.5 55.3 25.5 58.1 25.5 62.7 35.6 55.3 35.6 55.3 40.5 71.7 40.5 62.7 25.5 68.5 25.5 53.6 0 38.7 25.5 44.5 25.5 35.9 39.9 27.2 25.5 33 25.5 18.1 0 3.2 25.5 9 25.5 0 40.5Z';
const MARK = (h = 10) => `<span class="mark"><svg viewBox="0 0 72 41" height="${h}" width="${Math.round(h * 72 / 41)}"><path fill="#de0a2a" d="${TREE_PATH}"/></svg></span>`;
const WORDMARK = (dark = true) => `<div class="brand">${LOGO(dark ? 46 : 38, dark ? '#ffffff' : '#162e38')}</div>`;
const plate = (code, label, dark = false) => `<p class="plate ${dark ? 'dark' : ''}"><span class="c">${esc(code)}</span><span class="l">${esc(label)}</span></p>`;

/** Hand-written captions per slice (key = screen name, index = slice number), from what each slice shows. */
const CAPTIONS = {
  home: [
    'The hero on the group\'s own photograph of the Troon mill, between red rules like the banner on the current site: the twin firs from the logo, the founding year, the two actions a buyer needs and a tabbed info card. The product range runs beneath it.',
    'The current home page\'s welcome text and layout, kept and modernised: red tab labels over white boxes for Glendeck, Glenfence and the group\'s companies, beside the welcome.',
    'The four mills on a map of Ireland and Scotland, each pin and card linked to that mill\'s page.',
    'From the log yard to a timber frame home: five steps, with a drawing that changes as each one scrolls past (see Motion and interaction).',
    'Capacity figures and awards only as the group publishes them, then the product lines, each linking to its sizes.',
    'DTE timber frame, with drawings sent online, and careers at the four mills and DTE.',
    'Trade quotes and haulier slot booking side by side, news from the group\'s own press page, and the red footer bar of the current site.',
  ],
  plants: [
    'Our mills: four sawmills in two countries, Longford and Fermoy first, each with its address from the group\'s contact page and what it produces.',
    'Windymains and Troon in Scotland, DTE Manufacturing on the Longford site, and the wider group.',
  ],
  plant: ['A mill page (Troon): its story from the group\'s own pages, the biomass CHP plant, key facts, jobs open at that mill, and its own contact block with a quote and slot-booking call to action.'],
  products: [
    'Products: what the mills saw, dry, grade and treat, with an anchor for each product line.',
    'Construction timber, fencing, and pallet and packaging timber.',
    'Glendeck decking, and the residues sold on: wood chip, sawdust, bark and shavings.',
    'Trade, merchant and manufacturer pricing: every product leads to the trade quote form.',
  ],
  frame: [
    'DTE timber frame homes and roof trusses: what DTE makes, and how a set of drawings becomes a frame on site.',
    'The three steps (send drawings, give the timeline, design review), DTE\'s own details, and a route for UK projects.',
  ],
  sustainability: [
    'Sustainability, using only what the group publishes, under the same banner as every inner page.',
    'FSC® chain of custody codes for the Irish and UK plants, the EPA IPC licence, SEPA, ISPM 15 and NSAI approval for DTE, with the Environmental Product Declarations to download.',
    'How every part of the log is used: wood chip, sawdust, bark and shavings, and the biomass CHP plant at Troon.',
  ],
  about: [
    'About: a family sawmilling business since 1913, in the group\'s own words, with the joint managing directors named on its website.',
    'The history from water power in Longford to biomass CHP in Troon, dated from the group\'s history page.',
    'Awards the group publishes itself: TTJ Sawmiller of the Year (six times), HAI Best Supplier Customer Experience and the European Business Awards Ruban d\'Honneur.',
  ],
  contact: [
    'Contact: every mill\'s address, telephone and email, DTE and the group\'s other businesses.',
    'A message form with a topic and a mill, which issues an EN- reference.',
  ],
  quote: [
    'Trade quote: who it is for, the business details, and the delivery country, which decides the sales team and the currency (euro for Ireland, sterling for the UK).',
    'Line items: product, grade, sizes in millimetres and metres, treatment, quantity and unit, with an optional delivery site per line. Up to twelve lines, three shown to start.',
    'An optional cutting list upload, delivery notes and consent. Files survive a validation error, so nothing has to be chosen twice.',
  ],
  'frame-enquire': [
    'Timber frame enquiry: who is building, the type of project and, for schemes, the number of homes.',
    'The site, Eircode, planning status, planned start and floor area, then up to eight drawings (PDF, DWG, DXF, images or ZIP).',
    'Consent and submit. DTE gets a TF- reference and the customer gets a private link to send more drawings later.',
  ],
  drawings: ['The customer\'s private drawings page, opened from the signed link in the confirmation email. It shows what DTE already has, takes revised drawings, expires after 30 days and stops working once DTE revokes it.'],
  booking: [
    'Haulier slot booking: a mill, a date and a three-hour slot.',
    'The haulier, vehicle registration and type, the purpose (collecting, delivering logs, residues) and the order reference.',
  ],
  careers: [
    'Careers: every opening at the four mills and DTE, filterable by mill and by role family (sample postings modelled on the group\'s current vacancies).',
    'Each posting shows its mill, hours, type of work and closing date.',
    'One application covers all four mills, and operatives and drivers do not need a CV.',
  ],
  apply: [
    'Job application: the role or "any opening", the preferred mill, and the right to work in Ireland or the UK.',
    'Experience and licences (asked only for drivers), CV upload where the role needs one, and consent.',
  ],
  'admin-login': ['Staff sign-in. Accounts that have been switched off are refused and signed out.'],
  'admin-dashboard': [
    'Overview for an administrator: the number of quote requests that have waited over 24 hours comes first, oldest first, for both sales teams. Figures are from the demo data.',
    'Tiles for every area: quotes, timber frame, applications, haulier slots and enquiries.',
  ],
  'admin-dashboard-sales': ['The same overview signed in as the UK sales team (Troon): it leads with that team\'s waiting quotes.'],
  'admin-dashboard-hr': [
    'Signed in as HR: it leads with applications that have sat in New or HR screening for over three days.',
    'The pipeline by stage, from new to offer made.',
  ],
  'admin-dashboard-frame': ['Signed in as DTE: it leads with timber frame enquiries waiting over two days for a design review.'],
  'admin-quotes': [
    'Trade quotes: status tabs, region (Ireland or UK), owner and age, search and CSV export.',
    'Every request with its lines, country, owner and how long it has waited.',
  ],
  'admin-quote': ['A quote request: business, delivery address, sales team and currency, the line items, the owner and status, a quoted total, and a timeline that records how it was routed.'],
  'admin-frames': ['Timber frame enquiries by status, with the type of project, county, planned start and designer.'],
  'admin-frame': ['A timber frame enquiry: the project, the drawings (DTE and administrators only, every download logged), the designer and status, and the customer\'s drawings link with copy and revoke.'],
  'admin-applications': [
    'Applications: stage tabs, mill, role family, reviewer and "waiting over 3 days", search and CSV export.',
    'Each applicant with role, mill, experience, right to work and stage.',
  ],
  'admin-application': ['An application: the mill, role family, right to work and availability, the CV (HR and administrators only), the reviewer, and the stage from new through HR screening and plant manager review to offer.'],
  'admin-bookings': ['Haulier bookings by date and mill, with requested slots to confirm first.'],
  'admin-booking': ['A booking: mill, slot, haulier, vehicle and order reference, confirmed or declined by sales, with notes on the timeline.'],
  'admin-users': ['Staff accounts with four roles (administrator, sales, HR and DTE), and a region for each salesperson.'],
};

/** Screen definitions: section, key, title, bullets. */
const SECTIONS = [
  { id: 'public', eyebrow: 'Part 1', code: '01', title: 'The public website', intro: 'A new glennonbrothers.ie, written from the group\'s own pages and dressed in its own brand: the red #de0a2a, the GLENNON twin-fir logo, red tab navigation over a slate bar, banner photos between red rules and red tab labels on white boxes, as on the current site. Every page works on a phone, in light or dark.', screens: [
    ['home', 'Home page', ['Leads with what the group makes and the two actions a buyer needs.', 'Capacity figures and awards only as the group publishes them.']],
    ['plants', 'The four mills', ['Longford, Fermoy, Windymains and Troon, each with its own address and contact.']],
    ['plant', 'A mill page', ['One page per mill: its story, facts, open jobs and its own contacts.']],
    ['products', 'Products', ['Each product line links straight to a trade quote.', 'Old addresses such as /fencing.html redirect to the new pages.']],
    ['frame', 'DTE timber frame', ['DTE gets a proper home instead of a placeholder at dte.ie.']],
    ['sustainability', 'Sustainability', ['Only the certifications and declarations the group publishes, with their codes.']],
    ['about', 'About the group', ['Since 1913 in Longford, in the group\'s own words, with its published awards.']],
    ['contact', 'Contact', ['Every mill, DTE and a message form.']],
  ]},
  { id: 'flows', eyebrow: 'Part 2', code: '02', title: 'Quotes, frames, hauliers and people', intro: 'Four intake flows replace phone calls and emails: trade quotes with line items, timber frame enquiries with drawings, haulier slot bookings and job applications, plus a contact form. Each gives the sender a reference number on screen and by email and lands with the right team.', screens: [
    ['quote', 'Trade quote', ['Ireland goes to the Ireland sales team in euro; Great Britain to the UK team in sterling.', 'Each request is assigned to the salesperson in that region with the fewest open quotes.']],
    ['frame-enquire', 'Timber frame enquiry', ['Drawings are stored privately and opened only by DTE.']],
    ['drawings', 'Customer drawings link', ['A signed link that expires and can be revoked, so a customer can send revisions without email attachments.']],
    ['booking', 'Haulier slot booking', ['Collections and log deliveries booked into a slot at the right mill.']],
    ['careers', 'Careers', ['Openings with Google job-posting data, so they appear in job search.']],
    ['apply', 'Job application', ['No CV for operatives, drivers and frame erectors; required for maintenance and office roles.']],
  ]},
  { id: 'admin', eyebrow: 'Part 3', code: '03', title: 'The back office', intro: 'A private, role-based back office for sales in Ireland and the UK, DTE and HR. Each role sees only its own work, and the overview opens on the one number that role has to act on today.', screens: [
    ['admin-login', 'Staff sign-in', ['Individual accounts with four roles; switched-off accounts are refused.']],
    ['admin-dashboard', 'Overview', ['Quote requests waiting over 24 hours come first.']],
    ['admin-dashboard-sales', 'Overview for UK sales', ['A salesperson sees their own region\'s quotes.']],
    ['admin-dashboard-hr', 'Overview for HR', ['HR sees applicants waiting too long.']],
    ['admin-dashboard-frame', 'Overview for DTE', ['DTE sees enquiries waiting for a design review.']],
    ['admin-quotes', 'Trade quotes', ['New, pricing, quoted, won, lost or declined, with an owner on every request.']],
    ['admin-quote', 'Quote detail', ['Lines, owner, status and quoted total in one place, with a full timeline.']],
    ['admin-frames', 'Timber frame enquiries', ['Every enquiry from first drawings to signed order.']],
    ['admin-frame', 'Timber frame detail', ['Drawings, designer, status and the customer\'s link.']],
    ['admin-applications', 'Applications', ['Applicants by mill and role family, with a named reviewer.']],
    ['admin-application', 'Applicant detail', ['CVs open for HR and administrators only.']],
    ['admin-bookings', 'Haulier bookings', ['Requested slots to confirm, by mill and date.']],
    ['admin-booking', 'Booking detail', ['Confirm or decline a slot, and mark it completed.']],
    ['admin-users', 'Staff and roles', ['Add staff, set roles and regions, and switch accounts off.']],
  ]},
];
const SCREEN_COUNT = SECTIONS.reduce((n, s) => n + s.screens.length, 0);

const FEATURES = [
  ['Public website', ['One page per mill', 'Products linked to trade quotes', 'DTE timber frame', 'Sustainability and EPDs', 'History and awards', 'News', 'Redirects from the old site\'s pages', 'Sitemap and Google structured data', 'Phone action bar on every page']],
  ['Intake', ['Trade quotes with line items and a cutting list', 'Routing by region and currency', 'Timber frame enquiries with drawings', 'Signed drawings link for customers', 'Haulier slot booking', 'Careers with job postings', 'Job applications with CV upload', 'Contact form', 'Reference number on screen and by email']],
  ['Back office', ['Overview that leads with each role\'s number', 'Quote register with owners and ages', 'Timber frame register and drawings', 'Recruitment pipeline by mill and role family', 'Haulier bookings', 'Notes and a timeline on every record', 'CSV exports', 'Four staff roles and two sales regions']],
  ['Platform', ['Laravel 13 on PHP 8.3', 'HTTPS throughout', 'All uploads stored privately', 'Each team opens only its own files', 'Rate-limited, spam-protected forms', 'Light and dark themes', 'Motion that respects reduced-motion settings', `${TESTS} automated tests`, 'Irish and UK GDPR privacy notice', 'Packaged for one-zip installation']],
];

/**
 * Pack a screen's slices onto pages. A slice's height ÷ width is its share of a page. Every slice keeps
 * its own caption; slices never break across pages and keep their order. The layout is chosen by cost:
 * one point per page, one more for a page left about a third empty, half a point for shrinking slices
 * below 90% of a 1440px screen to share a page (never below 85%), and a quarter point for moving the
 * bullets off the first page. So a short slice joins its neighbour instead of sitting alone on a
 * half-blank page; ties go to the layout whose emptiest page is fullest.
 */
function pages(c, hasBullets) {
  const sizes = c.sizes ?? c.chunks.map(() => [1440, 1440]);
  // Page capacity as image height ÷ text width: about 1.22 below the heading, 0.98 when bullets sit on top.
  // The estimate leaves out caption height, so a page counts as gappy at 25% estimated (about 35% printed).
  const CAP = 0.08, GOOD_SCALE = 0.9, MIN_SCALE = 0.85, GAP = 0.25;
  const ratios = sizes.map(([w, h]) => h / w);
  const page = (from, to, bullets) => {
    const budget = bullets ? 0.98 : 1.22;
    const sum = ratios.slice(from, to).reduce((a, r) => a + r, 0);
    const n = to - from;
    const scale = Math.min(1, (budget - CAP * n) / sum);
    const readable = scale * 1440 / sizes[from][0]; // narrower captures carry larger type
    if (n > 1 && readable < MIN_SCALE) return null;
    const empty = 1 - (sum * scale + CAP * n) / budget;
    return { items: [...Array(n).keys()].map((k) => from + k), bullets, budget, scale, empty, cost: 1 + (empty > GAP ? 1 : 0) + (readable < GOOD_SCALE ? 0.5 : 0) + (bullets && from > 0 ? 0.25 : 0) };
  };
  // best(i, owed) = cheapest packing of slices i..end, where owed means the bullets still need a page.
  const n = ratios.length;
  const memo = new Map();
  const best = (i, owed) => {
    if (i === n) return owed ? null : { list: [], cost: 0, worst: 0 };
    const key = i + ':' + owed;
    if (memo.has(key)) return memo.get(key);
    let pick = null;
    for (const here of owed ? [true, false] : [false]) {
      for (let j = i + 1; j <= n; j++) {
        const pg = page(i, j, here);
        if (!pg) break;
        const rest = best(j, owed && !here);
        if (!rest) continue;
        const cand = { list: [pg, ...rest.list], cost: pg.cost + rest.cost, worst: Math.max(pg.empty, rest.worst) };
        if (!pick || cand.cost < pick.cost - 1e-9 || (Math.abs(cand.cost - pick.cost) < 1e-9 && cand.worst < pick.worst - 1e-9)) pick = cand;
      }
    }
    memo.set(key, pick);
    return pick;
  };
  return best(0, hasBullets).list;
}

// Every desktop slice has its own caption: fail loudly when the slicing changes under them.
for (const [key, c] of Object.entries(chunks)) {
  if (key.startsWith('m-')) continue;
  const n = (CAPTIONS[key] ?? []).length;
  if (n !== c.chunks.length) throw new Error(`${key}: ${c.chunks.length} slices but ${n} captions; update CAPTIONS.`);
}

function caption(key, i, c) {
  const text = CAPTIONS[key]?.[i];
  if (!text) return '';
  const n = c.chunks.length > 1 ? `${i + 1}/${c.chunks.length} · ` : '';
  return `<figcaption class="cap"><span class="n">${esc(n)}</span>${esc(text)}</figcaption>`;
}

function screenPages(sec, [key, title, bullets]) {
  const c = chunks[key];
  if (!c) throw new Error(`Screenshot missing: ${key}`);
  return pages(c, bullets.length > 0).map((p, pi) => `
    <section class="page shot">
      <header class="shot-head">
        <div><p class="eyebrow">${esc(sec.title)}${pi ? ' · continued' : ''}</p><h2>${esc(title)}</h2></div>
        <p class="url">${esc(c.url.split('?')[0].replace(/^(\/timber-frame\/drawings\/)[^/]+\/.+$/, '$1…'))}</p>
      </header>
      ${p.bullets ? `<ul class="bullets">${bullets.map((b) => `<li>${esc(b)}</li>`).join('')}</ul>` : ''}
      ${p.items.map((i) => `<figure><img style="width:${(p.scale * 100).toFixed(1)}%" src="${img(c.chunks[i])}" alt="${esc(title)}">${caption(key, i, c)}</figure>`).join('')}
      ${foot()}
    </section>`).join('');
}

const foot = () => `<footer class="foot"><span class="fl">${MARK(10)}Glennon Brothers · Website proposal</span><span>Code Canvas Consultants Ltd · ${esc(DATE)}</span></footer>`;
const notes = (list) => `<div class="phone-notes">${list.map(([t, d]) => `<div><b>${esc(t)}</b>${esc(d)}</div>`).join('')}</div>`;
const phones = (list) => `<div class="phones">${list.map(([k, c]) => `<figure><img src="${img(chunks[k].chunks[0])}" alt="${esc(c)}"><figcaption>${esc(c)}</figcaption></figure>`).join('')}</div>`;

const html = `<!doctype html><html lang="en-IE"><head><meta charset="utf-8"><title>Glennon Brothers – Website Proposal</title>
<style>

  ${FONTS}
  @page { size: Letter; margin: 0.5in 0.6in 0.55in; }
  :root { --graphite: #162e38; --graphite-950: #0e1d24; --gm: #1f3d4a; --bg: #f4f5f5; --surface-2: #e8ebec; --ink: #162e38; --ink-2: #33474f; --ink-3: #56656b; --line: #d5dadc; --coral: #de0a2a; --coral-text: #b5081f; --coral-soft: #fbe7ea; --red: #de0a2a; }
  * { box-sizing: border-box; }
  html, body { margin: 0; padding: 0; font-family: "Inter", system-ui, sans-serif; color: var(--ink); font-size: 10.5pt; line-height: 1.45; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  h1, h2, h3, .display { font-family: "Montserrat", system-ui, sans-serif; font-weight: 700; letter-spacing: -0.015em; color: var(--ink); margin: 0; line-height: 1.05; }
  h3 { font-family: "Inter", system-ui, sans-serif; letter-spacing: 0; font-size: 12pt; }
  .page { page-break-after: always; break-after: page; height: 9.95in; display: flex; flex-direction: column; overflow: hidden; }
  .page:last-child { page-break-after: auto; }
  .eyebrow { font-size: 8pt; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; color: var(--coral-text); margin: 0 0 5px; }
  .muted { color: var(--ink-3); }
  /* red tab label, as on the site (and the current glennonbrothers.ie) */
  .plate { align-self: flex-start; display: inline-flex; background: var(--red); color: #fff; border-radius: 4px 4px 0 0; font-family: "Montserrat"; font-size: 7.5pt; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; line-height: 1; margin: 0 0 12px; overflow: hidden; }
  .plate span { display: inline-flex; align-items: center; padding: 6px 8px 5px; }
  .plate .c { background: rgba(0,0,0,.16); font-weight: 700; letter-spacing: .04em; }
  .ticks { height: 6px; background: var(--red); }
  /* cover and dividers: the site's graphite hero band */
  .dark-page { background: var(--graphite); color: #fff; border-radius: 3px; padding: 0.6in 0.6in 0.5in; position: relative; overflow: hidden; }
  .dark-page h1 { color: #fff; }
  .cover { justify-content: space-between; background-image: linear-gradient(180deg, rgba(12,28,35,.45), rgba(12,28,35,.95) 62%), url("${img(COVER)}"); background-size: cover; background-position: 50% 0; }
  .cover h1 { font-size: 40pt; margin: 14px 0 16px; max-width: 6.2in; }
  .cover .accent { color: #ff5468; }
  .cover .lead { color: rgba(255,255,255,.85); font-size: 13pt; max-width: 5.8in; margin: 0; }
  .cover .meta { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; border-top: 1px solid rgba(255,255,255,.18); padding-top: 16px; font-size: 9.5pt; color: rgba(255,255,255,.8); }
  .cover .meta b { display: block; color: #fff; font-size: 9pt; letter-spacing: .14em; text-transform: uppercase; margin-bottom: 4px; }
  .brand { display: flex; align-items: center; }
  .mark { display: inline-flex; align-items: center; flex: none; }
  .divider { justify-content: center; }
  .divider h1 { font-size: 38pt; margin: 6px 0 18px; } .divider p.intro { color: rgba(255,255,255,.85); font-size: 12.5pt; max-width: 6in; margin: 0; }
  .divider ol { color: rgba(255,255,255,.85); columns: 2; font-size: 10pt; margin: 26px 0 0; padding-left: 18px; } .divider li { break-inside: avoid; margin: 3px 0; }
  .divider .note { margin-top: 22px; font-size: 9pt; color: rgba(255,255,255,.65); max-width: 6in; }
  .divider .ticks, .cover .ticks { position: absolute; left: 0; right: 0; bottom: 0; }
  /* text pages */
  .prose h1 { font-size: 24pt; margin-bottom: 12px; }
  .prose h2 { font-size: 13pt; margin: 16px 0 6px; letter-spacing: -0.01em; }
  .prose p { margin: 0 0 8px; }
  .prose ul.plain { margin: 0 0 8px; padding-left: 16px; } .prose ul.plain li { margin: 2px 0; }
  .cols { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  .card { border: 1px solid var(--line); border-radius: 6px; box-shadow: 0 1px 2px rgba(22,46,56,.06), 0 4px 12px rgba(22,46,56,.06); padding: 11px 13px; background: #fff; break-inside: avoid; }
  .card h3 { margin-bottom: 5px; }
  .card ul { margin: 0; padding-left: 15px; } .card li { margin: 2px 0; font-size: 9.6pt; }
  .evidence { border-left: 4px solid var(--coral-text); background: var(--bg); border-radius: 0 10px 10px 0; padding: 10px 14px 10px 30px; margin: 8px 0 10px; }
  .evidence li { margin: 3px 0; }
  .src { font-size: 8pt; color: var(--ink-3); }
  .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin: 12px 0 6px; }
  .stat { background: var(--graphite); color: #fff; border-radius: 3px; padding: 12px 12px 11px; }
  .stat b { display: block; font-family: "Montserrat"; font-weight: 700; font-size: 22pt; line-height: 1; letter-spacing: -0.03em; color: #fff; border-left: 3px solid var(--coral); padding-left: 8px; }
  .stat span { display: block; margin-top: 8px; font-size: 8.5pt; color: rgba(255,255,255,.78); line-height: 1.3; }
  table.feat { width: 100%; border-collapse: collapse; font-size: 9.5pt; }
  table.feat th, table.feat td { border-bottom: 1px solid var(--line); padding: 8px 8px; text-align: left; vertical-align: top; }
  table.feat th { background: var(--surface-2); font-size: 8pt; text-transform: uppercase; letter-spacing: .1em; color: var(--ink-3); }
  .pill-row { display: flex; flex-wrap: wrap; gap: 5px; } .pill { border: 1px solid #efc3ca; background: var(--coral-soft); color: var(--coral-text); border-radius: 3px; padding: 2px 9px; font-size: 8.5pt; font-weight: 600; }
  /* screenshot pages */
  .shot-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 12px; border-bottom: 2px solid var(--red); padding-bottom: 6px; margin-bottom: 7px; }
  .shot-head h2 { font-size: 16pt; } .url { font-family: Menlo, Consolas, monospace; font-size: 8pt; color: var(--ink-3); margin: 0; white-space: nowrap; }
  .bullets { margin: 0 0 7px; padding-left: 16px; font-size: 9.3pt; } .bullets li { margin: 1px 0; } .bullets li::marker { color: var(--coral-text); }
  figure { margin: 0 0 8px; display: block; text-align: center; }
  figure img { display: block; margin: 0 auto; border: 1px solid var(--line); border-radius: 3px; }
  .cap { text-align: left; margin: 5px 0 0; font-size: 8.6pt; line-height: 1.35; color: var(--ink-2); border-left: 3px solid var(--coral-text); padding-left: 8px; }
  .cap .n { font-weight: 700; color: var(--ink-3); }
  .phones { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 12px; }
  .phones figure { margin: 0; }
  .phones img { width: 100%; border-radius: 3px; border: 7px solid var(--graphite); }
  .phone-notes { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 14px; }
  .phone-notes div { background: var(--bg); border-radius: 3px; padding: 9px 12px; font-size: 9pt; color: var(--ink-2); }
  .phone-notes b { display: block; color: var(--ink); font-size: 9.5pt; margin-bottom: 2px; }
  .phones figcaption { text-align: center; font-size: 9pt; color: var(--ink-2); margin-top: 6px; font-weight: 600; }
  /* investment */
  .price { display: grid; grid-template-columns: 1.45fr 1fr; gap: 14px; align-items: stretch; margin: 6px 0 4px; }
  .price .big { background: var(--graphite); color: #fff; border-radius: 3px; padding: 16px 18px; }
  .price .big .amt { white-space: nowrap; font-family: "Montserrat"; font-weight: 700; font-size: 26pt; line-height: 1; color: #fff; } .price .big .amt small { font-size: 11pt; font-weight: 500; color: rgba(255,255,255,.75); }
  .price .big p { color: rgba(255,255,255,.82); margin: 8px 0 0; font-size: 9.6pt; }
  .price .year { border: 2px solid var(--coral-text); border-radius: 3px; padding: 14px 16px; background: var(--coral-soft); }
  .price .year div { display: flex; justify-content: space-between; gap: 8px; padding: 4px 0; border-bottom: 1px dotted #e2a3ad; font-size: 10pt; } .price .year div:last-child { border: 0; }
  .price .year b { font-family: "Montserrat"; font-weight: 700; }
  .terms li { margin: 3px 0; font-size: 9.6pt; }
  .sla { width: 100%; border-collapse: collapse; font-size: 9pt; margin-top: 4px; } .sla td, .sla th { border-bottom: 1px solid var(--line); padding: 4px 6px; text-align: left; } .sla th { font-size: 7.8pt; letter-spacing: .1em; text-transform: uppercase; color: var(--ink-3); background: var(--surface-2); }
  .weeks { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 6px 0 10px; }
  .week { border: 1px solid var(--line); border-radius: 3px; padding: 10px 12px; background: #fff; }
  .week b { display: inline-block; font-family: "Montserrat"; font-weight: 700; font-size: 8.5pt; background: var(--red); color: #fff; border-radius: 4px 4px 0 0; padding: 3px 6px; margin-bottom: 5px; }
  .week p { margin: 0; font-size: 9.5pt; }
  /* motion stills */
  .stills { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 14px; margin-top: 8px; }
  .stills figure { margin: 0; text-align: left; }
  .stills img { width: 100%; border: 1px solid var(--line); border-radius: 4px; }
  .stills .phone img { width: auto; height: 2.3in; border: 6px solid var(--graphite); border-radius: 14px; }
  .stills .phone { display: flex; gap: 12px; align-items: flex-start; }
  .stills figcaption { margin-top: 5px; font-size: 8.6pt; line-height: 1.35; color: var(--ink-2); border-left: 3px solid var(--coral-text); padding-left: 8px; }
  .rules { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 12px; }
  .rules div { background: var(--surface-2); border-radius: 6px; padding: 9px 12px; font-size: 8.9pt; color: var(--ink-2); }
  .rules b { display: block; color: var(--ink); font-size: 9.4pt; margin-bottom: 2px; }
  footer.foot { margin-top: auto; padding-top: 6px; font-size: 7.8pt; color: #8a939d; display: flex; justify-content: space-between; border-top: 1px solid var(--line); align-items: center; } footer.foot .fl { display: inline-flex; align-items: center; gap: 6px; }

  .phones img { border-radius: 14px; }
  .dark-page { border-radius: 0; }

</style></head><body>

<section class="page dark-page cover">
  ${WORDMARK()}
  <div>
    ${plate('1913', 'Website proposal · ' + DATE, true)}
    <h1>A website as modern as <span class="accent">the mills behind it.</span></h1>
    <p class="lead">A new glennonbrothers.ie for the four sawmills and DTE: one page per mill, trade quotes with line items routed to the right sales team, timber frame enquiries with drawings, haulier slot booking, careers by mill, and a back office for sales, DTE and HR. Built for phones first.</p>
  </div>
  <div class="meta">
    <div><b>Prepared for</b>Pat Glennon and Mike Glennon, Joint Managing Directors<br>Glennon Brothers Timber Ltd<br>Saw Mills, Dublin Road, Longford N39 AX73</div>
    <div><b>Prepared by</b>Keron Lewis, Director, Code Canvas Consultants Ltd<br>Port of Spain · codecanvastt.com<br>info@codecanvastt.com · +1 868 466-CODE</div>
  </div>
  <div class="ticks"></div>
</section>

<section class="page prose">
  ${plate('01', 'Executive summary')}
  <h1>Why rebuild now</h1>
  <p>Glennon Brothers has sawn timber in Longford since 1913 and today runs four sawmills in Ireland and Scotland, with DTE making timber frame homes on the Longford site. Customers across Europe, the UK and Ireland have voted it TTJ Softwood Trader: Sawmiller of the Year six times, most recently in 2025. The website does not show that group. When we checked on 27 October 2026:</p>
  <ul class="evidence">
    <li><b>The home page is built for Flash.</b> It is an XHTML 1.0 page that loads its banner through SWFObject and a .swf file, which no current browser plays.</li>
    <li><b>There is no mobile layout.</b> The pages have no viewport setting, so a phone shows the desktop page shrunk to fit; the stylesheet fixes every page at 800 pixels wide, with no media queries.</li>
    <li><b>Careers goes to a Teamtailor site named "glennonbrothers-demo".</b> The vacancies there are real, but the address looks like a test site, and nothing on the main website shows which mill is hiring.</li>
    <li><b>Quotes, drawings and haulier bookings are phone and email only</b>, and dte.ie is a placeholder.</li>
  </ul>
  <p class="src">Sources: glennonbrothers.ie (index, aboutus, contactus and product pages and css/style.css, read 27 October 2026); glennonbrothers-demo.teamtailor.com; dte.ie; glennonbrothers.ie/press/TTJ_Awards_2025.html.</p>
  <p>This proposal replaces the static site with one built for a sawmill group that sells to the trade in two currencies: a page per mill, a trade quote form with line items that reaches the right sales team in euro or sterling, a DTE section that takes drawings and gives the customer a private link for revisions, haulier slot booking, careers by mill and role family, and a back office for each team. A working demo is built and can be shown live, on a laptop or a phone.</p>
  <div class="stat-grid">
    <div class="stat"><b>${SCREEN_COUNT}</b><span>screens designed, built and shown in this proposal</span></div>
    <div class="stat"><b>4</b><span>sawmills, each with its own page, plus DTE</span></div>
    <div class="stat"><b>4</b><span>staff roles, each seeing only its own work</span></div>
    <div class="stat"><b>${TESTS}</b><span>automated tests run before every release</span></div>
  </div>
  <h2>What the group gains</h2>
  <div class="cols">
    <div class="card"><h3>Merchants, builders and hauliers</h3><ul><li>A site that works on the phone in the yard or the cab</li><li>A quote request with sizes and quantities, answered by the right team</li><li>Slot booking at the right mill, and drawings sent once</li></ul></div>
    <div class="card"><h3>Sales, DTE and HR</h3><ul><li>Every quote with an owner, and a warning after 24 hours</li><li>Drawings kept privately with the enquiry, not in inboxes</li><li>Applicants by mill and role family, with CVs kept private</li></ul></div>
  </div>
  ${foot()}
</section>

<section class="page prose">
  ${plate('02', 'At a glance')}
  <h1>What is included</h1>
  <table class="feat"><thead><tr><th style="width:20%">Area</th><th>Features</th></tr></thead><tbody>
    ${FEATURES.map(([a, f]) => `<tr><td><b>${esc(a)}</b></td><td><div class="pill-row">${f.map((x) => `<span class="pill">${esc(x)}</span>`).join('')}</div></td></tr>`).join('')}
  </tbody></table>
  <h2>How a trade quote flows</h2>
  <p>A builders' merchant in Co. Offaly asks for three lines of kiln-dried carcassing and CLS. The form issues a reference such as <b>QR-26-00014</b>, emails the buyer and alerts the sales inbox. Because the delivery address is in Ireland, the request goes to the Ireland team, priced in euro, and is assigned to the salesperson with the fewest open quotes; a request for Ayrshire goes to the UK team in sterling. The owner moves it from New to Pricing and Quoted, records the total, and marks it Won or Lost. After 24 hours without an answer it moves to the top of the overview.</p>
  <h2>Content and imagery</h2>
  <p>The history, mills, products, certifications and awards come from the group's own website, its press pages and dte.ie. Nothing is invented: no figures, awards, certifications, staff or testimonials beyond what the group publishes, and the only people named are the joint managing directors. Most photographs are the group's own, from its website, and would be used with its permission; the forest and timber frame photographs are licensed from Wikimedia Commons, credited on the site and labelled as illustrative. Quotes, enquiries, bookings, postings and applicants in these screenshots are clearly marked samples with fictitious names.</p>
  ${foot()}
</section>

${SECTIONS.map((sec) => `
<section class="page dark-page divider">
  ${plate(sec.code, sec.eyebrow, true)}
  <h1>${esc(sec.title)}</h1>
  <p class="intro">${esc(sec.intro)}</p>
  <ol>${sec.screens.map((s) => `<li>${esc(s[1])}</li>`).join('')}</ol>
  <p class="note">Screens are from the working demo at 1440 pixels wide (back office at 1280). Quotes, enquiries, bookings, postings, applicants, staff names and figures are sample data.</p>
  <div class="ticks"></div>
</section>
${sec.screens.map((s) => screenPages(sec, s)).join('')}
`).join('')}

<section class="page">
  <header class="shot-head"><div><p class="eyebrow">Responsive design</p><h2>Built for phones first</h2></div><p class="url">390 × 844</p></header>
  <p style="margin:0 0 4px">Buyers on site, hauliers in the cab and people looking for work at the mills mostly arrive on a phone. Every page adapts to a small screen, and a bar with Call, Email and Trade quote stays within thumb reach.</p>
  ${phones([['m-home', 'Home'], ['m-plant', 'A mill page (Fermoy)'], ['m-quote', 'Trade quote']])}
  ${notes([['Thumb-sized controls', 'Every button and choice is at least 48 pixels tall.'], ['The right keyboard', 'Phone and quantity fields open the number pad; contact fields fill in from the phone\'s saved details.'], ['Light on data', 'Photos are served as WebP in three sizes, so a phone downloads the small one.']])}
  ${foot()}
</section>
<section class="page">
  <header class="shot-head"><div><p class="eyebrow">Responsive design · continued</p><h2>Careers and the back office on a phone</h2></div><p class="url">390 × 844</p></header>
  <p style="margin:0 0 4px">An operative can apply from a phone without a CV, and a salesperson can see what is waiting from the road.</p>
  ${phones([['m-careers', 'Careers'], ['m-admin-dashboard', 'Back-office overview'], ['m-admin-quote', 'A quote request']])}
  ${notes([['Apply in minutes', 'No CV for operatives, drivers or frame erectors; a PDF or Word CV where one is needed.'], ['Waiting first', 'The overview opens on what has waited too long.'], ['One tap to call', 'Call or email the buyer, customer, haulier or applicant from the record.']])}
  ${foot()}
</section>


<section class="page">
  <header class="shot-head"><div><p class="eyebrow">Motion and interaction</p><h2>A site that moves like a working mill</h2></div><p class="url">Stills from the home page</p></header>
  <p style="margin:0 0 2px">Movement is used to explain the business, not to decorate it: logs become boards, boards become a house, routes join the four mills. Every effect is built into the site itself (no video files, no heavy animation libraries), so pages stay fast on a phone.</p>
  <div class="stills">
    ${['hero', 'map', 'story-2', 'story-5'].map((n) => `<figure><img src="${img(still(n).file)}" alt=""><figcaption>${esc(still(n).caption)}</figcaption></figure>`).join('')}
  </div>
  <div class="rules">
    <div><b>Calm when asked</b>Visitors whose phone or computer asks for reduced motion get the same pages with no movement at all.</div>
    <div><b>Nothing waits on a script</b>Every word and figure is in the page as sent. If JavaScript is off or fails, the content is simply there.</div>
    <div><b>Smooth on older phones</b>Only movement the graphics chip handles cheaply, so the page never jumps while it loads.</div>
  </div>
  ${foot()}
</section>
<section class="page">
  <header class="shot-head"><div><p class="eyebrow">Motion and interaction · continued</p><h2>Figures, themes, history and the phone menu</h2></div><p class="url">1440 × 900 and 390 × 844</p></header>
  <div class="stills">
    ${['figures', 'theme', 'timeline'].map((n) => `<figure><img src="${img(still(n).file)}" alt=""><figcaption>${esc(still(n).caption)}</figcaption></figure>`).join('')}
    <figure class="phone"><img src="${img(still('phone-menu').file)}" alt=""><figcaption>${esc(still('phone-menu').caption)} A marquee of the group's product range runs under the hero and stops when a pointer or the keyboard reaches it.</figcaption></figure>
  </div>
  ${foot()}
</section>

<section class="page prose">
  ${plate('04', 'Technology, hosting & security')}
  <h1>Built properly, hosted for you</h1>
  <div class="cols">
    <div class="card"><h3>Platform</h3><ul><li>Laravel 13 on PHP 8.3 with a MySQL database: open-source and widely supported, with no proprietary page builder</li><li>Tailwind CSS and Alpine.js; fonts served from your own site, not a third-party font service</li><li>No background servers; emails send directly, and one hourly task clears unfinished uploads</li><li>${TESTS} automated tests cover every form, role and private file</li></ul></div>
    <div class="card"><h3>Security and privacy</h3><ul><li>HTTPS enforced, with security headers on every page</li><li>Cutting lists, drawings and CVs are stored outside the public website and served only to signed-in staff with the right role</li><li>Every opening of a private file is logged</li><li>Customer drawing links are signed, expire, and are never indexed</li><li>A privacy notice under the GDPR, the Data Protection Acts 1988 to 2018 and UK GDPR, naming the DPC and the ICO</li></ul></div>
    <div class="card"><h3>Speed and search</h3><ul><li>Responsive WebP photography</li><li>XML sitemap, and schema.org data for the group and every job posting</li><li>The old .html addresses redirect permanently to the new pages</li><li>Accessible: 17px body text, keyboard navigation, labelled forms, contrast checked in light and dark themes, and every form works without JavaScript</li></ul></div>
    <div class="card"><h3>Ownership</h3><ul><li>Your content, photos, domain and data are always yours</li><li>Staff manage quotes, enquiries, bookings, postings and users themselves</li><li>Hosting, backups, monitoring and updates are handled by Code Canvas under the monthly plan</li><li>Your email stays exactly where it is</li></ul></div>
  </div>
  <h2>Emails included</h2>
  <p>Every submission sends the sender a confirmation with their reference number and what happens next, and alerts the right team's inbox (Ireland or UK sales, DTE, HR or the yard) with a link to the record. Timber frame customers receive their private drawings link by email. Private files are never attached to emails. If you keep Teamtailor for recruitment, the careers pages can link to it instead, or the two can run side by side while you decide.</p>
  ${foot()}
</section>

<section class="page prose">
  ${plate('05', 'Investment')}
  <h1>The US$1,000 a month plan</h1>
  <div class="price">
    <div class="big"><div class="amt">US$1,000 <small>per month</small></div><p><b>US$0 for design and development.</b> No setup fee and no deposit. Invoiced in US dollars. Billing starts at the end of the month in which the site launches, prorated for the days it was live; monthly invoices are due within 14 days.</p></div>
    <div class="year"><div><span>Year one</span><b>US$12,000</b></div><div><span>Or prepaid annually (10% off)</span><b>US$10,800</b></div><div><span>Build cost</span><b>US$0</b></div></div>
  </div>
  <div class="cols" style="margin-top:8px">
    <div class="card"><h3>Included every month</h3><ul>
      <li>The complete design and build shown in this proposal</li>
      <li>Managed hosting with CDN and SSL</li>
      <li>Daily backups plus a weekly off-site copy</li>
      <li>Security patches and software updates</li>
      <li>24/7 uptime monitoring</li>
      <li><b>Up to 10 hours a month</b> of content changes and small improvements, such as new products or job postings</li>
      <li>Staff training</li>
      <li>Google Analytics, Search Console and a monthly summary</li>
      <li>Support by email, WhatsApp and phone, plus a quarterly review call</li>
      <li>A 30-day post-launch warranty</li>
    </ul></div>
    <div class="card"><h3>Terms</h3><ul class="terms">
      <li>No minimum term. 30 days' notice by email from either side.</li>
      <li>Price fixed for the first 24 months.</li>
      <li>Optional annual prepayment at 10% off: US$10,800 a year.</li>
      <li><b>Handover fee US$7,000, only if you leave.</b> It buys the website outright and moves it to hosting in Glennon Brothers' name. It is waived if Code Canvas stops trading.</li>
      <li>Your content, domain and data are always yours.</li>
    </ul>
    <h3 style="margin-top:8px">Quoted separately, only with your approval</h3><ul>
      <li>Work beyond 10 hours in a month: US$50 an hour, or a fixed quote</li>
      <li>SMS confirmations for haulier slots</li>
      <li>Integrations with your ERP, stock or pricing system, or with Teamtailor</li>
      <li>A merchant portal with logins and price lists; paid advertising and SEO campaigns; professional photography or drone footage</li>
    </ul></div>
  </div>
  <h2>Service levels</h2>
  <table class="sla"><thead><tr><th>Issue</th><th>Response</th><th>Fix target</th></tr></thead><tbody>
    <tr><td>Site down or forms not sending</td><td>1 hour, any day</td><td>4 hours</td></tr>
    <tr><td>Something on a page is broken</td><td>4 business hours</td><td>1 business day</td></tr>
    <tr><td>Content changes (text, photos, products, postings)</td><td>Acknowledged in 1 business day</td><td>Done in 2 business days</td></tr>
  </tbody></table>
  ${foot()}
</section>

<section class="page prose">
  ${plate('06', 'Timeline')}
  <h1>Six weeks to launch</h1>
  <div class="weeks">
    <div class="week"><b>Week 1</b><p>Kick-off, brand assets and content: your logo files, mill photos and confirmation of every fact on the site.</p></div>
    <div class="week"><b>Week 2</b><p>Revision round 1. Forms, emails, sales regions and staff roles configured with your real inboxes.</p></div>
    <div class="week"><b>Week 3</b><p>Content load: product ranges, open job postings, DTE projects and news.</p></div>
    <div class="week"><b>Week 4</b><p>Revision round 2.</p></div>
    <div class="week"><b>Week 5</b><p>Formal testing with your sales, DTE and HR staff on every form and screen.</p></div>
    <div class="week"><b>Week 6</b><p>Launch on HTTPS, redirects from the old addresses, Search Console, and staff training.</p></div>
  </div>
  <h2>What we need from Glennon Brothers</h2>
  <ul class="plain">
    <li>Your logo as a vector file (the demo redraws the twin-tree logo from the current website) and any brand guidelines.</li>
    <li>Permission to use the photographs on your current website, and any newer ones of the mills and DTE homes.</li>
    <li>Confirmation of the facts shown, especially capacity figures, certificate codes and the DTE address.</li>
    <li>Whether Balcas, ATD and Pontrilas should appear, and whether careers should stay on Teamtailor.</li>
    <li>The staff who need accounts, their roles and sales regions, and the inboxes for each team.</li>
    <li>Access to the glennonbrothers.ie domain settings (your email records stay untouched).</li>
  </ul>
  <h2>When billing starts</h2>
  <p>Not on signing. The build, both revision rounds, testing and the preview site cost nothing. Billing starts at the end of the month in which the site launches, prorated for the days it was live. Nothing goes live until you approve it in writing.</p>
  ${foot()}
</section>

<section class="page prose">
  ${plate('07', 'Next steps')}
  <h1>From proposal to launch</h1>
  <ol class="plain" style="padding-left:18px">
    <li style="margin:5px 0"><b>See the demo live.</b> We walk through the website and the back office together, on a laptop and a phone.</li>
    <li style="margin:5px 0"><b>Tell us what to change.</b> Pages, wording, roles, regions or the intake flows; revision round 1 opens straight away.</li>
    <li style="margin:5px 0"><b>Sign a one-page agreement</b> with the terms in this proposal: US$1,000 a month, month to month on 30 days' notice, US$0 build cost, and a US$7,000 handover fee only if you leave.</li>
    <li style="margin:5px 0"><b>Kick-off the same week</b>, with launch six weeks later.</li>
  </ol>
  <div class="card" style="margin-top:18px; max-width:4.6in">
    <h3>Contact</h3>
    <p style="margin:0">Keron Lewis, Director<br>Code Canvas Consultants Ltd, Port of Spain<br>codecanvastt.com · +1 868 466-CODE<br><a href="mailto:info@codecanvastt.com">info@codecanvastt.com</a><br><a href="https://calendly.com/keronlewis/30min">Book a call: calendly.com/keronlewis/30min</a></p>
  </div>
  <div style="margin-top:auto; margin-bottom:12px">${WORDMARK(false)}</div>
  ${foot()}
</section>
</body></html>`;

fs.writeFileSync(OUT_HTML, html);
const browser = await puppeteer.launch({ executablePath: CHROME, headless: 'new', args: ['--no-sandbox', '--allow-file-access-from-files'] });
const page = await browser.newPage();
await page.goto('file://' + OUT_HTML, { waitUntil: 'networkidle0' });
await page.evaluate(() => document.fonts.ready);
await page.pdf({ path: OUT_PDF, format: 'Letter', printBackground: true, preferCSSPageSize: true, displayHeaderFooter: false });
await browser.close();
const mb = Math.round(fs.statSync(OUT_PDF).size / 1024 / 1024 * 10) / 10;
console.log('PDF written:', OUT_PDF, mb + ' MB');
if (mb > MAX_MB) {
  console.error(`The PDF is over ${MAX_MB} MB, too large for most email attachments. Lower the JPEG quality in capture.mjs.`);
  process.exit(1);
}
