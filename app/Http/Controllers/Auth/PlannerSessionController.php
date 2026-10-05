<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Entrée et sortie de StudyLib avec la connexion unique de HESTIM Planner.
 */
class PlannerSessionController extends Controller
{
    /**
     * Vers la page de connexion de Planner, avec le chemin où revenir. Seul un chemin interne
     * de StudyLib est transmis (jamais une URL fournie par l'utilisateur telle quelle).
     */
    public function login(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->intended(route('dashboard'));
        }
        $next = $this->internalPath((string) $request->session()->pull('url.intended', ''), $request)
            ?? $request->getBasePath().'/dashboard';

        return redirect()->to(config('planner.login_url').'?'.http_build_query(['next' => $next]));
    }

    /**
     * Déconnexion de toute la plateforme : la session Planner du jeton est révoquée (appel de
     * serveur à serveur, authentifié par ce jeton), puis les cookies et la session locale sont effacés.
     */
    public function logout(Request $request): RedirectResponse
    {
        $token = $request->bearerToken();
        foreach ((array) config('planner.cookies') as $name) {
            $token ??= $request->cookies->get($name) ?: null;
        }
        if (is_string($token) && $token !== '') {
            try {
                Http::timeout(5)->withToken($token)->acceptJson()->post(config('planner.api_url').'/auth/logout');
            } catch (Throwable) {
                // Planner injoignable : le jeton expirera de lui-même (15 minutes)
            }
        }

        Auth::guard()->forgetUser();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $response = redirect()->to(config('planner.login_url'));
        foreach ((array) config('planner.cookies') as $name) {
            $response->withCookie(Cookie::forget($name, '/', null));
        }

        return $response;
    }

    private function internalPath(string $url, Request $request): ?string
    {
        if ($url === '') {
            return null;
        }
        $parts = parse_url($url);
        if ($parts === false || (isset($parts['host']) && $parts['host'] !== $request->getHost())) {
            return null;
        }
        $path = ($parts['path'] ?? '/').(isset($parts['query']) ? '?'.$parts['query'] : '');
        $base = $request->getBasePath();
        if (! str_starts_with($path, '/') || str_starts_with($path, '//') || str_contains($path, '\\')
            || ($base !== '' && ! str_starts_with($path, $base.'/') && $path !== $base)) {
            return null;
        }

        return $path;
    }
}
