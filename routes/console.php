<?php

use App\Console\Commands\SendPaymentReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Send payment reminders every day at 08:00
Schedule::command(SendPaymentReminders::class)->dailyAt('08:00');
