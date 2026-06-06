<?php

declare(strict_types=1);

use App\Http\Controllers\AiRecommendationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentDownloadController;
use App\Http\Controllers\DocumentRatingController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\InternshipReviewController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProjectIdeaController;
use App\Http\Controllers\YoutubeRecommendationController;
use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Bibliothèque
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::post('/documents/{document}/ratings', [DocumentRatingController::class, 'store'])->name('documents.ratings.store');
    Route::post('/documents/{document}/download', [DocumentDownloadController::class, 'store'])->name('documents.download');

    // Recommandations YouTube par module
    Route::get('/modules/{module}/youtube', [YoutubeRecommendationController::class, 'index'])->name('modules.youtube');

    // Stages
    Route::get('/internship-reviews', [InternshipReviewController::class, 'index'])->name('internship-reviews.index');
    Route::post('/internship-reviews', [InternshipReviewController::class, 'store'])->name('internship-reviews.store');

    // Projets CV
    Route::get('/project-ideas', [ProjectIdeaController::class, 'index'])->name('project-ideas.index');
    Route::post('/project-ideas', [ProjectIdeaController::class, 'store'])->name('project-ideas.store');

    // Événements
    Route::get('/events', [EventController::class, 'index'])->name('events.index');

    // Suggestions IA
    Route::post('/ai/suggestions', [AiRecommendationController::class, 'store'])->name('ai.suggestions');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}', [NotificationController::class, 'update'])->name('notifications.update');
});

// Administration
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/moderation/documents', [Admin\ModerationController::class, 'index'])->name('moderation.index');
    Route::patch('/moderation/documents/{document}/approve', [Admin\ModerationController::class, 'approve'])->name('moderation.approve');
    Route::patch('/moderation/documents/{document}/reject', [Admin\ModerationController::class, 'reject'])->name('moderation.reject');

    Route::post('/events', [Admin\EventController::class, 'store'])->name('events.store');
    Route::put('/events/{event}', [Admin\EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [Admin\EventController::class, 'destroy'])->name('events.destroy');
});
