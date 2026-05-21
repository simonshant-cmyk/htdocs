<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reminders:send')->dailyAt('10:00');
Schedule::command('sanctum:prune-expired --hours=720')->daily(); // удаляем токены старше 30 дней
