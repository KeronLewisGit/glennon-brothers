# Glennon Brothers

Website and back office for Glennon Brothers Timber Ltd, the family sawmilling group founded in Longford in 1913, with sawmills at Longford and Fermoy (Ireland) and Windymains and Troon (Scotland), and DTE Manufacturing (timber frame homes and roof trusses) on the Longford site. The group's current site, glennonbrothers.ie, is a static XHTML site from the Flash era: no viewport setting, a stylesheet fixed at 800 pixels wide, a SWFObject/.swf banner on the home page, and a Careers link to `glennonbrothers-demo.teamtailor.com` (checked 27 October 2026). This is a concept build for a pitch; nothing here has been sent to or approved by the group.

## What it does

- **Public site.** Home, the four mills (one page each, with the address from the group's contact page, the mill's story, facts, its open jobs and contacts), products (construction timber and CLS, fencing, pallet and packaging, Glendeck decking, residues), DTE timber frame, sustainability (only the certifications and EPDs the group publishes), about (family, history timeline, the TTJ and other awards the group publishes), news (from the group's press page), contact, a privacy notice (GDPR, Data Protection Acts 1988 to 2018, UK GDPR; DPC and ICO), photo credits and a site map. Locale en-IE. Mobile-first, 17px body text, light and dark themes, and every page and form works without JavaScript. The old `.html` addresses (`/aboutus.html`, `/contactus.html`, `/fencing.html` and the rest, in `config/glennon.php`) redirect (301) to the new pages.
- **Five intake flows, each with a reference number and an email confirmation.**
  - Trade quote (`QR-26-00014`): business type, contact, delivery country and address, needed-by date, up to 12 line items (product, grade, thickness and width in mm, length in m, treatment, quantity and unit, optional delivery site per line), an optional cutting list (PDF, Excel, CSV, Word) and remarks. The country decides the sales region and currency: the Republic of Ireland and Northern Ireland go to the Ireland team, Great Britain to the UK team; Ireland is priced in EUR, everything else in GBP. Each request is assigned to the active salesperson in that region with the fewest open quotes.
  - DTE timber frame enquiry (`TF-…`): client type, project type (and number of homes for schemes), site, county, Eircode, planning status, planned start, floor area and up to 8 drawings (PDF, DWG, DXF, images, ZIP; 20 MB each). The confirmation email carries a signed link (30 days) where the customer can add revised drawings; DTE can revoke it, which rotates the key so every earlier link stops working (404). An expired link is refused (403) and a closed enquiry no longer takes drawings (410).
  - Haulier slot booking (`HB-…`): mill, date (up to 28 days ahead), slot, purpose, haulier, driver, vehicle registration and type, order reference.
  - Job application (`JA-…`): a posted opening or "any opening", preferred mill, role family, experience, right to work in Ireland/UK, licences for drivers, and a CV (required for maintenance and office roles only).
  - Contact form (`EN-…`) with a topic and a mill.

  All uploads go to the private `local` disk (`storage/app/private/{quotes,frame,cvs}`), never the public web root. If a form comes back with an error, the files that passed are kept (`storage/app/private/held`, keyed to the visitor's session, shown as "Attached: …"), so nobody chooses them twice; leftovers are pruned after 24 hours (`app:prune-held-uploads`, hourly).
- **Back office** at `/admin`, with four roles:
  - **Admin**: everything, including staff accounts.
  - **Sales** (with a region, Ireland or UK): trade quotes, haulier bookings and enquiries.
  - **HR**: applications, CVs and job postings.
  - **Frame** (DTE): timber frame enquiries and drawings.

  The overview leads with the one number for the signed-in role: quote requests unanswered for over 24 hours (sales see their own region; admin sees both), applications in New or HR screening for over 3 days (HR), or timber frame enquiries waiting over 2 days for a design review (DTE). Then tiles and a pipeline. Each register has status tabs, filters, search (press `/`) and CSV export (formula-safe). Every record has a notes timeline. Status changes claim the row with a conditional UPDATE, so a double submit applies once. Every opening of a private file is logged.

## Requirements

PHP 8.3 or 8.4, Composer 2, Node 20+ for building assets (and Chrome for the proposal), and SQLite locally or MySQL in production.

## Local setup

```sh
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seeder=DemoSeeder   # demo staff, 12 quotes, 7 frame enquiries, 10 bookings, 9 postings, 24 applications, 5 enquiries
npm install && npm run build
php artisan serve --port=8111
```

Demo logins all use the password `password`: `admin@glennonbrothers.test`, `sales@glennonbrothers.test` (Ireland), `sales.uk@glennonbrothers.test` (UK), `hr@glennonbrothers.test` and `frame@glennonbrothers.test`. Every demo company, customer, haulier and applicant is fictional and marked "(sample)"; the job titles are modelled on the group's real Teamtailor vacancies. `DatabaseSeeder` only creates the first admin from `ADMIN_*`. The DemoSeeder refuses to run in production unless `GB_PREVIEW=true`.

## Checks

```sh
php artisan test                 # feature tests for every form, role boundary, policy and private file
vendor/bin/phpstan analyse       # Larastan level 6, analysed as PHP 8.3
vendor/bin/pint --test
node scripts/screens.mjs dist/screens   # phone/desktop, light/dark screenshots and an overflow report
```

The suite also passes on MySQL (`DB_CONNECTION=mysql DB_DATABASE=… php artisan test`); it was checked on MySQL 26.7 with the DemoSeeder and a crawl of every public and admin page.

## Deploying

See [INSTALL-HOSTINGER.md](INSTALL-HOSTINGER.md). `scripts/package-hostinger.sh` builds `dist/glennon-hostinger.zip`, which extracts straight into `public_html/`, with the app in `glennon-app/`. One cron job (`schedule:run` every minute) clears held uploads. glennonbrothers.ie mail is filtered by Barracuda, so leave the MX and SPF records alone when pointing the domain at Hostinger, and send through a mailbox the group's IT team provides (or add Hostinger to SPF).

## Proposal

With the dev server on 8111 and demo data loaded, `npm run proposal` captures every screen and builds `dist/proposal/Glennon-Brothers-Website-Proposal.pdf` (Letter; the build fails if it is over 9.5 MB).

## Before launch: confirm with the client

Everything on the public site comes from glennonbrothers.ie, its press pages, dte.ie and the group's Teamtailor site, read 27 October 2026. These points need the group's confirmation:

1. **Photos.** Most photos are the group's own, taken from glennonbrothers.ie (`'own' => true` in `config/photo-credits.php`). Reuse needs the group's permission. The forest and timber frame house photos are Wikimedia Commons and labelled "Illustrative photo".
2. **Logo.** `<x-logo>` and `<x-tree>` are SVG redraws of the twin-tree logo on the current site, with the wordmark set in Instrument Sans. Ask for the vector artwork.
3. **DTE address and email.** The group's contact page gives DTE's Eircode as "N39 A0T" (incomplete) and its email as info@dte.ie; dte.ie gives "60 Sawmills, Dublin Road, Longford N39 AX73" and sales@dte.ie. The site uses the dte.ie version. dte.ie itself is a placeholder page.
4. **Capacity figures.** 450,000 m³ sawn and 220,000 m³ kiln-drying a year (aboutus.html, processes.html) are undated; Windymains' 50,000 m³ is from its own page. Confirm they are current.
5. **Fermoy investment.** Sources give both €25m and €27m; neither figure is used.
6. **FSC codes.** The Irish codes (NC-COC-014377, NC-CW-014377, FSC-C006033) are listed under NEPCon, which is now Preferred by Nature. Confirm the current certificate holder and codes, and Control Union for the UK plants.
7. **Award names.** TTJ award titles vary between press pages ("Softwood Trader: Sawmiller of the Year" vs "Softwood Trader of the Year"). Confirm the wording and years.
8. **The wider group.** Balcas (Enniskillen, Invergordon), Alexanders Timber Design and Pontrilas appear on the Teamtailor site but have no pages here. Decide whether to include them.
9. **Careers.** The live Teamtailor site (`glennonbrothers-demo.teamtailor.com`) has real vacancies. Decide whether to keep Teamtailor (and link to it or embed its feed) or move recruitment to this back office.
10. **People and headcounts.** Only the joint managing directors, Pat and Mike Glennon, are named. No staff names, headcounts or testimonials are used.
11. **Privacy notice.** Confirm the controller entity (or entities, for the UK companies), retention periods, and whether a DPO is appointed.
12. **Sales regions and inboxes.** Which salespeople cover Ireland and the UK, and the `GB_NOTIFY_*` inboxes for quotes, DTE, careers, deliveries and enquiries.
13. **Haulier slots.** The slot times, how far ahead bookings may be made, and which mills accept bookings.
14. **Demo data.** Demo phone numbers use the unallocated `08x 000 xxxx` pattern and emails use `example.com`; run `migrate:fresh` without the DemoSeeder before launch, and set `GB_PREVIEW=false`.
