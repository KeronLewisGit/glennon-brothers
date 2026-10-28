# Glennon Brothers – Hostinger installation guide

Requirements: **PHP 8.3** (8.4 also works), a **MySQL database**, and SSH access if possible. Without SSH, use the zip and its one-time `setup.php` installer (Option A).

## Option A – upload the zip into public_html (fastest, no Git or SSH)

Run `scripts/package-hostinger.sh` locally. It builds `dist/glennon-hostinger.zip`, which includes the production `vendor/` and built assets.

```
public_html/
├── index.php  .htaccess  robots.txt  favicon.svg  setup.php
├── build/                 compiled CSS, JS and fonts
├── img/                   site photography (WebP, three sizes each)
└── glennon-app/            the Laravel application (blocked from the web by its own .htaccess)
```

Before you start, do steps 1–4 of **Prepare hPanel** in Option B below (domain + SSL, PHP 8.3, MySQL database, mailbox).

1. In File Manager, empty `public_html/` (delete Hostinger's `default.php`), upload the zip to `public_html` and extract it there.
2. Copy `glennon-app/.env.example` to `glennon-app/.env`. Fill in the database, mail and `GB_*` values, and set `SETUP_TOKEN` to a long random string.
3. Open `https://www.glennonbrothers.ie/setup.php?token=YOUR_TOKEN`. It generates the app key, migrates, creates the first staff account and deletes itself.
4. Remove `SETUP_TOKEN` from `.env`.

To update later, build a new zip, and upload and extract it over the old files. `glennon-app/.env` and `glennon-app/storage/` (cutting lists, timber frame drawings, CVs, logs) are not in the zip, so they are kept. Then visit `/setup.php?token=…` again if the release has new migrations (add a `SETUP_TOKEN` first; once a staff account exists the installer only migrates and never creates or resets accounts), or run `php artisan migrate --force` over SSH from `glennon-app/`.

## Option B – Hostinger Git deployment (for ongoing development)

The whole repository is cloned straight into `public_html/`:

```
public_html/
├── .htaccess        forces HTTPS, rewrites every request into public/
├── .env             created by you on the server (never committed)
├── app/ bootstrap/ config/ database/ lang/ resources/ routes/ storage/ vendor/
└── public/          the only folder the web can reach (index.php, build/, robots.txt)
```

- The root `.htaccess` sends every request into `public/`, so `.env`, `storage/`, `vendor/` and `.git/` are never served.
- `composer.lock` is resolved for PHP 8.3 through `config.platform.php` in `composer.json`.
- `public/build/` holds the compiled CSS, JS and fonts. It is committed because the server has no Node, so run `npm run build` and commit the result after any CSS or JS change.
- Mail is sent inline (`QUEUE_CONNECTION=sync`), so there is no queue worker. One cron job is needed to clear attachments held for unfinished forms (see **Cron** below).

### 1. Prepare hPanel (one time)

1. **Domain and SSL.** Point the `www` and apex records for glennonbrothers.ie at the Hostinger plan **without touching the MX and SPF records** (mail is filtered by Barracuda), then go to hPanel → Security → SSL and install the free certificate. Keep the old static pages' addresses in mind: `/aboutus.html`, `/contactus.html` and the product pages redirect (301) to the new ones. The `.htaccess` redirects every request to HTTPS, so the site will not load until the certificate is active.
2. **PHP version.** Go to hPanel → Advanced → PHP Configuration and choose **PHP 8.3** (8.4 also works). Leave the default extensions enabled. `pdo_mysql`, `mbstring`, `openssl`, `fileinfo` and `tokenizer` are required.
3. **Database.** Go to hPanel → Databases → MySQL Databases and create a database and a user. Write down the full names, which include the `u123456789_` prefix, and the password.
4. **Mail.** glennonbrothers.ie mail goes through Barracuda to the group's own mail server. Ask the group's IT team for an SMTP mailbox (for example `no-reply@glennonbrothers.ie`) for the `MAIL_*` settings, or add Hostinger to the SPF record and use a Hostinger mailbox. The site sends new-submission notices to staff, reference-number confirmations to senders, and timber frame customers their private drawings link.
5. **SSH.** Go to hPanel → Advanced → SSH Access, enable it, and note the IP, port (usually `65002`) and username.

### 2. Connect the private GitHub repository

The repository (`YOUR-GITHUB-USER/glennon-brothers`) is private, so Hostinger needs a read-only deploy key.

1. Go to hPanel → Advanced → **GIT** and click **Generate SSH key**. Copy the public key.
2. On GitHub, go to the repository → Settings → Deploy keys → **Add deploy key**. Paste the key, name it "Hostinger", and leave *Allow write access* unchecked.
3. Hostinger only deploys into an **empty** directory. Open File Manager and delete everything in `public_html/`, including Hostinger's `default.php`.
4. Back in hPanel → GIT → Create a new repository, fill in:
   - **Repository:** `git@github.com:YOUR-GITHUB-USER/glennon-brothers.git` (the SSH address, not `https://`)
   - **Branch:** `main`
   - **Directory:** leave empty. An empty value deploys into `public_html/`.
5. Click **Create**, then **Deploy**. When `composer.json` is present, Hostinger runs `composer install` after it clones the repository.

### 3. First-time setup over SSH

```bash
ssh -p 65002 u123456789@YOUR_SERVER_IP
cd ~/domains/glennonbrothers.ie/public_html

php -v                                   # must be 8.3+. If not, replace `php` below with /opt/alt/php83/usr/bin/php
ls vendor/autoload.php                   # if this is missing, Composer did not run: see the next line
composer install --no-dev --optimize-autoloader --no-interaction

cp deploy/env.production.example .env
nano .env                                # DB_*, MAIL_PASSWORD, GB_* values. Leave APP_KEY empty
chmod -R 775 storage bootstrap/cache
php artisan key:generate --force
php artisan migrate --force
php artisan app:create-admin             # first administrator (prompts for name, email, password); add other staff in Dashboard → Staff
php artisan optimize
```

`SETUP_TOKEN` and the `ADMIN_*` lines in `.env` are only used by the zip installer (Option A). Leave them empty here.

Open `https://www.glennonbrothers.ie` and sign in at `/login` to check the install.

### Cron (both options)

Attachments held for unfinished forms (a quote's cutting list, timber frame drawings, a CV) are deleted after 24 hours by `app:prune-held-uploads`, which Laravel's scheduler runs hourly (Europe/Dublin time). In hPanel → Advanced → Cron Jobs, add a job that runs **every minute**:

```
/usr/bin/php /home/u123456789/domains/glennonbrothers.ie/public_html/glennon-app/artisan schedule:run >> /dev/null 2>&1
```

For Option B the path ends in `public_html/artisan`. Test it once over SSH with `php artisan app:prune-held-uploads`.

### 4. Turn on automatic deploys (optional)

Go to hPanel → GIT → your repository → **Auto Deployment** and copy the webhook URL. On GitHub, go to Settings → Webhooks → **Add webhook**, paste the URL as the *Payload URL*, and keep the *push* event. Every push to `main` then deploys on its own.

### 5. Every later deploy

1. Locally: run `npm run build` if any CSS or JS changed, run the checks (`php artisan test`, `vendor/bin/phpstan analyse`, `vendor/bin/pint`), then commit and push to `main`.
2. Hostinger pulls the code, either through the webhook or when you click **Deploy** in hPanel → GIT. `.env`, `storage/` contents and `vendor/` are not tracked in Git, so a pull leaves them alone.
3. Over SSH:

   ```bash
   cd ~/domains/glennonbrothers.ie/public_html
   composer install --no-dev --optimize-autoloader --no-interaction
   php artisan migrate --force
   php artisan optimize
   ```

   Do not skip `optimize`. The routes, config and views are cached, so new pages return 404 and new settings are ignored until you run it.

`optimize` also caches the configuration. After any later `.env` edit, run `php artisan optimize` again or the change is ignored.

### Troubleshooting

| Symptom | Fix |
|---|---|
| hPanel says the directory is not empty | Delete everything in `public_html/`, including hidden files, then deploy again. |
| "Permission denied (publickey)" during deploy | The deploy key is missing on GitHub, or the repository URL is the `https://` form. |
| 500 error with no message | Read `storage/logs/laravel-*.log`. Usual causes: no `.env`, empty `APP_KEY`, wrong `DB_*` values, or `storage/` not writable. |
| "Your Composer dependencies require a PHP version >= 8.3" | The website or SSH PHP is older than 8.3. Fix it in PHP Configuration, or run `/opt/alt/php83/usr/bin/php $(which composer) install --no-dev`. |
| Redirect loop or "too many redirects" | SSL is not active yet, or Cloudflare is in *Flexible* mode. Use *Full*. |
| Page loads without styling | `public/build/` was not committed. Run `npm run build` locally, commit, push and deploy again. |
| Emails never arrive | Check `MAIL_*` and `GB_NOTIFY_EMAIL` in `.env`, run `php artisan optimize`, and check the mailbox's spam folder and the Barracuda quarantine. |
| Held attachments pile up in `storage/app/private/held` | The cron job is missing. Check hPanel → Advanced → Cron Jobs. |

## Check after install

- `https://www.glennonbrothers.ie/.env`, `/.git/config` and `/composer.json` must return 403 or 404. For Option A, check `/glennon-app/` as well.
- `http://` must redirect to `https://`.
- From a phone, send a trade quote for Ireland and one for Scotland (with a cutting list), a timber frame enquiry with a drawing, a haulier slot booking, an operative job application (no CV) and a contact message. Each should show a reference number (QR-, TF-, HB-, JA-, EN-), email the sender, and email the right `GB_NOTIFY_*` inbox. The Irish quote should be assigned to an Ireland salesperson in euro; the Scottish one to a UK salesperson in sterling.
- Open the drawings link from the timber frame confirmation email in a private window, add a drawing, then revoke the link in the back office and check it now fails.
- Sign in as **HR** and confirm trade quotes, timber frame and bookings return 403; as **Sales** confirm CVs and drawings do; as **Frame** confirm quotes and CVs do. Confirm `/glennon-app/storage/app/private/` returns 403 (Option A) or that `storage/` is unreachable (Option B).
- Old addresses such as `/aboutus.html`, `/contactus.html` and `/fencing.html` should redirect (301) to the new pages.
- Submit `/sitemap.xml` in Google Search Console.
- When the site replaces glennonbrothers.ie, set `GB_PREVIEW=false`. On a temporary preview domain, set it to `true` so a banner shows and search engines skip it.
