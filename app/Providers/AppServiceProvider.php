<?php

declare(strict_types=1);

namespace App\Providers;

use App\Auth\PlannerTokenVerifier;
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
use App\Services\AuthService;
use App\Services\GoogleDrive\GoogleDriveClient;
use App\Services\Recommendation\ProjectMatchScorer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProjectMatchScorer::class, static fn (): ProjectMatchScorer => ProjectMatchScorer::default());
        $this->app->bind(GoogleDriveClient::class, static fn (): GoogleDriveClient => new GoogleDriveClient((string) config('services.google_drive.credentials')));
    }

    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->configurePlannerAuthentication();

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

        // API mobile : 60 requêtes par minute et par compte ; téléchargements plus serrés
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by(
            $request->user()?->getKey() ?? $request->ip(),
        ));

        RateLimiter::for('downloads', fn (Request $request) => Limit::perMinute(20)->by(
            $request->user()?->getKey() ?? $request->ip(),
        ));
    }

    /**
     * Gardes de la connexion unique : chaque requête porte un jeton de HESTIM Planner, vérifié
     * (signature RS256, expiration, émetteur, audience) puis rattaché au compte StudyLib.
     */
    private function configurePlannerAuthentication(): void
    {
        $resolve = function (Request $request, bool $cookies): ?User {
            $token = $request->bearerToken();
            if ($token === null && $cookies) {
                foreach ((array) config('planner.cookies') as $name) {
                    $token = $request->cookies->get($name) ?: null;
                    if ($token !== null) {
                        break;
                    }
                }
            }
            if (! is_string($token) || $token === '') {
                return null;
            }
            try {
                $claims = app(PlannerTokenVerifier::class)->verify($token);

                return app(AuthService::class)->provisionFromPlanner($claims);
            } catch (Throwable $e) {
                // Jeton expiré, forgé ou Planner injoignable : non connecté (redirigé vers la connexion)
                Log::info('Jeton Planner refusé : '.$e->getMessage());

                return null;
            }
        };

        Auth::viaRequest('planner', fn (Request $request) => $resolve($request, true));
        Auth::viaRequest('planner-bearer', fn (Request $request) => $resolve($request, false));
    }
}
