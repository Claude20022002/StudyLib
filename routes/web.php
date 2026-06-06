<?php

declare(strict_types=1);

use App\Http\Controllers\Admin;
use App\Http\Controllers\AiRecommendationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentDownloadController;
use App\Http\Controllers\DocumentRatingController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FiliereController;
use App\Http\Controllers\InternshipReviewController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectIdeaController;
use App\Http\Controllers\YoutubeRecommendationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.landing')->name('home');

Route::get('/filieres', [FiliereController::class, 'index'])->name('filieres.index');
Route::get('/filieres/{filiere}/modules', [ModuleController::class, 'index'])->name('filieres.modules');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [DocumentController::class, 'store'])
        ->middleware('throttle:uploads')
        ->name('documents.store');
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::post('/documents/{document}/ratings', [DocumentRatingController::class, 'store'])->name('documents.ratings.store');
    Route::post('/documents/{document}/download', [DocumentDownloadController::class, 'store'])->name('documents.download');

    Route::get('/modules/{module}/youtube', [YoutubeRecommendationController::class, 'index'])->name('modules.youtube');

    Route::get('/internship-reviews', [InternshipReviewController::class, 'index'])->name('internship-reviews.index');
    Route::post('/internship-reviews', [InternshipReviewController::class, 'store'])->name('internship-reviews.store');

    Route::get('/project-ideas', [ProjectIdeaController::class, 'index'])->name('project-ideas.index');
    Route::post('/project-ideas', [ProjectIdeaController::class, 'store'])->name('project-ideas.store');

    Route::get('/events', [EventController::class, 'index'])->name('events.index');

    Route::post('/ai/suggestions', [AiRecommendationController::class, 'store'])
        ->middleware('throttle:ai')
        ->name('ai.suggestions');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}', [NotificationController::class, 'update'])->name('notifications.update');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/moderation/documents', [Admin\ModerationController::class, 'index'])->name('moderation.index');
    Route::patch('/moderation/documents/{document}/approve', [Admin\ModerationController::class, 'approve'])->name('moderation.approve');
    Route::patch('/moderation/documents/{document}/reject', [Admin\ModerationController::class, 'reject'])->name('moderation.reject');

    Route::post('/events', [Admin\EventController::class, 'store'])->name('events.store');
    Route::put('/events/{event}', [Admin\EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [Admin\EventController::class, 'destroy'])->name('events.destroy');
});
