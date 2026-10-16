<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ClassDocumentController;
use App\Http\Controllers\Api\InternalDocumentController;
use App\Http\Controllers\Api\InternshipOfferController;
use App\Http\Controllers\Api\InternshipReviewMobileController;
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
    // Écran « Stages » de l'application : tous les retours (filtres), les miens, les valeurs des filtres
    Route::get('/internship-reviews/mobile', [InternshipReviewMobileController::class, 'index']);
    Route::get('/internship-reviews/miens', [InternshipReviewMobileController::class, 'mine']);
    Route::get('/internship-reviews/filtres', [InternshipReviewMobileController::class, 'filters']);
    // Offres de stage partagées par un lien : lecture de la page ou du texte collé, puis publication
    Route::post('/offres/analyser', [InternshipOfferController::class, 'analyser'])->middleware('throttle:offres-analyse');
    Route::post('/offres/analyser-texte', [InternshipOfferController::class, 'analyserTexte'])->middleware('throttle:offres-analyse');
    Route::get('/offres/disponibilite', [InternshipOfferController::class, 'disponibilite']);
    Route::get('/offres/miennes', [InternshipOfferController::class, 'mine']);
    Route::get('/offres', [InternshipOfferController::class, 'index']);
    Route::post('/offres', [InternshipOfferController::class, 'store'])->middleware('throttle:offres-publier');
    Route::delete('/offres/{offre}', [InternshipOfferController::class, 'destroy'])->whereUuid('offre');
    Route::get('/internship-reviews/{review}/photo', [MobileController::class, 'internshipReviewPhoto'])->whereUuid('review');
    Route::get('/internship-reviews', [InternshipReviewController::class, 'index']);
    Route::post('/internship-reviews', [InternshipReviewController::class, 'store']);
    Route::get('/project-ideas/mobile', [MobileController::class, 'projectIdeas']);
    Route::get('/project-ideas', [ProjectIdeaController::class, 'index']);
    Route::get('/events', [EventController::class, 'index']);
});

/*
 * Espace de cours de Planner (plan espace-de-cours.md, § 3) : documents de classe, déposés et
 * retirés par le serveur de Planner avec le jeton de l'enseignant (garde « api »).
 */
Route::middleware(['auth:api', 'throttle:api'])->prefix('classe/documents')->group(function () {
    Route::post('/', [ClassDocumentController::class, 'store'])->middleware('throttle:uploads');
    Route::get('/{document}', [ClassDocumentController::class, 'show'])->whereUuid('document');
    Route::patch('/{document}/publier', [ClassDocumentController::class, 'publish'])->whereUuid('document');
    Route::delete('/{document}', [ClassDocumentController::class, 'destroy'])->whereUuid('document');
});

// Lien d'un document pour un étudiant de la classe : jeton de service de Planner, jamais un utilisateur
Route::middleware(['planner.service:document:lien', 'throttle:interne'])
    ->get('/interne/documents/{document}/lien', [InternalDocumentController::class, 'link'])
    ->whereUuid('document');
