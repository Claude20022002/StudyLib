<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\PlannerSessionController;
use Illuminate\Support\Facades\Route;

/*
 * Connexion unique : les comptes sont créés par l'administration dans HESTIM Planner, qui
 * authentifie tout le monde. StudyLib n'a ni inscription ni formulaire de connexion :
 * /login et /register renvoient vers la page de connexion de Planner, qui ramène ici ensuite.
 */
Route::middleware('web')->group(function () {
    Route::get('/login', [PlannerSessionController::class, 'login'])->name('login');
    Route::get('/register', [PlannerSessionController::class, 'login'])->name('register');

    Route::post('/logout', [PlannerSessionController::class, 'logout'])
        ->middleware('auth')
        ->name('logout');
});
