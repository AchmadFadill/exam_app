<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the auto-submit command
\Illuminate\Support\Facades\Schedule::command('exam:auto-submit')->everyMinute();
