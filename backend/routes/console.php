<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('drivers:dump-locations')->everyMinute();
Schedule::command('rides:process-scheduled')->everyMinute();
Schedule::command('rides:check-expired')->everySecond(); // Running very frequently for responsiveness
