<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            require __DIR__.'/../routes/auth.php';
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);
        // Derrière la passerelle de la plateforme (seule à joindre ce conteneur) : elle retire le
        // préfixe /biblio et le transmet dans X-Forwarded-Prefix, d'où les URL générées
        $middleware->trustProxies(
            at: env('TRUSTED_PROXIES', '*'),
            headers: Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_PREFIX,
        );
        // Cookies posés par HESTIM Planner (même origine) : ni chiffrés ni signés par Laravel,
        // leur contenu (jeton RS256) est vérifié par le garde de la connexion unique
        $middleware->encryptCookies(except: ['access_token', '__Host-access_token', 'csrf_token', '__Host-csrf_token', 'refresh_token', '__Host-refresh_token']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            // API mobile, et appels JSON des pages (supports de cours depuis Planner)
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
