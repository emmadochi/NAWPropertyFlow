<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('payments:check-due')->dailyAt('08:00');
Schedule::command('commissions:monthly-summary')->monthlyOn(1, '00:00');
Schedule::command('crm:release-expired-reservations')->dailyAt('08:00');
Schedule::command('hr:weekly-digest')->weeklyOn(1, '07:00'); // Every Monday at 7am
Schedule::command('drip:process-steps')->hourly();

