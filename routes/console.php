<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── WorkLeft Scheduler ────────────────────────────────────────────────────────
// Runs every minute via: php artisan schedule:run (called by cron every minute)
// To test manually:      php artisan workleft:check-schedules

Schedule::command('workleft:check-schedules')
    ->everyMinute()
    ->withoutOverlapping()   // skip if previous run still going
    ->runInBackground()      // non-blocking
    ->appendOutputTo(storage_path('logs/scheduler.log'));

Schedule::job(new \App\Jobs\DailyBriefingJob)
    ->everyThirtyMinutes()
    ->withoutOverlapping();

// Purge tasks that have been overdue for more than 24 hours
Schedule::command('tasks:purge-overdue')
    ->hourly()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// Send weekly attendance summaries to teachers (every Friday at 5:00 PM)
Schedule::command('attendance:weekly-summary')
    ->weeklyOn(5, '17:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scheduler.log'));
