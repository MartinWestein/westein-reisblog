<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('sitemap:generate')->dailyAt('04:00');

// Queue-drain: op shared hosting draait geen permanente queue:work-worker (F6-13).
// De scheduler werkt daarom elke minuut de wachtrij leeg en stopt zodra 'ie leeg is,
// zodat bevestigings-, contact- en nieuwsbriefmails alsnog verstuurd worden.
Schedule::command('queue:work --stop-when-empty --tries=3')
    ->everyMinute()
    ->withoutOverlapping();
