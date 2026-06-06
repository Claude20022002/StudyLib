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
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureRateLimiting();

        Gate::policy(Document::class, DocumentPolicy::class);
        Gate::policy(InternshipReview::class, InternshipReviewPolicy::class);
        Gate::policy(ProjectIdea::class, ProjectIdeaPolicy::class);
        Gate::policy(Event::class, EventPolicy::class);
        Gate::policy(Notification::class, NotificationPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email', '');

            return Limit::perMinute(5)->by(Str::lower($email).'|'.$request->ip());
        });

        RateLimiter::for('register', fn (Request $request) => Limit::perMinute(3)->by($request->ip()));

        RateLimiter::for('ai', fn (Request $request) => Limit::perHour(20)->by(
            $request->user()?->getKey() ?? $request->ip(),
        ));

        RateLimiter::for('uploads', fn (Request $request) => Limit::perMinute(10)->by(
            $request->user()?->getKey() ?? $request->ip(),
        ));
    }
}
