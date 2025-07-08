<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\CheckMaintenanceStatus;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Schedule::command(CheckMaintenanceStatus::class)->dailyAt('01:00');

Schedule::command(CheckMaintenanceStatus::class)->everyMinute()->appendOutputTo(storage_path('logs/laravel.log'));
