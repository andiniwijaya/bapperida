<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Production Scheduler
|--------------------------------------------------------------------------
|
| Pastikan cron menjalankan: php artisan schedule:run (setiap menit).
| Lihat deploy/crontab.example dan docs/DEPLOYMENT.md.
|
| Catatan: purge activity log (activity_log_retention_days) — pengembangan berikutnya.
|
*/
