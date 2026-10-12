<?php

use App\Models\AiRecommendation;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Filières et modules suivent la maquette de HESTIM Planner (connexion unique, phase C)
Schedule::command('planner:sync')->everyFifteenMinutes()->withoutOverlapping();

// Demandes et réponses de l'IA effacées après le délai de conservation (config studylib.ai)
Schedule::command('model:prune', ['--model' => [AiRecommendation::class]])->dailyAt('03:30');
