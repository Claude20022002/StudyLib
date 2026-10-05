<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Filières et modules suivent la maquette de HESTIM Planner (connexion unique, phase C)
Schedule::command('planner:sync')->everyFifteenMinutes()->withoutOverlapping();
