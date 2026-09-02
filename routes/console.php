<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Schedule::command('queue:work --stop-when-empty --tries=1 --max-time=50')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('subscriptions:expire')
    ->dailyAt('00:01')
    ->runInBackground();

