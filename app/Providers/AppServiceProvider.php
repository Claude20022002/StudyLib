<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Document;
use App\Models\Event;
use App\Models\InternshipReview;
use App\Models\Notification;
use App\Models\ProjectIdea;
use App\Models\User;
use App\Policies\DocumentPolicy;
use App\Policies\EventPolicy;
use App\Policies\InternshipReviewPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\ProjectIdeaPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Document::class, DocumentPolicy::class);
        Gate::policy(InternshipReview::class, InternshipReviewPolicy::class);
        Gate::policy(ProjectIdea::class, ProjectIdeaPolicy::class);
        Gate::policy(Event::class, EventPolicy::class);
        Gate::policy(Notification::class, NotificationPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
