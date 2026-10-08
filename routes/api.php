<?php

declare(strict_types=1);

use App\Http\Controllers\Api\MobileController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\InternshipReviewController;
use App\Http\Controllers\ModuleSupportsController;
use App\Http\Controllers\ProjectIdeaController;
use Illuminate\Support\Facades\Route;

/*
 * API JSON de l'application mobile (préfixe /api). Authentification par le jeton de HESTIM
 * Planner en Bearer uniquement (garde « api ») : aucun cookie, donc pas de CSRF possible.
 * Les contrôleurs du web répondent en JSON quand on le leur demande (Accept: application/json).
 */
Route::middleware(['auth:api', 'throttle:api'])->group(function () {
    Route::get('/modules/by-code/{filiere}/{code}', [MobileController::class, 'module'])
        ->where(['filiere' => '[A-Za-z0-9_-]{1,20}', 'code' => '[A-Za-z0-9_.-]{1,30}']);

    Route::get('/modules/supports', [ModuleSupportsController::class, 'index']);

    Route::get('/documents/search', [MobileController::class, 'searchDocuments']);
    Route::get('/documents', [DocumentController::class, 'index']);
    Route::get('/documents/{document}', [DocumentController::class, 'show']);
    Route::post('/documents/{document}/download', [MobileController::class, 'download'])->middleware('throttle:downloads');

    Route::get('/internship-reviews/recent', [MobileController::class, 'recentInternshipReviews']);
    Route::get('/internship-reviews/{review}/photo', [MobileController::class, 'internshipReviewPhoto'])->whereUuid('review');
    Route::get('/internship-reviews', [InternshipReviewController::class, 'index']);
    Route::post('/internship-reviews', [InternshipReviewController::class, 'store']);
    Route::get('/project-ideas/mobile', [MobileController::class, 'projectIdeas']);
    Route::get('/project-ideas', [ProjectIdeaController::class, 'index']);
    Route::get('/events', [EventController::class, 'index']);
});
