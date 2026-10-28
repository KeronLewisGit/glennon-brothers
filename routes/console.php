<?php

use Illuminate\Support\Facades\Schedule;

// Hostinger cron (hPanel → Advanced → Cron Jobs), every minute (INSTALL-HOSTINGER.md, "Cron"):
//   /usr/bin/php /home/USER/domains/glennonbrothers.ie/public_html/glennon-app/artisan schedule:run
// Times are Europe/Dublin (APP_TIMEZONE).
Schedule::command('app:prune-held-uploads')->hourly();
