<?php

use App\Console\Commands\ReconcilePendingPayments;
use App\Console\Commands\SendAppointmentReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(SendAppointmentReminders::class)->everyFifteenMinutes();
Schedule::command(ReconcilePendingPayments::class)->everyMinute()->withoutOverlapping(10);
